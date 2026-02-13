<?php

namespace Kivii\Services;

use Kivii\Api\AvailabilityProviderInterface;
use Kivii\Api\BookingWriterInterface;
use Kivii\Api\KiviiAvailabilityProvider;
use Kivii\Api\KiviiBookingWriter;
use Kivii\Api\MockApiAdapter;
use Kivii\Database\BookingRepository;
use Kivii\Database\ServiceRepository;
use Kivii\Database\LogRepository;

/**
 * Core business logic for bookings.
 */
class BookingService {

    private BookingRepository $bookings;
    private ServiceRepository $services;
    private LogRepository $logger;
    private AvailabilityProviderInterface $availability;
    private BookingWriterInterface $writer;

    public function __construct() {
        $this->bookings = new BookingRepository();
        $this->services = new ServiceRepository();
        $this->logger   = new LogRepository();

        // Decide which adapter to use
        $api_options = get_option( 'kivii_api', [] );
        $use_mock    = empty( $api_options['base_url'] ) || ! empty( $api_options['use_mock'] );

        if ( $use_mock ) {
            $mock = new MockApiAdapter();
            $this->availability = $mock;
            $this->writer       = $mock;
        } else {
            $this->availability = new KiviiAvailabilityProvider();
            $this->writer       = new KiviiBookingWriter();
        }
    }

    /**
     * Get available days for calendar.
     */
    public function get_available_days( int $month, int $year, int $duration ): array {
        $cache_key = "kivii_days_{$month}_{$year}_{$duration}";
        $cached    = get_transient( $cache_key );

        if ( $cached !== false ) {
            return $cached;
        }

        $days = $this->availability->getAvailableDays( $month, $year, $duration );

        // Cache for 2 minutes
        set_transient( $cache_key, $days, 2 * MINUTE_IN_SECONDS );

        return $days;
    }

    /**
     * Get available time slots for a date.
     */
    public function get_available_slots( string $date, int $duration ): array {
        $cache_key = "kivii_slots_{$date}_{$duration}";
        $cached    = get_transient( $cache_key );

        if ( $cached !== false ) {
            return $cached;
        }

        $slots = $this->availability->getAvailableTimeSlots( $date, $duration );

        // Cache for 1 minute
        set_transient( $cache_key, $slots, MINUTE_IN_SECONDS );

        return $slots;
    }

    /**
     * Create a complete booking.
     */
    public function create_booking( array $data ): array {
        // Generate booking reference (idempotency)
        $reference = $data['booking_reference'] ?? wp_generate_uuid4();

        // Check idempotency
        $existing = $this->bookings->get_by_reference( $reference );
        if ( $existing ) {
            return [
                'success'   => true,
                'reference' => $reference,
                'message'   => 'Booking already created.',
            ];
        }

        // Calculate totals from selected services
        $service_ids   = $data['services'] ?? [];
        $service_items = $this->services->get_services_by_ids( $service_ids );

        $total_price    = 0;
        $total_duration = 0;
        $booking_items  = [];
        $lang           = $data['language'] ?? 'nl';
        $title_field    = $lang === 'en' ? 'title_en' : 'title_nl';

        foreach ( $service_items as $svc ) {
            $total_price    += (float) $svc->price;
            $total_duration += (int) $svc->duration_minutes;

            $booking_items[] = [
                'service_id'       => (int) $svc->id,
                'title'            => $svc->$title_field ?: $svc->title_nl,
                'price'            => (float) $svc->price,
                'duration_minutes' => (int) $svc->duration_minutes,
                'is_addon'         => (bool) $svc->is_addon,
            ];
        }

        // Prepare booking data
        $booking_data = array_merge( $data, [
            'booking_reference' => $reference,
            'total_price'       => $total_price,
            'total_duration'    => $total_duration,
            'source_domain'     => wp_parse_url( home_url(), PHP_URL_HOST ),
        ] );

        // Insert booking
        $booking_id = $this->bookings->create( $booking_data );

        if ( ! $booking_id ) {
            $this->logger->error( 'Failed to create booking in database', $booking_data );
            return [
                'success' => false,
                'message' => 'Database error.',
            ];
        }

        // Insert booking items
        $this->bookings->add_items( $booking_id, $booking_items );

        $this->logger->info( "Booking created: {$reference}", [
            'booking_id' => $booking_id,
        ] );

        // Sync to Kivii API
        $api_payload = $this->build_api_payload( $booking_data, $booking_items );
        $api_result  = $this->writer->createBooking( $api_payload );

        if ( $api_result['success'] ) {
            $this->bookings->mark_synced( $booking_id, wp_json_encode( $api_result ) );
            $this->logger->info( "Booking synced to Kivii: {$reference}" );
        } else {
            $this->logger->error( "Failed to sync booking to Kivii: {$reference}", $api_result );
        }

        // Send emails
        $email_service = new EmailService();
        $booking       = $this->bookings->get_by_id( $booking_id );

        $email_service->send_customer_confirmation( $booking );
        $email_service->send_garage_notification( $booking );

        return [
            'success'   => true,
            'reference' => $reference,
            'synced'    => $api_result['success'],
            'message'   => 'Booking created successfully.',
        ];
    }

