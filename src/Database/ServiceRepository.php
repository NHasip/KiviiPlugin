<?php

namespace Kivii\Database;

/**
 * Repository for services and service categories.
 */
class ServiceRepository {

    private \wpdb $db;
    private string $services_table;
    private string $categories_table;

    public function __construct() {
        global $wpdb;
        $this->db               = $wpdb;
        $this->services_table   = $wpdb->prefix . 'kiviiweb_services';
        $this->categories_table = $wpdb->prefix . 'kiviiweb_service_categories';
    }

    // ── Categories ───────────────────────────────────────

    public function get_categories(): array {
        return $this->db->get_results(
            "SELECT * FROM {$this->categories_table} ORDER BY sort_order ASC, id ASC"
        );
    }

    public function get_active_categories(): array {
        return $this->db->get_results(
            "SELECT * FROM {$this->categories_table} WHERE is_active = 1 ORDER BY sort_order ASC"
        );
    }

    public function get_category( int $id ): ?object {
        return $this->db->get_row(
            $this->db->prepare( "SELECT * FROM {$this->categories_table} WHERE id = %d", $id )
        );
    }

    public function create_category( array $data ): int|false {
        $result = $this->db->insert( $this->categories_table, [
            'name_nl'    => sanitize_text_field( $data['name_nl'] ),
            'name_en'    => sanitize_text_field( $data['name_en'] ?? '' ),
            'sort_order' => absint( $data['sort_order'] ?? 0 ),
            'is_active'  => (int) ( $data['is_active'] ?? 1 ),
        ] );

        return $result ? $this->db->insert_id : false;
    }

    public function update_category( int $id, array $data ): bool {
        return (bool) $this->db->update(
            $this->categories_table,
            [
                'name_nl'    => sanitize_text_field( $data['name_nl'] ),
                'name_en'    => sanitize_text_field( $data['name_en'] ?? '' ),
                'sort_order' => absint( $data['sort_order'] ?? 0 ),
                'is_active'  => (int) ( $data['is_active'] ?? 1 ),
            ],
            [ 'id' => $id ]
        );
    }

    public function delete_category( int $id ): bool {
        // Delete associated services first
        $this->db->delete( $this->services_table, [ 'category_id' => $id ] );
        return (bool) $this->db->delete( $this->categories_table, [ 'id' => $id ] );
    }

    // ── Services ─────────────────────────────────────────

    public function get_services( ?int $category_id = null ): array {
        if ( $category_id ) {
            return $this->db->get_results( $this->db->prepare(
                "SELECT * FROM {$this->services_table} WHERE category_id = %d ORDER BY sort_order ASC",
                $category_id
            ) );
        }

        return $this->db->get_results(
            "SELECT * FROM {$this->services_table} ORDER BY sort_order ASC, id ASC"
        );
    }

    public function get_service( int $id ): ?object {
        return $this->db->get_row(
            $this->db->prepare( "SELECT * FROM {$this->services_table} WHERE id = %d", $id )
        );
    }

