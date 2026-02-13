<?php

namespace Kivii\Api;

use Kivii\Database\LogRepository;

/**
 * HTTP client for communicating with the Kivii API.
 */
class KiviiApiClient {

    private string $base_url;
    private string $token;
    private int $timeout;
    private int $retries;
    private LogRepository $logger;

    public function __construct() {
        $options        = get_option( 'kivii_api', [] );
        $this->base_url = rtrim( $options['base_url'] ?? '', '/' );
        $this->token    = $options['token'] ?? '';
        $this->timeout  = (int) ( $options['timeout'] ?? 30 );
        $this->retries  = (int) ( $options['retries'] ?? 2 );
        $this->logger   = new LogRepository();
    }

    /**
     * Make a GET request.
     */
    public function get( string $endpoint, array $params = [] ): array {
        $url = $this->base_url . '/' . ltrim( $endpoint, '/' );

        if ( ! empty( $params ) ) {
            $url .= '?' . http_build_query( $params );
        }

        return $this->request( 'GET', $url );
    }

    /**
     * Make a POST request.
     */
    public function post( string $endpoint, array $data = [] ): array {
        $url = $this->base_url . '/' . ltrim( $endpoint, '/' );
        return $this->request( 'POST', $url, $data );
    }

    /**
     * Execute HTTP request with retry logic.
     */
    private function request( string $method, string $url, array $body = [] ): array {
        $attempt = 0;

        while ( $attempt <= $this->retries ) {
            $attempt++;

            $args = [
                'method'  => $method,
                'timeout' => $this->timeout,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json',
                ],
            ];

            if ( $method === 'POST' && ! empty( $body ) ) {
                $args['body'] = wp_json_encode( $body );
            }

            $response = wp_remote_request( $url, $args );

            if ( is_wp_error( $response ) ) {
                $this->logger->warning( "Kivii API request failed (attempt {$attempt})", [
                    'url'   => $url,
                    'error' => $response->get_error_message(),
                ] );

                if ( $attempt > $this->retries ) {
                    return [
                        'success' => false,
                        'error'   => $response->get_error_message(),
                        'data'    => null,
                    ];
                }

                // Exponential backoff
                usleep( $attempt * 500000 );
                continue;
            }

            $status_code = wp_remote_retrieve_response_code( $response );
            $body_raw    = wp_remote_retrieve_body( $response );
            $data        = json_decode( $body_raw, true );

            if ( $status_code >= 200 && $status_code < 300 ) {
                return [
                    'success' => true,
                    'data'    => $data,
                    'status'  => $status_code,
                ];
            }

            $this->logger->warning( "Kivii API returned {$status_code} (attempt {$attempt})", [
                'url'      => $url,
                'status'   => $status_code,
                'response' => $body_raw,
            ] );

            if ( $attempt > $this->retries ) {
                return [
                    'success' => false,
                    'error'   => "API returned status {$status_code}",
                    'data'    => $data,
                    'status'  => $status_code,
                ];
            }

            usleep( $attempt * 500000 );
        }

        return [ 'success' => false, 'error' => 'Max retries exceeded', 'data' => null ];
    }

    /**
     * Test the API connection.
     */
    public function test_connection(): array {
        if ( empty( $this->base_url ) || empty( $this->token ) ) {
            return [
                'success' => false,
                'message' => 'API URL of token is niet geconfigureerd.',
            ];
        }

        $result = $this->get( 'ping' );

        if ( $result['success'] ) {
            return [
                'success' => true,
                'message' => 'Verbinding succesvol!',
            ];
        }

        return [
            'success' => false,
            'message' => 'Verbinding mislukt: ' . ( $result['error'] ?? 'onbekende fout' ),
        ];
    }
}
