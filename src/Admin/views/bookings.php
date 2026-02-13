<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$repo    = new \Kivii\Database\BookingRepository();
$page    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$filters = [
    'status'    => sanitize_text_field( $_GET['status'] ?? '' ),
    'date_from' => sanitize_text_field( $_GET['date_from'] ?? '' ),
    'date_to'   => sanitize_text_field( $_GET['date_to'] ?? '' ),
    'search'    => sanitize_text_field( $_GET['s'] ?? '' ),
];
$result   = $repo->get_all( $filters, $page, 20 );
$bookings = $result['items'];
$nonce    = wp_create_nonce( 'kivii_admin_nonce' );
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Boekingen</h1>

    <div class="kivii-filters">
        <form method="get">
            <input type="hidden" name="page" value="kivii-bookings">
            <input type="text" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="Zoek op kenteken, naam, e-mail...">
            <select name="status">
                <option value="">Alle statussen</option>
                <option value="pending" <?php selected( $filters['status'], 'pending' ); ?>>Pending</option>
                <option value="confirmed" <?php selected( $filters['status'], 'confirmed' ); ?>>Confirmed</option>
                <option value="cancelled" <?php selected( $filters['status'], 'cancelled' ); ?>>Cancelled</option>
            </select>
            <input type="date" name="date_from" value="<?php echo esc_attr( $filters['date_from'] ); ?>" placeholder="Van">
            <input type="date" name="date_to" value="<?php echo esc_attr( $filters['date_to'] ); ?>" placeholder="Tot">
            <button class="button" type="submit">Filter</button>
        </form>
        <button class="button" id="kivii-export-csv">CSV Export</button>
    </div>

    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th>Referentie</th>
                <th>Kenteken</th>
                <th>Klant</th>
                <th>Datum</th>
                <th>Tijd</th>
                <th>Totaal</th>
                <th>Status</th>
                <th>Kivii sync</th>
                <th>Aangemaakt</th>
            </tr>
        </thead>
        <tbody>
        <?php if ( empty( $bookings ) ) : ?>
            <tr><td colspan="9">Geen boekingen gevonden.</td></tr>
        <?php else : ?>
            <?php foreach ( $bookings as $b ) : ?>
            <tr>
                <td><a href="?page=kivii-bookings&booking_id=<?php echo (int) $b->id; ?>"><strong><?php echo esc_html( substr( $b->booking_reference, 0, 8 ) ); ?>...</strong></a></td>
                <td><?php echo esc_html( $b->license_plate ); ?></td>
                <td><?php echo esc_html( $b->first_name . ' ' . $b->last_name ); ?></td>
                <td><?php echo esc_html( date_i18n( 'd-m-Y', strtotime( $b->appointment_date ) ) ); ?></td>
                <td><?php echo $b->is_drop_off ? esc_html( $b->drop_off_time ) . ' (achterlaten)' : esc_html( substr( $b->appointment_time ?? '', 0, 5 ) ); ?></td>
                <td>€ <?php echo number_format( (float) $b->total_price, 2, ',', '.' ); ?></td>
                <td><span class="kivii-status-badge kivii-status-<?php echo esc_attr( $b->status ); ?>"><?php echo esc_html( ucfirst( $b->status ) ); ?></span></td>
                <td><?php echo $b->kivii_synced ? '✅' : '❌'; ?></td>
                <td><?php echo esc_html( date_i18n( 'd-m-Y H:i', strtotime( $b->created_at ) ) ); ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <?php if ( $result['pages'] > 1 ) : ?>
    <div class="tablenav">
        <div class="tablenav-pages">
            <?php for ( $i = 1; $i <= $result['pages']; $i++ ) : ?>
                <?php if ( $i === $page ) : ?>
                    <strong><?php echo $i; ?></strong>
                <?php else : ?>
                    <a href="?page=kivii-bookings&paged=<?php echo $i; ?>&s=<?php echo urlencode( $filters['search'] ); ?>&status=<?php echo urlencode( $filters['status'] ); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<script>var kiviiNonce = '<?php echo esc_js( $nonce ); ?>';</script>
