<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$options = get_option( 'kivii_api', [] );
$nonce = wp_create_nonce( 'kivii_admin_nonce' );
$mapping = get_option( 'kivii_field_mapping', [] );
$fields = ['booking_reference','license_plate','mileage','services','total_duration','total_price','appointment_date','appointment_time','is_drop_off','drop_off_time','first_name','last_name','email','phone','street','house_number','house_addition','postal_code','city','remarks','language','source','domain'];
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Integratie Kivii</h1>

    <h2>API Instellingen</h2>
    <form method="post" action="options.php">
        <?php settings_fields( 'kivii_api_group' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="base_url">API Base URL</label></th>
                <td><input type="url" id="base_url" name="kivii_api[base_url]" value="<?php echo esc_url( $options['base_url'] ?? '' ); ?>" class="regular-text" placeholder="https://api.kivii.nl/v1"></td>
            </tr>
            <tr>
                <th><label for="token">API Token</label></th>
                <td><input type="password" id="token" name="kivii_api[token]" value="<?php echo esc_attr( $options['token'] ?? '' ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="timeout">Timeout (seconden)</label></th>
                <td><input type="number" id="timeout" name="kivii_api[timeout]" value="<?php echo esc_attr( $options['timeout'] ?? 30 ); ?>" min="5" max="120" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="retries">Retries</label></th>
                <td><input type="number" id="retries" name="kivii_api[retries]" value="<?php echo esc_attr( $options['retries'] ?? 2 ); ?>" min="0" max="5" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="use_mock">Gebruik mock adapter</label></th>
                <td><input type="checkbox" id="use_mock" name="kivii_api[use_mock]" value="1" <?php checked( $options['use_mock'] ?? 0, 1 ); ?>>
                <p class="description">Voor development: gebruik dummy data in plaats van echte API.</p></td>
            </tr>
        </table>
        <?php submit_button( 'Opslaan' ); ?>
    </form>

    <hr>

    <h2>Test verbinding</h2>
    <button class="button button-primary" id="kivii-test-api">Test API verbinding</button>
    <span id="kivii-test-result"></span>

    <hr>

    <h2>Veld Mapping</h2>
    <p>Optioneel: map interne veldnamen naar Kivii API veldnamen.</p>
    <form method="post" action="options.php">
        <?php settings_fields( 'kivii_field_mapping_group' ); ?>
        <table class="wp-list-table widefat striped">
            <thead><tr><th>Intern veld</th><th>Kivii API veld</th></tr></thead>
            <tbody>
            <?php foreach ( $fields as $field ) : ?>
            <tr>
                <td><code><?php echo esc_html( $field ); ?></code></td>
                <td><input type="text" name="kivii_field_mapping[<?php echo esc_attr( $field ); ?>]" value="<?php echo esc_attr( $mapping[ $field ] ?? $field ); ?>" class="regular-text"></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php submit_button( 'Mapping opslaan' ); ?>
    </form>
</div>
<script>var kiviiNonce = '<?php echo esc_js( $nonce ); ?>';</script>
