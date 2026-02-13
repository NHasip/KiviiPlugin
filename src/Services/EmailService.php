<?php

namespace Kivii\Services;

use Kivii\I18n\Translator;

/**
 * Email service for sending booking confirmations and notifications.
 */
class EmailService {

    /**
     * Send confirmation email to customer.
     */
    public function send_customer_confirmation( object $booking ): bool {
        $lang     = $booking->language ?? 'nl';
        $template = $this->get_template( "customer_confirmation_{$lang}" );
        $subject  = $this->replace_placeholders(
            $template['subject'] ?? $this->default_subject( 'customer', $lang ),
            $booking
        );
        $body = $this->replace_placeholders(
            $template['body'] ?? $this->default_body( 'customer', $lang ),
            $booking
        );

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->get_from_name() . ' <' . $this->get_from_email() . '>',
        ];

        return wp_mail( $booking->email, $subject, $body, $headers );
    }

    /**
     * Send notification email to garage.
     */
    public function send_garage_notification( object $booking ): bool {
        $lang      = $booking->language ?? 'nl';
        $template  = $this->get_template( "garage_notification_{$lang}" );
        $to        = $this->get_garage_email();

        if ( empty( $to ) ) {
            return false;
        }

        $subject = $this->replace_placeholders(
            $template['subject'] ?? $this->default_subject( 'garage', $lang ),
            $booking
        );
        $body = $this->replace_placeholders(
            $template['body'] ?? $this->default_body( 'garage', $lang ),
            $booking
        );

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];

        return wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * Replace placeholders in template.
     */
    private function replace_placeholders( string $text, object $booking ): string {
        $items_html = '';
        if ( ! empty( $booking->items ) ) {
            foreach ( $booking->items as $item ) {
                $items_html .= '<tr><td style="padding:8px;border-bottom:1px solid #eee;">' . esc_html( $item->title ) . '</td>';
                $items_html .= '<td style="padding:8px;border-bottom:1px solid #eee;text-align:right;">€ ' . number_format( (float) $item->price, 2, ',', '.' ) . '</td></tr>';
            }
        }

        $time_display = $booking->is_drop_off
            ? ( $booking->drop_off_time ?? '' )
            : ( $booking->appointment_time ? substr( $booking->appointment_time, 0, 5 ) : '' );

        $replacements = [
            '{{booking_reference}}' => $booking->booking_reference,
            '{{first_name}}'        => esc_html( $booking->first_name ),
            '{{last_name}}'         => esc_html( $booking->last_name ),
            '{{full_name}}'         => esc_html( $booking->first_name . ' ' . $booking->last_name ),
            '{{email}}'             => esc_html( $booking->email ),
            '{{phone}}'             => esc_html( $booking->phone ),
            '{{license_plate}}'     => esc_html( $booking->license_plate ),
            '{{mileage}}'           => number_format( (int) $booking->mileage, 0, '', '.' ),
            '{{appointment_date}}'  => date_i18n( 'd-m-Y', strtotime( $booking->appointment_date ) ),
            '{{appointment_time}}'  => $time_display,
            '{{total_price}}'       => '€ ' . number_format( (float) $booking->total_price, 2, ',', '.' ),
            '{{total_duration}}'    => (int) $booking->total_duration . ' min',
            '{{address}}'           => esc_html( $booking->street . ' ' . $booking->house_number . ( $booking->house_addition ? ' ' . $booking->house_addition : '' ) ),
            '{{postal_code}}'       => esc_html( $booking->postal_code ),
            '{{city}}'              => esc_html( $booking->city ),
            '{{remarks}}'           => nl2br( esc_html( $booking->remarks ?? '' ) ),
            '{{services_table}}'    => $items_html,
            '{{company_name}}'      => esc_html( $this->get_company_name() ),
        ];

        return str_replace( array_keys( $replacements ), array_values( $replacements ), $text );
    }

    private function get_template( string $key ): array {
        $templates = get_option( 'kivii_email_templates', [] );
        return $templates[ $key ] ?? [];
    }

    private function get_from_name(): string {
        return get_option( 'kivii_general', [] )['company_name'] ?? get_bloginfo( 'name' );
    }

    private function get_from_email(): string {
        return get_option( 'kivii_general', [] )['from_email'] ?? get_option( 'admin_email' );
    }

    private function get_garage_email(): string {
        return get_option( 'kivii_general', [] )['garage_email'] ?? get_option( 'admin_email' );
    }

    private function get_company_name(): string {
        return get_option( 'kivii_general', [] )['company_name'] ?? get_bloginfo( 'name' );
    }

    private function default_subject( string $type, string $lang ): string {
        if ( $type === 'customer' ) {
            return $lang === 'en'
                ? 'Appointment confirmation – {{booking_reference}}'
                : 'Bevestiging afspraak – {{booking_reference}}';
        }

        return $lang === 'en'
            ? 'New appointment – {{booking_reference}}'
            : 'Nieuwe afspraak – {{booking_reference}}';
    }

    private function default_body( string $type, string $lang ): string {
        if ( $type === 'customer' && $lang === 'nl' ) {
            return $this->load_default_template( 'customer-confirmation-nl' );
        }
        if ( $type === 'customer' && $lang === 'en' ) {
            return $this->load_default_template( 'customer-confirmation-en' );
        }
        if ( $type === 'garage' && $lang === 'nl' ) {
            return $this->load_default_template( 'garage-notification-nl' );
        }
        return $this->load_default_template( 'garage-notification-en' );
    }

    private function load_default_template( string $name ): string {
        $file = KIVII_PLUGIN_DIR . "templates/emails/{$name}.php";
        if ( file_exists( $file ) ) {
            ob_start();
            include $file;
            return ob_get_clean();
        }
        return '<p>{{full_name}}</p><p>Ref: {{booking_reference}}</p>';
    }
}
