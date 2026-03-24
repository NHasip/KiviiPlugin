<?php

namespace Kivii\I18n;

/**
 * Translator – provides configurable texts for the frontend.
 */
class Translator {

    private static ?Translator $instance = null;
    private string $lang = 'nl';
    private array $texts = [];

    public static function instance(): self {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void {
        $general    = get_option( 'kivii_general', [] );
        $this->lang = $general['language'] ?? 'nl';
        $this->texts = $this->load_texts();
    }

    public function get( string $key, string $default = '' ): string {
        return $this->texts[ $key ] ?? $default;
    }

    public function get_all_texts(): array {
        return $this->texts;
    }

    private function load_texts(): array {
        $custom = get_option( "kivii_texts_{$this->lang}", [] );
        $defaults = $this->defaults();

        return array_merge( $defaults[ $this->lang ] ?? $defaults['nl'], $custom );
    }

    public function defaults(): array {
        return [
            'nl' => [
                // General
                'page_title'           => 'Afspraak maken',
                'next_button'          => 'Volgende',
                'prev_button'          => 'Vorige',
                'submit_button'        => 'Afspraak inplannen',
                'loading'              => 'Laden...',

                // Progress bar
                'step_1_label'         => 'Autogegevens',
                'step_2_label'         => 'Werkzaamheden',
                'step_3_label'         => 'Tijdstip',
                'step_4_label'         => 'Contactgegevens',

                // Step 1
                'step1_title'          => 'Uw autogegevens',
                'step1_subtitle'       => 'Voer uw kenteken en kilometerstand in.',
                'license_plate_label'  => 'Kenteken',
                'license_plate_placeholder' => 'XX-999-X',
                'mileage_label'        => 'Km-stand',
                'mileage_placeholder'  => 'bijv. 85000',

                // Step 2
                'step2_title'          => 'Selecteer uw werkzaamheden',
                'step2_subtitle'       => 'Kies werkzaamheden die aan uw auto moeten gebeuren.',
                'min_service_note'     => 'Selecteer minimaal één werkzaamheid.',
                'addons_title'         => 'Aanvullende werkzaamheden',
                'total_price_label'    => 'Totaal',
                'total_duration_label' => 'Geschatte duur',
                'show_details'         => 'Meer informatie',
                'hide_details'         => 'Minder informatie',

                // Step 3
                'step3_title'          => 'Plan uw tijdstip',
                'step3_subtitle'       => 'Selecteer een beschikbare dag.',
                'drop_off_question'    => 'Laat u de auto achter?',
                'drop_off_yes'         => 'Ja, ik laat de auto achter',
                'drop_off_no'          => 'Nee, ik wacht op de auto',
                'select_time'          => 'Kies een tijdstip',
                'select_drop_off_time' => 'Kies een brengmoment',
                'day_available'        => 'Beschikbaar',
                'day_almost_full'      => 'Bijna vol',
                'day_full'             => 'Vol',
                'no_slots'             => 'Geen tijdsloten beschikbaar voor deze dag.',
                'calendar_note'        => 'Wij houden altijd ruimte vrij voor spoedgevallen.',
                'error_drop_off_choice'=> 'Maak eerst een keuze of u de auto achterlaat.',

                // Step 4
                'step4_title'          => 'Uw contactgegevens',
                'step4_subtitle'       => 'Vul uw gegevens in om de afspraak te bevestigen.',
                'first_name'           => 'Voornaam',
                'last_name'            => 'Achternaam',
                'email'                => 'E-mailadres',
                'phone'                => 'Telefoonnummer',
                'street'               => 'Straat',
                'house_number'         => 'Huisnummer',
                'house_addition'       => 'Toevoeging',
                'postal_code'          => 'Postcode',
                'city'                 => 'Woonplaats',
                'remarks'              => 'Opmerkingen',
                'remarks_placeholder'  => 'Heeft u nog opmerkingen of vragen?',
                'privacy_label'        => 'Ik ga akkoord met de',
                'privacy_link_text'    => 'voorwaarden',

                // Sidebar
                'overview_title'       => 'Overzicht',
                'overview_vehicle'     => 'Autogegevens',
                'overview_services'    => 'Werkzaamheden',
                'overview_timeslot'    => 'Tijdstip',
                'overview_contact'     => 'Contactgegevens',

                // Confirmation
                'confirm_title'        => 'Afspraak bevestigd!',
                'confirm_message'      => 'Uw afspraak is succesvol ingepland. U ontvangt een bevestiging per e-mail.',
                'confirm_reference'    => 'Referentienummer',
                'confirm_new'          => 'Nieuwe afspraak maken',

                // Errors
                'generic_error'        => 'Er is een fout opgetreden. Probeer het opnieuw.',
                'api_error'            => 'Kan geen verbinding maken met de server.',
            ],
            'en' => [
                'page_title'           => 'Schedule appointment',
                'next_button'          => 'Next',
                'prev_button'          => 'Previous',
                'submit_button'        => 'Schedule appointment',
                'loading'              => 'Loading...',

                'step_1_label'         => 'Vehicle info',
                'step_2_label'         => 'Services',
                'step_3_label'         => 'Date & time',
                'step_4_label'         => 'Contact details',

                'step1_title'          => 'Your vehicle details',
                'step1_subtitle'       => 'Enter your license plate and mileage.',
                'license_plate_label'  => 'License plate',
                'license_plate_placeholder' => 'XX-999-X',
                'mileage_label'        => 'Mileage',
                'mileage_placeholder'  => 'e.g. 85000',

                'step2_title'          => 'Select your services',
                'step2_subtitle'       => 'Choose the services you need for your vehicle.',
                'min_service_note'     => 'Please select at least one service.',
                'addons_title'         => 'Additional services',
                'total_price_label'    => 'Total',
                'total_duration_label' => 'Estimated duration',
                'show_details'         => 'More info',
                'hide_details'         => 'Less info',

                'step3_title'          => 'Choose your date & time',
                'step3_subtitle'       => 'Select an available day.',
                'drop_off_question'    => 'Will you leave the car?',
                'drop_off_yes'         => 'Yes, I will leave the car',
                'drop_off_no'          => 'No, I will wait',
                'select_time'          => 'Choose a time slot',
                'select_drop_off_time' => 'Choose a drop-off time',
                'day_available'        => 'Available',
                'day_almost_full'      => 'Almost full',
                'day_full'             => 'Full',
                'no_slots'             => 'No time slots available for this day.',
                'calendar_note'        => 'We always keep room for emergencies.',
                'error_drop_off_choice'=> 'Please choose first whether you will leave the car.',

                'step4_title'          => 'Your contact details',
                'step4_subtitle'       => 'Enter your details to confirm the appointment.',
                'first_name'           => 'First name',
                'last_name'            => 'Last name',
                'email'                => 'Email address',
                'phone'                => 'Phone number',
                'street'               => 'Street',
                'house_number'         => 'House number',
                'house_addition'       => 'Addition',
                'postal_code'          => 'Postal code',
                'city'                 => 'City',
                'remarks'              => 'Remarks',
                'remarks_placeholder'  => 'Do you have any remarks or questions?',
                'privacy_label'        => 'I agree to the',
                'privacy_link_text'    => 'terms and conditions',

                'overview_title'       => 'Overview',
                'overview_vehicle'     => 'Vehicle info',
                'overview_services'    => 'Services',
                'overview_timeslot'    => 'Date & time',
                'overview_contact'     => 'Contact details',

                'confirm_title'        => 'Appointment confirmed!',
                'confirm_message'      => 'Your appointment has been successfully scheduled. You will receive a confirmation by email.',
                'confirm_reference'    => 'Reference number',
                'confirm_new'          => 'Schedule another appointment',

                'generic_error'        => 'An error occurred. Please try again.',
                'api_error'            => 'Cannot connect to the server.',
            ],
        ];
    }
}
