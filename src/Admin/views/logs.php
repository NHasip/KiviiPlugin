<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$repo    = new \Kivii\Database\LogRepository();
$page    = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
$filters = [
    'level'  => sanitize_text_field( $_GET['level'] ?? '' ),
    'search' => sanitize_text_field( $_GET['s'] ?? '' ),
];
$result  = $repo->get_all( $filters, $page, 50 );
$logs    = $result['items'];
$nonce   = wp_create_nonce( 'kivii_admin_nonce' );
?>
<div class="wrap kivii-admin-wrap">
    <h1>Kivii Online Afspraak – Logs</h1>

    <div class="kivii-filters">
        <form method="get">
            <input type="hidden" name="page" value="kivii-logs">
            <select name="level">
                <option value="">Alle niveaus</option>
                <option value="info" <?php selected( $filters['level'], 'info' ); ?>>Info</option>
                <option value="warning" <?php selected( $filters['level'], 'warning' ); ?>>Warning</option>
                <option value="error" <?php selected( $filters['level'], 'error' ); ?>>Error</option>
            </select>
            <input type="text" name="s" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="Zoeken...">
            <button class="button" type="submit">Filter</button>
        </form>
        <button class="button" id="kivii-clear-logs">Logs wissen</button>
    </div>

    <table class="wp-list-table widefat striped">
        <thead><tr><th>Datum</th><th>Niveau</th><th>Bericht</th><th>Context</th></tr></thead>
        <tbody>
        <?php if ( empty( $logs ) ) : ?>
            <tr><td colspan="4">Geen logs gevonden.</td></tr>
        <?php else : ?>
            <?php foreach ( $logs as $log ) : ?>
            <tr>
                <td><?php echo esc_html( date_i18n( 'd-m-Y H:i:s', strtotime( $log->created_at ) ) ); ?></td>
                <td><span class="kivii-log-<?php echo esc_attr( $log->level ); ?>"><?php echo esc_html( strtoupper( $log->level ) ); ?></span></td>
                <td><?php echo esc_html( $log->message ); ?></td>
                <td><?php if ( $log->context ) : ?><details><summary>Toon</summary><pre><?php echo esc_html( $log->context ); ?></pre></details><?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<script>var kiviiNonce = '<?php echo esc_js( $nonce ); ?>';</script>
