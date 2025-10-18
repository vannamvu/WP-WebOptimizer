<?php
/**
 * Google PageSpeed Insights API Integration
 *
 * Integrates with Google PageSpeed Insights API v5 to test website performance
 *
 * @package WP_WebOptimizer
 * @since   1.0.0
 */

class WPWO_PageSpeed_API {

	/**
	 * PageSpeed API endpoint.
	 *
	 * @var string
	 */
	private $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

	/**
	 * API key (optional, can work without it but with rate limits).
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Constructor.
	 *
	 * @param string $api_key Optional API key.
	 */
	public function __construct( $api_key = '' ) {
		$this->api_key = $api_key;
	}

	/**
	 * Run PageSpeed test for a URL.
	 *
	 * @param string $url      URL to test.
	 * @param string $strategy Strategy: 'mobile' or 'desktop'.
	 * @return array|WP_Error Test results or error.
	 */
	public function run_test( $url, $strategy = 'mobile' ) {
		// Build API request URL
		$request_url = add_query_arg(
			array(
				'url'      => urlencode( $url ),
				'strategy' => $strategy,
				'category' => array( 'performance', 'accessibility', 'best-practices', 'seo' ),
			),
			$this->api_url
		);

		// Add API key if available
		if ( ! empty( $this->api_key ) ) {
			$request_url = add_query_arg( 'key', $this->api_key, $request_url );
		}

		// Make API request
		$response = wp_remote_get(
			$request_url,
			array(
				'timeout' => 60,
				'headers' => array(
					'Accept' => 'application/json',
				),
			)
		);

		// Check for errors
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( $response_code !== 200 ) {
			return new WP_Error(
				'api_error',
				sprintf( __( 'PageSpeed API returned error code: %d', 'wp-weboptimizer' ), $response_code )
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data ) ) {
			return new WP_Error( 'parse_error', __( 'Failed to parse API response', 'wp-weboptimizer' ) );
		}

		// Parse and return results
		return $this->parse_results( $data );
	}

	/**
	 * Parse PageSpeed API results.
	 *
	 * @param array $data Raw API response data.
	 * @return array Parsed results.
	 */
	private function parse_results( $data ) {
		$results = array(
			'scores'         => array(),
			'metrics'        => array(),
			'opportunities'  => array(),
			'diagnostics'    => array(),
			'screenshot'     => '',
			'final_url'      => isset( $data['lighthouseResult']['finalUrl'] ) ? $data['lighthouseResult']['finalUrl'] : '',
		);

		// Extract scores
		if ( isset( $data['lighthouseResult']['categories'] ) ) {
			$categories = $data['lighthouseResult']['categories'];

			$results['scores'] = array(
				'performance'     => isset( $categories['performance']['score'] ) ? round( $categories['performance']['score'] * 100 ) : 0,
				'accessibility'   => isset( $categories['accessibility']['score'] ) ? round( $categories['accessibility']['score'] * 100 ) : 0,
				'best-practices'  => isset( $categories['best-practices']['score'] ) ? round( $categories['best-practices']['score'] * 100 ) : 0,
				'seo'             => isset( $categories['seo']['score'] ) ? round( $categories['seo']['score'] * 100 ) : 0,
			);
		}

		// Extract metrics
		if ( isset( $data['lighthouseResult']['audits'] ) ) {
			$audits = $data['lighthouseResult']['audits'];

			$results['metrics'] = array(
				'fcp'         => isset( $audits['first-contentful-paint']['numericValue'] ) ? round( $audits['first-contentful-paint']['numericValue'] ) : 0,
				'lcp'         => isset( $audits['largest-contentful-paint']['numericValue'] ) ? round( $audits['largest-contentful-paint']['numericValue'] ) : 0,
				'cls'         => isset( $audits['cumulative-layout-shift']['numericValue'] ) ? round( $audits['cumulative-layout-shift']['numericValue'], 3 ) : 0,
				'tbt'         => isset( $audits['total-blocking-time']['numericValue'] ) ? round( $audits['total-blocking-time']['numericValue'] ) : 0,
				'speed_index' => isset( $audits['speed-index']['numericValue'] ) ? round( $audits['speed-index']['numericValue'] ) : 0,
			);

			// Extract opportunities (suggestions for improvement)
			foreach ( $audits as $audit_id => $audit ) {
				if ( ! empty( $audit['details']['type'] ) && $audit['details']['type'] === 'opportunity' ) {
					$results['opportunities'][] = array(
						'id'          => $audit_id,
						'title'       => isset( $audit['title'] ) ? $audit['title'] : '',
						'description' => isset( $audit['description'] ) ? $audit['description'] : '',
						'score'       => isset( $audit['score'] ) ? $audit['score'] : null,
						'savings'     => isset( $audit['details']['overallSavingsMs'] ) ? $audit['details']['overallSavingsMs'] : 0,
					);
				}
			}

			// Extract diagnostics
			foreach ( $audits as $audit_id => $audit ) {
				if ( ! empty( $audit['details']['type'] ) && $audit['details']['type'] === 'table' && isset( $audit['score'] ) && $audit['score'] < 1 ) {
					$results['diagnostics'][] = array(
						'id'          => $audit_id,
						'title'       => isset( $audit['title'] ) ? $audit['title'] : '',
						'description' => isset( $audit['description'] ) ? $audit['description'] : '',
						'score'       => $audit['score'],
					);
				}
			}

			// Get screenshot
			if ( isset( $audits['final-screenshot']['details']['data'] ) ) {
				$results['screenshot'] = $audits['final-screenshot']['details']['data'];
			}
		}

		return $results;
	}

	/**
	 * Save test results to database.
	 *
	 * @param array  $results  Test results.
	 * @param string $url      Tested URL.
	 * @param string $strategy Strategy used.
	 * @return bool Success status.
	 */
	public function save_test_results( $results, $url, $strategy ) {
		$history = $this->get_test_history();

		$test_entry = array(
			'timestamp' => current_time( 'timestamp' ),
			'date'      => current_time( 'mysql' ),
			'url'       => $url,
			'strategy'  => $strategy,
			'scores'    => $results['scores'],
			'metrics'   => $results['metrics'],
		);

		// Add to history
		array_unshift( $history, $test_entry );

		// Keep only last 20 tests
		if ( count( $history ) > 20 ) {
			$history = array_slice( $history, 0, 20 );
		}

		return update_option( 'wpwo_pagespeed_history', $history );
	}

	/**
	 * Get test history from database.
	 *
	 * @return array Test history.
	 */
	public function get_test_history() {
		$history = get_option( 'wpwo_pagespeed_history', array() );
		return is_array( $history ) ? $history : array();
	}

	/**
	 * Clear test history.
	 *
	 * @return bool Success status.
	 */
	public function clear_test_history() {
		return delete_option( 'wpwo_pagespeed_history' );
	}

	/**
	 * Get latest test results.
	 *
	 * @param string $strategy Optional strategy filter.
	 * @return array|null Latest test results or null.
	 */
	public function get_latest_test( $strategy = '' ) {
		$history = $this->get_test_history();

		if ( empty( $history ) ) {
			return null;
		}

		// If strategy specified, find latest test for that strategy
		if ( ! empty( $strategy ) ) {
			foreach ( $history as $test ) {
				if ( isset( $test['strategy'] ) && $test['strategy'] === $strategy ) {
					return $test;
				}
			}
			return null;
		}

		// Return most recent test
		return $history[0];
	}
}
