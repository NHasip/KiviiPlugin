<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$options = get_option( 'kivii_booking_rules', [] );
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Boekingsregels</h1>
    <form method="post" action="options.php">
        <?php settings_fields( 'kivii_booking_rules_group' ); ?>
        <table class="form-table">
            <tr>
                <th><label for="drop_off_times">Brengmoment tijden</label></th>
                <td>
                    <textarea id="drop_off_times" name="kivii_booking_rules[drop_off_times]" rows="4" class="regular-text"><?php echo esc_textarea( $options['drop_off_times'] ?? "09:00\n13:00" ); ?></textarea>
                    <p class="description">Eén tijd per regel, bijv. 09:00</p>
                </td>
            </tr>
            <tr>
                <th><label for="min_lead_hours">Minimale voorbereidingstijd (uren)</label></th>
                <td><input type="number" id="min_lead_hours" name="kivii_booking_rules[min_lead_hours]" value="<?php echo esc_attr( $options['min_lead_hours'] ?? 24 ); ?>" min="0" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="max_advance_days">Maximaal vooruit boeken (dagen)</label></th>
                <td><input type="number" id="max_advance_days" name="kivii_booking_rules[max_advance_days]" value="<?php echo esc_attr( $options['max_advance_days'] ?? 60 ); ?>" min="7" class="small-text"></td>
            </tr>
            <tr>
                <th><label for="calendar_note_nl">Kalender opmerking (NL)</label></th>
                <td><input type="text" id="calendar_note_nl" name="kivii_booking_rules[calendar_note_nl]" value="<?php echo esc_attr( $options['calendar_note_nl'] ?? 'Wij houden altijd ruimte vrij voor spoedgevallen.' ); ?>" class="large-text"></td>
            </tr>
            <tr>
                <th><label for="calendar_note_en">Kalender opmerking (EN)</label></th>
                <td><input type="text" id="calendar_note_en" name="kivii_booking_rules[calendar_note_en]" value="<?php echo esc_attr( $options['calendar_note_en'] ?? 'We always keep room for emergencies.' ); ?>" class="large-text"></td>
            </tr>
        </table>
        <?php submit_button( 'Opslaan' ); ?>
    </form>
</div>
