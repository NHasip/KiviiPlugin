<?php

namespace Kivii\Rest;

use Kivii\Services\BookingService;
use Kivii\Services\ValidationService;
use Kivii\Database\ServiceRepository;

/**
 * REST API controller – registers all routes.
 */
class RestController {

    private string $namespace = 'kiviiweb/v1';

    public function register_routes(): void {
        // GET /availability/days
        register_rest_route( $this->namespace, '/availability/days', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_available_days' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'month'    => [ 'required' => true, 'type' => 'integer', 'minimum' => 1, 'maximum' => 12 ],
                'year'     => [ 'required' => true, 'type' => 'integer', 'minimum' => 2024 ],
                'duration' => [ 'required' => false, 'type' => 'integer', 'default' => 60 ],
            ],
        ] );

        // GET /availability/slots
        register_rest_route( $this->namespace, '/availability/slots', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_available_slots' ],
            'permission_callback' => '__return_true',
            'args'                => [
                'date'     => [ 'required' => true, 'type' => 'string' ],
                'duration' => [ 'required' => false, 'type' => 'integer', 'default' => 60 ],
            ],
        ] );

        // POST /booking
        register_rest_route( $this->namespace, '/booking', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'create_booking' ],
            'permission_callback' => function ( \WP_REST_Request $request ) {
                return wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' );
            },
        ] );

        // GET /services
        register_rest_route( $this->namespace, '/services', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'get_services' ],
            'permission_callback' => '__return_true',
        ] );

        // POST /api-test (admin only)
        register_rest_route( $this->namespace, '/api-test', [
            'methods'             => 'POST',
            'callback'            => [ $this, 'test_api_connection' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ] );
    }

    /**
     * GET /availability/days
     */
    public function get_available_days( \WP_REST_Request $request ): \WP_REST_Response {
        $month    = (int) $request->get_param( 'month' );
        $year     = (int) $request->get_param( 'year' );
        $duration = (int) $request->get_param( 'duration' );

        $service = new BookingService();
        $days    = $service->get_available_days( $month, $year, $duration );

        return new \WP_REST_Response( [
            'success' => true,
            'data'    => [
                'month' => $month,
                'year'  => $year,
                'days'  => $days,
            ],
        ], 200 );
    }

    /**
     * GET /availability/slots
     */
    public function get_available_slots( \WP_REST_Request $request ): \WP_REST_Response {
        $date     = sanitize_text_field( $request->get_param( 'date' ) );
        $duration = (int) $request->get_param( 'duration' );

        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
            return new \WP_REST_Response( [
                'success' => false,
                'message' => 'Invalid date format.',
            ], 400 );
        }

        $service = new BookingService();
        $slots   = $service->get_available_slots( $date, $duration );

        return new \WP_REST_Response( [
            'success' => true,
            'data'    => [
                'date'  => $date,
                'slots' => $slots,
            ],
        ], 200 );
    }

    /**
     * POST /booking
     */
    public function create_booking( \WP_REST_Request $request ): \WP_REST_Response {
        $data = $request->get_json_params();

        // Validate
        $lang      = $data['language'] ?? 'nl';
        $validator = new ValidationService( $lang );

        if ( ! $validator->validate_all( $data ) ) {
            return new \WP_REST_Response( [
                'success' => false,
                'errors'  => $validator->get_errors(),
            ], 422 );
        }

        // Create booking
        $service = new BookingService();
        $result  = $service->create_booking( $data );

        if ( $result['success'] ) {
            return new \WP_REST_Response( [
                'success'   => true,
                'reference' => $result['reference'],
                'message'   => $result['message'],
            ], 201 );
        }

        return new \WP_REST_Response( [
            'success' => false,
            'message' => $result['message'] ?? 'Unknown error.',
        ], 500 );
    }

    /**
     * GET /services
     */
    public function get_services( \WP_REST_Request $request ): \WP_REST_Response {
        $repo = new ServiceRepository();
        $data = $repo->get_all_active_grouped();

        return new \WP_REST_Response( [
            'success' => true,
            'data'    => $data,
        ], 200 );
    }

    /**
     * POST /api-test
     */
    public function test_api_connection( \WP_REST_Request $request ): \WP_REST_Response {
        $client = new \Kivii\Api\KiviiApiClient();
        $result = $client->test_connection();

        return new \WP_REST_Response( $result, $result['success'] ? 200 : 502 );
    }
}