    public function get_services_by_ids( array $ids ): array {
        if ( empty( $ids ) ) {
            return [];
        }

        $placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
        return $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->services_table} WHERE id IN ($placeholders)",
                ...$ids
            )
        );
    }

    public function create_service( array $data ): int|false {
        $result = $this->db->insert( $this->services_table, [
            'category_id'      => absint( $data['category_id'] ),
            'title_nl'         => sanitize_text_field( $data['title_nl'] ),
            'title_en'         => sanitize_text_field( $data['title_en'] ?? '' ),
            'description_nl'   => sanitize_textarea_field( $data['description_nl'] ?? '' ),
            'description_en'   => sanitize_textarea_field( $data['description_en'] ?? '' ),
            'long_desc_nl'     => wp_kses_post( $data['long_desc_nl'] ?? '' ),
            'long_desc_en'     => wp_kses_post( $data['long_desc_en'] ?? '' ),
            'price'            => floatval( $data['price'] ?? 0 ),
            'duration_minutes' => absint( $data['duration_minutes'] ?? 30 ),
            'is_addon'         => (int) ( $data['is_addon'] ?? 0 ),
            'is_active'        => (int) ( $data['is_active'] ?? 1 ),
            'sort_order'       => absint( $data['sort_order'] ?? 0 ),
        ] );

        return $result ? $this->db->insert_id : false;
    }

    public function update_service( int $id, array $data ): bool {
        return (bool) $this->db->update(
            $this->services_table,
            [
                'category_id'      => absint( $data['category_id'] ),
                'title_nl'         => sanitize_text_field( $data['title_nl'] ),
                'title_en'         => sanitize_text_field( $data['title_en'] ?? '' ),
                'description_nl'   => sanitize_textarea_field( $data['description_nl'] ?? '' ),
                'description_en'   => sanitize_textarea_field( $data['description_en'] ?? '' ),
                'long_desc_nl'     => wp_kses_post( $data['long_desc_nl'] ?? '' ),
                'long_desc_en'     => wp_kses_post( $data['long_desc_en'] ?? '' ),
                'price'            => floatval( $data['price'] ?? 0 ),
                'duration_minutes' => absint( $data['duration_minutes'] ?? 30 ),
                'is_addon'         => (int) ( $data['is_addon'] ?? 0 ),
                'is_active'        => (int) ( $data['is_active'] ?? 1 ),
                'sort_order'       => absint( $data['sort_order'] ?? 0 ),
            ],
            [ 'id' => $id ]
        );
    }

    public function delete_service( int $id ): bool {
        return (bool) $this->db->delete( $this->services_table, [ 'id' => $id ] );
    }

    /**
     * Get all active services grouped by category.
     */
    public function get_all_active_grouped(): array {
        $categories = $this->get_active_categories();
        $result     = [];

        foreach ( $categories as $cat ) {
            $services = $this->db->get_results( $this->db->prepare(
                "SELECT * FROM {$this->services_table}
                 WHERE category_id = %d AND is_active = 1
                 ORDER BY sort_order ASC",
                $cat->id
            ) );

            $result[] = [
                'id'       => (int) $cat->id,
                'name_nl'  => $cat->name_nl,
                'name_en'  => $cat->name_en,
                'services' => array_map( function ( $s ) {
                    return [
                        'id'              => (int) $s->id,
                        'title_nl'        => $s->title_nl,
                        'title_en'        => $s->title_en,
                        'description_nl'  => $s->description_nl,
                        'description_en'  => $s->description_en,
                        'long_desc_nl'    => $s->long_desc_nl,
                        'long_desc_en'    => $s->long_desc_en,
                        'price'           => (float) $s->price,
                        'duration_minutes' => (int) $s->duration_minutes,
                        'is_addon'        => (bool) $s->is_addon,
                    ];
                }, $services ),
            ];
        }

        return $result;
    }

    /**
     * Export all services as JSON-serializable array.
     */
    public function export_all(): array {
        $categories = $this->get_categories();
        $all        = [];

        foreach ( $categories as $cat ) {
            $services = $this->get_services( (int) $cat->id );
            $all[]    = [
                'category' => (array) $cat,
                'services' => array_map( fn( $s ) => (array) $s, $services ),
            ];
        }

        return $all;
    }

    /**
     * Import services from JSON array (replaces all).
     */
    public function import_all( array $data ): bool {
        // Clear existing
        $this->db->query( "TRUNCATE TABLE {$this->services_table}" );
        $this->db->query( "TRUNCATE TABLE {$this->categories_table}" );

        foreach ( $data as $group ) {
            $cat_data   = $group['category'] ?? [];
            $cat_id     = $this->create_category( $cat_data );

            if ( $cat_id && ! empty( $group['services'] ) ) {
                foreach ( $group['services'] as $svc ) {
                    $svc['category_id'] = $cat_id;
                    $this->create_service( $svc );
                }
            }
        }

        return true;
    }
}
