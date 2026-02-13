<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$templates = get_option( 'kivii_email_templates', [] );
$tab = $_GET['tab'] ?? 'customer_confirmation_nl';
$tabs = [
    'customer_confirmation_nl' => 'Klant bevestiging (NL)',
    'customer_confirmation_en' => 'Klant bevestiging (EN)',
    'garage_notification_nl'   => 'Garage notificatie (NL)',
    'garage_notification_en'   => 'Garage notificatie (EN)',
];
$current = $templates[ $tab ] ?? [];
$placeholders = ['{{booking_reference}}','{{first_name}}','{{last_name}}','{{full_name}}','{{email}}','{{phone}}','{{license_plate}}','{{mileage}}','{{appointment_date}}','{{appointment_time}}','{{total_price}}','{{total_duration}}','{{address}}','{{postal_code}}','{{city}}','{{remarks}}','{{services_table}}','{{company_name}}'];
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – E-mail Templates</h1>

    <nav class="nav-tab-wrapper">
        <?php foreach ( $tabs as $key => $label ) : ?>
        <a href="?page=kivii-emails&tab=<?php echo esc_attr( $key ); ?>" class="nav-tab <?php echo $tab === $key ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
        <?php endforeach; ?>
    </nav>

    <form method="post" action="options.php">
        <?php settings_fields( 'kivii_email_templates_group' ); ?>
        <?php
        // Preserve other templates
        foreach ( $templates as $k => $v ) {
            if ( $k !== $tab ) {
                echo '<input type="hidden" name="kivii_email_templates[' . esc_attr( $k ) . '][subject]" value="' . esc_attr( $v['subject'] ?? '' ) . '">';
                echo '<input type="hidden" name="kivii_email_templates[' . esc_attr( $k ) . '][body]" value="' . esc_attr( $v['body'] ?? '' ) . '">';
            }
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label for="email-subject">Onderwerp</label></th>
                <td><input type="text" id="email-subject" name="kivii_email_templates[<?php echo esc_attr( $tab ); ?>][subject]" value="<?php echo esc_attr( $current['subject'] ?? '' ); ?>" class="large-text" placeholder="Laat leeg voor standaard onderwerp"></td>
            </tr>
            <tr>
                <th><label for="email-body">Body (HTML)</label></th>
                <td><textarea id="email-body" name="kivii_email_templates[<?php echo esc_attr( $tab ); ?>][body]" rows="20" class="large-text code"><?php echo esc_textarea( $current['body'] ?? '' ); ?></textarea></td>
            </tr>
        </table>

        <div class="kivii-placeholder-list">
            <h3>Beschikbare placeholders:</h3>
            <p><?php echo implode( ' &nbsp; ', array_map( fn( $p ) => '<code>' . esc_html( $p ) . '</code>', $placeholders ) ); ?></p>
        </div>

        <?php submit_button( 'Opslaan' ); ?>
    </form>
</div>