    /**
     * Resend booking to Kivii API.
     */
    public function resend_to_kivii( int $booking_id ): array {
        $booking = $this->bookings->get_by_id( $booking_id );
        if ( ! $booking ) {
            return [ 'success' => false, 'message' => 'Booking not found.' ];
        }

        $payload = $this->build_api_payload_from_booking( $booking );
        $result  = $this->writer->createBooking( $payload );

        if ( $result['success'] ) {
            $this->bookings->mark_synced( $booking_id, wp_json_encode( $result ) );
        }

        return $result;
    }

    /**
     * Build API payload from form data.
     */
    private function build_api_payload( array $data, array $items ): array {
        return [
            'booking_reference' => $data['booking_reference'],
            'license_plate'     => $data['license_plate'],
            'mileage'           => (int) $data['mileage'],
            'services'          => array_map( fn( $i ) => [
                'title'    => $i['title'],
                'price'    => $i['price'],
                'duration' => $i['duration_minutes'],
            ], $items ),
            'total_duration'    => (int) $data['total_duration'],
            'total_price'       => (float) $data['total_price'],
            'appointment_date'  => $data['appointment_date'],
            'appointment_time'  => $data['appointment_time'] ?? null,
            'is_drop_off'       => (bool) ( $data['is_drop_off'] ?? false ),
            'drop_off_time'     => $data['drop_off_time'] ?? null,
            'first_name'        => $data['first_name'],
            'last_name'         => $data['last_name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'],
            'street'            => $data['street'],
            'house_number'      => $data['house_number'],
            'house_addition'    => $data['house_addition'] ?? '',
            'postal_code'       => $data['postal_code'],
            'city'              => $data['city'],
            'remarks'           => $data['remarks'] ?? '',
            'language'          => $data['language'] ?? 'nl',
            'source'            => 'website_booking',
            'domain'            => $data['source_domain'] ?? '',
        ];
    }

    /**
     * Build API payload from existing booking object.
     */
    private function build_api_payload_from_booking( object $booking ): array {
        $items = array_map( fn( $i ) => [
            'title'    => $i->title,
            'price'    => (float) $i->price,
            'duration' => (int) $i->duration_minutes,
        ], $booking->items ?? [] );

        return [
            'booking_reference' => $booking->booking_reference,
            'license_plate'     => $booking->license_plate,
            'mileage'           => (int) $booking->mileage,
            'services'          => $items,
            'total_duration'    => (int) $booking->total_duration,
            'total_price'       => (float) $booking->total_price,
            'appointment_date'  => $booking->appointment_date,
            'appointment_time'  => $booking->appointment_time,
            'is_drop_off'       => (bool) $booking->is_drop_off,
            'drop_off_time'     => $booking->drop_off_time,
            'first_name'        => $booking->first_name,
            'last_name'         => $booking->last_name,
            'email'             => $booking->email,
            'phone'             => $booking->phone,
            'street'            => $booking->street,
            'house_number'      => $booking->house_number,
            'house_addition'    => $booking->house_addition,
            'postal_code'       => $booking->postal_code,
            'city'              => $booking->city,
            'remarks'           => $booking->remarks,
            'language'          => $booking->language,
            'source'            => 'website_booking',
            'domain'            => $booking->source_domain ?? '',
        ];
    }
}
