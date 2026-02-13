<?php

namespace Kivii\Database;

/**
 * Repository for plugin logs.
 */
class LogRepository {

    private \wpdb $db;
    private string $table;

    public function __construct() {
        global $wpdb;
        $this->db    = $wpdb;
        $this->table = $wpdb->prefix . 'kiviiweb_logs';
    }

    /**
     * Write a log entry.
     */
    public function log( string $level, string $message, array $context = [] ): void {
        $this->db->insert( $this->table, [
            'level'   => sanitize_text_field( $level ),
            'message' => sanitize_textarea_field( $message ),
            'context' => wp_json_encode( $context ),
        ] );
    }

    public function info( string $message, array $context = [] ): void {
        $this->log( 'info', $message, $context );
    }

    public function warning( string $message, array $context = [] ): void {
        $this->log( 'warning', $message, $context );
    }

    public function error( string $message, array $context = [] ): void {
        $this->log( 'error', $message, $context );
    }

    /**
     * Get logs with pagination and filters.
     */
    public function get_all( array $filters = [], int $page = 1, int $per_page = 50 ): array {
        $where  = '1=1';
        $params = [];

        if ( ! empty( $filters['level'] ) ) {
            $where   .= ' AND level = %s';
            $params[] = $filters['level'];
        }

        if ( ! empty( $filters['search'] ) ) {
            $search   = '%' . $this->db->esc_like( $filters['search'] ) . '%';
            $where   .= ' AND message LIKE %s';
            $params[] = $search;
        }

        $offset = ( $page - 1 ) * $per_page;

        $total_query = "SELECT COUNT(*) FROM {$this->table} WHERE {$where}";
        $total = $params
            ? (int) $this->db->get_var( $this->db->prepare( $total_query, ...$params ) )
            : (int) $this->db->get_var( $total_query );

        $query      = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $all_params = array_merge( $params, [ $per_page, $offset ] );

        $items = $this->db->get_results( $this->db->prepare( $query, ...$all_params ) );

        return [
            'items'    => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $per_page,
            'pages'    => (int) ceil( $total / $per_page ),
        ];
    }

    /**
     * Clear all logs.
     */
    public function clear(): void {
        $this->db->query( "TRUNCATE TABLE {$this->table}" );
    }

    /**
     * Delete old logs.
     */
    public function delete_old( int $days ): int {
        $date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );
        return (int) $this->db->query(
            $this->db->prepare( "DELETE FROM {$this->table} WHERE created_at < %s", $date )
        );
    }
}
