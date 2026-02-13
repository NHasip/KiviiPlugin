<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$options = get_option( 'kivii_general', [] );
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Algemeen</h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'kivii_general_group' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="company_name">Bedrijfsnaam</label></th>
                <td><input type="text" id="company_name" name="kivii_general[company_name]" value="<?php echo esc_attr( $options['company_name'] ?? '' ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="language">Standaard taal</label></th>
                <td>
                    <select id="language" name="kivii_general[language]">
                        <option value="nl" <?php selected( $options['language'] ?? 'nl', 'nl' ); ?>>Nederlands</option>
                        <option value="en" <?php selected( $options['language'] ?? 'nl', 'en' ); ?>>English</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="privacy_url">Privacy URL</label></th>
                <td><input type="url" id="privacy_url" name="kivii_general[privacy_url]" value="<?php echo esc_url( $options['privacy_url'] ?? '' ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="garage_email">Garage e-mailadres</label></th>
                <td><input type="email" id="garage_email" name="kivii_general[garage_email]" value="<?php echo esc_attr( $options['garage_email'] ?? get_option( 'admin_email' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="from_email">Afzender e-mailadres</label></th>
                <td><input type="email" id="from_email" name="kivii_general[from_email]" value="<?php echo esc_attr( $options['from_email'] ?? get_option( 'admin_email' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="retention_days">Data retentie (dagen)</label></th>
                <td><input type="number" id="retention_days" name="kivii_general[retention_days]" value="<?php echo esc_attr( $options['retention_days'] ?? 365 ); ?>" min="30" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="remove_data_on_uninstall">Verwijder data bij deïnstallatie</label></th>
                <td><input type="checkbox" id="remove_data_on_uninstall" name="kivii_general[remove_data_on_uninstall]" value="1" <?php checked( $options['remove_data_on_uninstall'] ?? 0, 1 ); ?>></td>
            </tr>
        </table>
        <?php submit_button( 'Opslaan' ); ?>
    </form>
</div>
