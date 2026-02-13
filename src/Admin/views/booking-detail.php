<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$id      = absint( $_GET['booking_id'] ?? 0 );
$repo    = new \Kivii\Database\BookingRepository();
$booking = $repo->get_by_id( $id );
$nonce   = wp_create_nonce( 'kivii_admin_nonce' );

if ( ! $booking ) {
    echo '<div class="wrap"><h1>Boeking niet gevonden</h1><p><a href="?page=kivii-bookings">&larr; Terug naar overzicht</a></p></div>';
    return;
}
?>
<div class="wrap kivii-admin-wrap">
    <h1>Boeking: <?php echo esc_html( $booking->booking_reference ); ?></h1>
    <p><a href="?page=kivii-bookings">&larr; Terug naar overzicht</a></p>

    <div class="kivii-detail-grid">
        <div class="kivii-detail-card">
            <h2>Status</h2>
            <p><span class="kivii-status-badge kivii-status-<?php echo esc_attr( $booking->status ); ?>"><?php echo esc_html( ucfirst( $booking->status ) ); ?></span></p>
            <p>Kivii gesynchroniseerd: <?php echo $booking->kivii_synced ? '✅ Ja' : '❌ Nee'; ?></p>
            <div class="kivii-detail-actions">
                <button class="button" id="kivii-resend-api" data-id="<?php echo $id; ?>">Opnieuw versturen naar Kivii</button>
                <button class="button" id="kivii-resend-email" data-id="<?php echo $id; ?>">E-mail opnieuw versturen</button>
            </div>
        </div>

        <div class="kivii-detail-card">
            <h2>Autogegevens</h2>
            <p><strong>Kenteken:</strong> <?php echo esc_html( $booking->license_plate ); ?></p>
            <p><strong>Km-stand:</strong> <?php echo number_format( (int) $booking->mileage, 0, '', '.' ); ?> km</p>
        </div>

        <div class="kivii-detail-card">
            <h2>Afspraak</h2>
            <p><strong>Datum:</strong> <?php echo esc_html( date_i18n( 'd-m-Y', strtotime( $booking->appointment_date ) ) ); ?></p>
            <?php if ( $booking->is_drop_off ) : ?>
                <p><strong>Brengmoment:</strong> <?php echo esc_html( $booking->drop_off_time ); ?> (auto achterlaten)</p>
            <?php else : ?>
                <p><strong>Tijd:</strong> <?php echo esc_html( substr( $booking->appointment_time ?? '', 0, 5 ) ); ?></p>
            <?php endif; ?>
            <p><strong>Totale prijs:</strong> € <?php echo number_format( (float) $booking->total_price, 2, ',', '.' ); ?></p>
            <p><strong>Geschatte duur:</strong> <?php echo (int) $booking->total_duration; ?> minuten</p>
        </div>

        <div class="kivii-detail-card">
            <h2>Contactgegevens</h2>
            <p><strong>Naam:</strong> <?php echo esc_html( $booking->first_name . ' ' . $booking->last_name ); ?></p>
            <p><strong>E-mail:</strong> <?php echo esc_html( $booking->email ); ?></p>
            <p><strong>Telefoon:</strong> <?php echo esc_html( $booking->phone ); ?></p>
            <p><strong>Adres:</strong> <?php echo esc_html( $booking->street . ' ' . $booking->house_number . ( $booking->house_addition ? ' ' . $booking->house_addition : '' ) ); ?></p>
            <p><strong>Postcode:</strong> <?php echo esc_html( $booking->postal_code ); ?> <?php echo esc_html( $booking->city ); ?></p>
            <?php if ( $booking->remarks ) : ?>
                <p><strong>Opmerkingen:</strong><br><?php echo nl2br( esc_html( $booking->remarks ) ); ?></p>
            <?php endif; ?>
        </div>

        <div class="kivii-detail-card kivii-detail-full">
            <h2>Werkzaamheden</h2>
            <table class="wp-list-table widefat striped">
                <thead><tr><th>Werkzaamheid</th><th>Type</th><th>Duur</th><th>Prijs</th></tr></thead>
                <tbody>
                <?php foreach ( $booking->items as $item ) : ?>
                <tr>
                    <td><?php echo esc_html( $item->title ); ?></td>
                    <td><?php echo $item->is_addon ? 'Add-on' : 'Hoofddienst'; ?></td>
                    <td><?php echo (int) $item->duration_minutes; ?> min</td>
                    <td>€ <?php echo number_format( (float) $item->price, 2, ',', '.' ); ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ( $booking->kivii_response ) : ?>
        <div class="kivii-detail-card kivii-detail-full">
            <h2>Kivii API Response</h2>
            <pre><?php echo esc_html( $booking->kivii_response ); ?></pre>
        </div>
        <?php endif; ?>
    </div>

    <p class="description">
        Aangemaakt: <?php echo esc_html( date_i18n( 'd-m-Y H:i:s', strtotime( $booking->created_at ) ) ); ?> |
        Taal: <?php echo esc_html( strtoupper( $booking->language ) ); ?> |
        Bron: <?php echo esc_html( $booking->source_domain ); ?>
    </p>
</div>
<script>var kiviiNonce = '<?php echo esc_js( $nonce ); ?>';</script>
