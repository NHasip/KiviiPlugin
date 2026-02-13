<?php

namespace Kivii\Services;

/**
 * Server-side validation for booking data.
 */
class ValidationService {

    private array $errors = [];
    private string $lang;

    public function __construct( string $lang = 'nl' ) {
        $this->lang = $lang;
    }

    /**
     * Validate step 1: vehicle data.
     */
    public function validate_vehicle( array $data ): bool {
        $this->errors = [];

        if ( empty( $data['license_plate'] ) ) {
            $this->add_error( 'license_plate', $this->t( 'Kenteken is verplicht.', 'License plate is required.' ) );
        } elseif ( ! $this->is_valid_plate( $data['license_plate'] ) ) {
            $this->add_error( 'license_plate', $this->t( 'Ongeldig kenteken formaat.', 'Invalid license plate format.' ) );
        }

        if ( empty( $data['mileage'] ) ) {
            $this->add_error( 'mileage', $this->t( 'Km-stand is verplicht.', 'Mileage is required.' ) );
        } elseif ( ! is_numeric( $data['mileage'] ) || (int) $data['mileage'] < 0 ) {
            $this->add_error( 'mileage', $this->t( 'Km-stand moet een positief getal zijn.', 'Mileage must be a positive number.' ) );
        }

        return empty( $this->errors );
    }

    /**
     * Validate step 2: services selection.
     */
    public function validate_services( array $service_ids ): bool {
        $this->errors = [];

        if ( empty( $service_ids ) ) {
            $this->add_error( 'services', $this->t(
                'Selecteer minimaal één werkzaamheid.',
                'Please select at least one service.'
            ) );
        }

        return empty( $this->errors );
    }

    /**
     * Validate step 3: timeslot.
     */
    public function validate_timeslot( array $data ): bool {
        $this->errors = [];

        if ( empty( $data['appointment_date'] ) ) {
            $this->add_error( 'appointment_date', $this->t( 'Selecteer een datum.', 'Please select a date.' ) );
        } elseif ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $data['appointment_date'] ) ) {
            $this->add_error( 'appointment_date', $this->t( 'Ongeldige datum.', 'Invalid date.' ) );
        }

        $is_drop_off = ! empty( $data['is_drop_off'] );

        if ( $is_drop_off ) {
            if ( empty( $data['drop_off_time'] ) ) {
                $this->add_error( 'drop_off_time', $this->t( 'Selecteer een brengmoment.', 'Please select a drop-off time.' ) );
            }
        } else {
            if ( empty( $data['appointment_time'] ) ) {
                $this->add_error( 'appointment_time', $this->t( 'Selecteer een tijdstip.', 'Please select a time slot.' ) );
            }
        }

        return empty( $this->errors );
    }

    /**
     * Validate step 4: contact details.
     */
    public function validate_contact( array $data ): bool {
        $this->errors = [];

        $required = [
            'first_name'   => $this->t( 'Voornaam is verplicht.', 'First name is required.' ),
            'last_name'    => $this->t( 'Achternaam is verplicht.', 'Last name is required.' ),
            'email'        => $this->t( 'E-mail is verplicht.', 'Email is required.' ),
            'phone'        => $this->t( 'Telefoonnummer is verplicht.', 'Phone number is required.' ),
            'street'       => $this->t( 'Straat is verplicht.', 'Street is required.' ),
            'house_number' => $this->t( 'Huisnummer is verplicht.', 'House number is required.' ),
            'postal_code'  => $this->t( 'Postcode is verplicht.', 'Postal code is required.' ),
            'city'         => $this->t( 'Woonplaats is verplicht.', 'City is required.' ),
        ];

        foreach ( $required as $field => $message ) {
            if ( empty( trim( $data[ $field ] ?? '' ) ) ) {
                $this->add_error( $field, $message );
            }
        }

        if ( ! empty( $data['email'] ) && ! is_email( $data['email'] ) ) {
            $this->add_error( 'email', $this->t( 'Ongeldig e-mailadres.', 'Invalid email address.' ) );
        }

        if ( empty( $data['privacy_accepted'] ) ) {
            $this->add_error( 'privacy_accepted', $this->t(
                'U moet akkoord gaan met de voorwaarden.',
                'You must agree to the terms.'
            ) );
        }

        return empty( $this->errors );
    }

    /**
     * Validate all steps at once (for server-side final check).
     */
    public function validate_all( array $data ): bool {
        $this->errors = [];

        $this->validate_vehicle( $data );
        $vehicle_errors = $this->errors;

        $this->validate_services( $data['services'] ?? [] );
        $service_errors = $this->errors;

        $this->validate_timeslot( $data );
        $time_errors = $this->errors;

        $this->validate_contact( $data );
        $contact_errors = $this->errors;

        $this->errors = array_merge( $vehicle_errors, $service_errors, $time_errors, $contact_errors );

        return empty( $this->errors );
    }

    public function get_errors(): array {
        return $this->errors;
    }

    private function add_error( string $field, string $message ): void {
        $this->errors[ $field ] = $message;
    }

    /**
     * Basic Dutch license plate validation.
     */
    private function is_valid_plate( string $plate ): bool {
        $clean = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $plate ) );
        return strlen( $clean ) >= 4 && strlen( $clean ) <= 8;
    }

    /**
     * Helper to get text by language.
     */
    private function t( string $nl, string $en ): string {
        return $this->lang === 'en' ? $en : $nl;
    }
}
