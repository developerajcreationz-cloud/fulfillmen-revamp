<?php
/**
 * Lead capture: REST endpoint (JS path) + admin-post fallback (no-JS path),
 * local storage as a private "fm_lead" post, GoHighLevel webhook, email copy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FM_LP_Leads {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_route' ) );
		add_action( 'admin_post_nopriv_fm_quote', array( __CLASS__, 'handle_post' ) );
		add_action( 'admin_post_fm_quote', array( __CLASS__, 'handle_post' ) );
		add_filter( 'manage_fm_lead_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_fm_lead_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( 'add_meta_boxes_fm_lead', array( __CLASS__, 'meta_box' ) );
	}

	public static function register_post_type() {
		register_post_type(
			'fm_lead',
			array(
				'labels'          => array(
					'name'          => 'Quote Leads',
					'singular_name' => 'Quote Lead',
				),
				'public'          => false,
				'show_ui'         => true,
				'show_in_menu'    => true,
				'menu_icon'       => 'dashicons-email-alt',
				'supports'        => array( 'title' ),
				'capability_type' => 'post',
				'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
				'map_meta_cap'    => true,
			)
		);
	}

	public static function register_route() {
		register_rest_route(
			'fulfillmen/v1',
			'/quote',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_rest' ),
				// Public lead form. Nonces are deliberately not used: full-page caches
				// (LiteSpeed on Hostinger) serve stale nonces and silently kill leads.
				// Abuse is limited by honeypot, time trap and per-IP rate limit below.
				'permission_callback' => '__return_true',
			)
		);
	}

	public static function handle_rest( WP_REST_Request $req ) {
		$result = self::process( $req->get_params() );
		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array(
					'ok'     => false,
					'errors' => $result->get_error_data() ? $result->get_error_data() : array( '_' => $result->get_error_message() ),
				),
				422
			);
		}
		return new WP_REST_Response( array( 'ok' => true, 'redirect' => $result ), 200 );
	}

	/** No-JS fallback: plain form POST to admin-post.php. */
	public static function handle_post() {
		$result = self::process( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( is_wp_error( $result ) ) {
			$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
			wp_safe_redirect( add_query_arg( 'fm_error', 1, $back ) . '#quote' );
			exit;
		}
		wp_safe_redirect( $result );
		exit;
	}

	/**
	 * Validate, store, forward. Returns the thank-you URL or WP_Error.
	 *
	 * @param array $p Raw params.
	 * @return string|WP_Error
	 */
	public static function process( $p ) {
		$get = function ( $k ) use ( $p ) {
			return isset( $p[ $k ] ) && is_scalar( $p[ $k ] ) ? (string) $p[ $k ] : '';
		};

		// Bot traps: honeypot must be empty; form must have been open ≥ 3 s.
		$ts = (int) $get( 'fm_ts' );
		if ( '' !== $get( 'company_website' ) || ( $ts && ( time() - $ts ) < 3 ) ) {
			// Pretend success so bots learn nothing.
			return fm_lp_thankyou_url();
		}

		// Rate limit: 5 submissions / 10 min per IP.
		$ip_key = 'fm_lp_rl_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'x' );
		$hits   = (int) get_transient( $ip_key );
		if ( $hits >= 5 ) {
			return new WP_Error( 'rate', 'Too many requests. Please try again in a few minutes.', array( '_' => 'Too many requests. Please try again in a few minutes.' ) );
		}
		set_transient( $ip_key, $hits + 1, 10 * MINUTE_IN_SECONDS );

		$lead = array(
			'name'           => sanitize_text_field( $get( 'name' ) ),
			'email'          => sanitize_email( $get( 'email' ) ),
			'phone'          => sanitize_text_field( $get( 'phone' ) ),
			'links'          => sanitize_textarea_field( $get( 'links' ) ),
			'volume'         => sanitize_key( $get( 'volume' ) ),
			'lead_type'      => sanitize_key( $get( 'lead_type' ) ),
			'landing_url'    => esc_url_raw( $get( 'landing_url' ) ),
			'referrer'       => esc_url_raw( $get( 'referrer' ) ),
		);
		foreach ( fm_lp_tracking_keys() as $k ) {
			$lead[ $k ] = sanitize_text_field( substr( $get( $k ), 0, 200 ) );
		}

		$errors = array();
		if ( strlen( $lead['name'] ) < 2 ) {
			$errors['name'] = 'Please enter your name.';
		}
		if ( ! is_email( $lead['email'] ) ) {
			$errors['email'] = 'Please enter a valid email address.';
		}
		if ( strlen( preg_replace( '/\D+/', '', $lead['phone'] ) ) < 6 ) {
			$errors['phone'] = 'Please enter a WhatsApp or phone number with country code.';
		}
		if ( strlen( $lead['links'] ) < 3 ) {
			$errors['links'] = 'Add your store link or a product link.';
		}
		if ( ! isset( fm_lp_volumes()[ $lead['volume'] ] ) ) {
			$errors['volume'] = 'Choose your monthly order volume.';
		}
		if ( $errors ) {
			return new WP_Error( 'invalid', 'Please check the highlighted fields.', $errors );
		}
		if ( ! isset( fm_lp_lead_types()[ $lead['lead_type'] ] ) ) {
			$lead['lead_type'] = 'unknown';
		}

		// 1) Store locally first so a webhook outage never loses a lead.
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'fm_lead',
				'post_status' => 'private',
				'post_title'  => sprintf( '%s — %s', $lead['name'], $lead['email'] ),
			)
		);
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			foreach ( $lead as $k => $v ) {
				update_post_meta( $post_id, '_fm_' . $k, $v );
			}
		}

		// 2) GoHighLevel inbound webhook.
		$hook = fm_lp_opt( 'ghl_webhook' );
		if ( $hook ) {
			$parts   = preg_split( '/\s+/', $lead['name'], 2 );
			$payload = array_merge(
				$lead,
				array(
					'first_name'          => $parts[0],
					'last_name'           => isset( $parts[1] ) ? $parts[1] : '',
					'full_name'           => $lead['name'],
					'lead_type_label'     => isset( fm_lp_lead_types()[ $lead['lead_type'] ] ) ? fm_lp_lead_types()[ $lead['lead_type'] ] : 'Not selected',
					'monthly_volume'      => fm_lp_volumes()[ $lead['volume'] ],
					'store_or_product_links' => $lead['links'],
					'tags'                => array( 'landing-quote', 'lead_type:' . $lead['lead_type'], $lead['lead_type'] ),
					'source'              => 'Fulfillmen landing page',
					'submitted_at'        => gmdate( 'c' ),
					'wp_lead_id'          => $post_id,
				)
			);
			$res = wp_remote_post(
				$hook,
				array(
					'timeout' => 8,
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => wp_json_encode( $payload ),
				)
			);
			$status = is_wp_error( $res ) ? 'error: ' . $res->get_error_message() : (string) wp_remote_retrieve_response_code( $res );
			if ( $post_id ) {
				update_post_meta( $post_id, '_fm_ghl_status', $status );
			}
		}

		// 3) Email copy.
		$to = fm_lp_opt( 'notify_email' );
		if ( $to ) {
			$body = '';
			foreach ( $lead as $k => $v ) {
				if ( '' !== $v ) {
					$body .= str_pad( $k, 14 ) . ': ' . $v . "\n";
				}
			}
			wp_mail(
				$to,
				sprintf( '[Quote] %s (%s, %s)', $lead['name'], $lead['lead_type'], fm_lp_volumes()[ $lead['volume'] ] ),
				$body,
				array( 'Reply-To: ' . $lead['name'] . ' <' . $lead['email'] . '>' )
			);
		}

		do_action( 'fm_lp_lead_created', $post_id, $lead );

		// Only non-PII values in the URL. `ref` lets the thank-you page fire tags once per lead.
		return add_query_arg(
			array(
				'lt'  => $lead['lead_type'],
				'ref' => substr( wp_hash( (string) $post_id ), 0, 10 ),
			),
			fm_lp_thankyou_url()
		);
	}

	/* ---------- wp-admin list ---------- */

	public static function columns( $cols ) {
		return array(
			'cb'        => $cols['cb'],
			'title'     => 'Lead',
			'lead_type' => 'Path',
			'volume'    => 'Volume',
			'utm'       => 'Source / campaign',
			'ghl'       => 'GHL',
			'date'      => 'Date',
		);
	}

	public static function column( $col, $id ) {
		switch ( $col ) {
			case 'lead_type':
				echo esc_html( get_post_meta( $id, '_fm_lead_type', true ) );
				break;
			case 'volume':
				$v = get_post_meta( $id, '_fm_volume', true );
				echo esc_html( isset( fm_lp_volumes()[ $v ] ) ? fm_lp_volumes()[ $v ] : $v );
				break;
			case 'utm':
				echo esc_html( trim( get_post_meta( $id, '_fm_utm_source', true ) . ' / ' . get_post_meta( $id, '_fm_utm_campaign', true ), ' /' ) );
				break;
			case 'ghl':
				echo esc_html( get_post_meta( $id, '_fm_ghl_status', true ) );
				break;
		}
	}

	public static function meta_box() {
		add_meta_box(
			'fm_lead_details',
			'Lead details',
			function ( $post ) {
				echo '<table class="widefat striped"><tbody>';
				foreach ( get_post_meta( $post->ID ) as $k => $v ) {
					if ( 0 !== strpos( $k, '_fm_' ) ) {
						continue;
					}
					echo '<tr><th style="width:180px">' . esc_html( substr( $k, 4 ) ) . '</th><td>' . nl2br( esc_html( $v[0] ) ) . '</td></tr>';
				}
				echo '</tbody></table>';
			},
			'fm_lead',
			'normal',
			'high'
		);
	}
}
