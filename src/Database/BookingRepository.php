<?php

namespace Kivii\Database;

/**
 * Repository for bookings and booking items.
 */
class BookingRepository {

    private \wpdb $db;
    private string $table;
    private string $items_table;

    public function __construct() {
        global $wpdb;
        $this->db          = $wpdb;
        $this->table       = $wpdb->prefix . 'kiviiweb_bookings';
        $this->items_table = $wpdb->prefix . 'kiviiweb_booking_items';
    }

    /**
     * Insert a new booking.
     */
    public function create( array $data ): int|false {
        $result = $this->db->insert( $this->table, [
            'booking_reference' => $data['booking_reference'],
            'license_plate'     => sanitize_text_field( $data['license_plate'] ),
            'mileage'           => absint( $data['mileage'] ),
            'appointment_date'  => sanitize_text_field( $data['appointment_date'] ),
            'appointment_time'  => $data['appointment_time'] ?? null,
            'is_drop_off'       => (int) ( $data['is_drop_off'] ?? false ),
            'drop_off_time'     => $data['drop_off_time'] ?? null,
            'total_price'       => floatval( $data['total_price'] ),
            'total_duration'    => absint( $data['total_duration'] ),
            'first_name'        => sanitize_text_field( $data['first_name'] ),
            'last_name'         => sanitize_text_field( $data['last_name'] ),
            'email'             => sanitize_email( $data['email'] ),
            'phone'             => sanitize_text_field( $data['phone'] ),
            'street'            => sanitize_text_field( $data['street'] ),
            'house_number'      => sanitize_text_field( $data['house_number'] ),
            'house_addition'    => sanitize_text_field( $data['house_addition'] ?? '' ),
            'postal_code'       => sanitize_text_field( $data['postal_code'] ),
            'city'              => sanitize_text_field( $data['city'] ),
            'remarks'           => sanitize_textarea_field( $data['remarks'] ?? '' ),
            'privacy_accepted'  => 1,
            'language'          => in_array( $data['language'] ?? 'nl', [ 'nl', 'en' ] ) ? $data['language'] : 'nl',
            'status'            => 'pending',
            'source_domain'     => sanitize_text_field( $data['source_domain'] ?? '' ),
        ] );

        return $result ? $this->db->insert_id : false;
    }

    /**
     * Add items to a booking.
     */
    public function add_items( int $booking_id, array $items ): void {
        foreach ( $items as $item ) {
            $this->db->insert( $this->items_table, [
                'booking_id'       => $booking_id,
                'service_id'       => absint( $item['service_id'] ),
                'title'            => sanitize_text_field( $item['title'] ),
                'price'            => floatval( $item['price'] ),
                'duration_minutes' => absint( $item['duration_minutes'] ),
                'is_addon'         => (int) ( $item['is_addon'] ?? false ),
            ] );
        }
    }

    /**
     * Get a booking by ID with items.
     */
    public function get_by_id( int $id ): ?object {
        $booking = $this->db->get_row(
            $this->db->prepare( "SELECT * FROM {$this->table} WHERE id = %d", $id )
        );

        if ( $booking ) {
            $booking->items = $this->db->get_results(
                $this->db->prepare( "SELECT * FROM {$this->items_table} WHERE booking_id = %d", $id )
            );
        }

        return $booking;
    }

    /**
     * Get a booking by reference.
     */
    public function get_by_reference( string $reference ): ?object {
        $booking = $this->db->get_row(
            $this->db->prepare( "SELECT * FROM {$this->table} WHERE booking_reference = %s", $reference )
        );

        if ( $booking ) {
            $booking->items = $this->db->get_results(
                $this->db->prepare( "SELECT * FROM {$this->items_table} WHERE booking_id = %d", $booking->id )
            );
        }

        return $booking;
    }

    /**
     * Update booking status.
     */
    public function update_status( int $id, string $status ): bool {
        return (bool) $this->db->update(
            $this->table,
            [ 'status' => sanitize_text_field( $status ) ],
            [ 'id' => $id ]
        );
    }

    /**
     * Mark as synced with Kivii.
     */
    public function mark_synced( int $id, string $response = '' ): bool {
        return (bool) $this->db->update(
            $this->table,
            [
                'kivii_synced'  => 1,
                'kivii_response' => $response,
                'status'         => 'confirmed',
            ],
            [ 'id' => $id ]
        );
    }

    /**
     * Get all bookings with pagination and filters.
     */
    public function get_all( array $filters = [], int $page = 1, int $per_page = 20 ): array {
        $where  = '1=1';
        $params = [];

        if ( ! empty( $filters['status'] ) ) {
            $where   .= ' AND status = %s';
            $params[] = $filters['status'];
        }

        if ( ! empty( $filters['date_from'] ) ) {
            $where   .= ' AND appointment_date >= %s';
            $params[] = $filters['date_from'];
        }

        if ( ! empty( $filters['date_to'] ) ) {
            $where   .= ' AND appointment_date <= %s';
            $params[] = $filters['date_to'];
        }

        if ( ! empty( $filters['search'] ) ) {
            $search   = '%' . $this->db->esc_like( $filters['search'] ) . '%';
            $where   .= ' AND (license_plate LIKE %s OR last_name LIKE %s OR email LIKE %s OR booking_reference LIKE %s)';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $offset = ( $page - 1 ) * $per_page;

        $total_query = "SELECT COUNT(*) FROM {$this->table} WHERE {$where}";
        $total       = $params
            ? (int) $this->db->get_var( $this->db->prepare( $total_query, ...$params ) )
            : (int) $this->db->get_var( $total_query );

        $query = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $all_params = array_merge( $params, [ $per_page, $offset ] );
        $items = $this->db->get_results( $this->db->prepare( $query, ...$all_params ) );

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $per_page,
            'pages'    => ceil( $total / $per_page ),
        ];
    }

    /**
     * Delete old bookings by retention days.
     */
    public function delete_old( int $retention_days ): int {
        $date = gmdate( 'Y-m-d', strtotime( "-{$retention_days} days" ) );

        // Delete items first
        $this->db->query( $this->db->prepare(
            "DELETE bi FROM {$this->items_table} bi
             INNER JOIN {$this->table} b ON bi.booking_id = b.id
             WHERE b.created_at < %s",
            $date
        ) );

        return (int) $this->db->query( $this->db->prepare(
            "DELETE FROM {$this->table} WHERE created_at < %s",
            $date
        ) );
    }

    /**
     * Export bookings as CSV data.
     */
    public function get_for_export( array $filters = [] ): array {
        $where  = '1=1';
        $params = [];

        if ( ! empty( $filters['date_from'] ) ) {
            $where   .= ' AND appointment_date >= %s';
            $params[] = $filters['date_from'];
        }

        if ( ! empty( $filters['date_to'] ) ) {
            $where   .= ' AND appointment_date <= %s';
            $params[] = $filters['date_to'];
        }

        $query = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY appointment_date ASC";

        return $params
            ? $this->db->get_results( $this->db->prepare( $query, ...$params ), ARRAY_A )
            : $this->db->get_results( $query, ARRAY_A );
    }
}
