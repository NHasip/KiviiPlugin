<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$options = get_option( 'kivii_styling', [] );
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Styling</h1>
    <p>Pas de kleuren en uitstraling aan van het boekingsformulier.</p>
    <form method="post" action="options.php">
        <?php settings_fields( 'kivii_styling_group' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="primary_color">Primaire kleur</label></th>
                <td><input type="text" id="primary_color" name="kivii_styling[primary_color]" value="<?php echo esc_attr( $options['primary_color'] ?? '#0B3D91' ); ?>" class="kivii-color-picker"></td>
            </tr>
            <tr>
                <th><label for="secondary_color">Secundaire kleur</label></th>
                <td><input type="text" id="secondary_color" name="kivii_styling[secondary_color]" value="<?php echo esc_attr( $options['secondary_color'] ?? '#E5A100' ); ?>" class="kivii-color-picker"></td>
            </tr>
            <tr>
                <th><label for="background_color">Achtergrondkleur</label></th>
                <td><input type="text" id="background_color" name="kivii_styling[background_color]" value="<?php echo esc_attr( $options['background_color'] ?? '#F8F9FA' ); ?>" class="kivii-color-picker"></td>
            </tr>
            <tr>
                <th><label for="text_color">Tekstkleur</label></th>
                <td><input type="text" id="text_color" name="kivii_styling[text_color]" value="<?php echo esc_attr( $options['text_color'] ?? '#1A1A2E' ); ?>" class="kivii-color-picker"></td>
            </tr>
            <tr>
                <th><label for="border_radius">Border radius (px)</label></th>
                <td><input type="number" id="border_radius" name="kivii_styling[border_radius]" value="<?php echo esc_attr( $options['border_radius'] ?? '8' ); ?>" min="0" max="30" class="small-text"></td>
            </tr>
        </table>
        <?php submit_button( 'Opslaan' ); ?>
    </form>
</div>
