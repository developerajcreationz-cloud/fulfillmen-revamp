<?php
/**
 * Front end: shortcodes, "Fulfillmen Canvas" page template, assets,
 * 301 redirect for the duplicate signup page, conversion tags.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FM_LP_Frontend {

	const TEMPLATE = 'fm-landing-canvas';

	public static function init() {
		add_shortcode( 'fulfillmen_landing', array( __CLASS__, 'landing' ) );
		add_shortcode( 'fulfillmen_thankyou', array( __CLASS__, 'thankyou' ) );
		add_filter( 'theme_page_templates', array( __CLASS__, 'add_template' ) );
		add_filter( 'template_include', array( __CLASS__, 'load_template' ), 99 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'template_redirect', array( __CLASS__, 'redirects' ), 1 );
		add_filter( 'script_loader_tag', array( __CLASS__, 'defer' ), 10, 2 );
		add_filter( 'wp_resource_hints', array( __CLASS__, 'hints' ), 10, 2 );
	}

	/* ---------- Template ---------- */

	public static function add_template( $templates ) {
		$templates[ self::TEMPLATE ] = 'Fulfillmen Canvas (no theme header/footer)';
		return $templates;
	}

	public static function load_template( $template ) {
		if ( is_singular() && self::TEMPLATE === get_page_template_slug() ) {
			return FM_LP_DIR . 'templates/canvas.php';
		}
		return $template;
	}

	/* ---------- Redirect /signup1/ → /sign-up/ (brief §8) ---------- */

	public static function redirects() {
		$from = trim( (string) fm_lp_opt( 'redirect_from' ), '/' );
		$to   = (string) fm_lp_opt( 'redirect_to' );
		if ( ! $from || ! $to || ! isset( $_SERVER['REQUEST_URI'] ) ) {
			return;
		}
		$path = trim( (string) wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ), '/' );
		$home = trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' );
		if ( $home && 0 === strpos( $path, $home ) ) {
			$path = trim( substr( $path, strlen( $home ) ), '/' );
		}
		if ( $path === $from && trim( wp_parse_url( $to, PHP_URL_PATH ), '/' ) !== $from ) {
			$qs = isset( $_SERVER['QUERY_STRING'] ) ? sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ) : '';
			$target = ( 0 === strpos( $to, 'http' ) ) ? $to : home_url( $to );
			wp_safe_redirect( $qs ? $target . ( strpos( $target, '?' ) ? '&' : '?' ) . $qs : $target, 301 ); // keep UTMs
			exit;
		}
	}

	/* ---------- Assets ---------- */

	private static function page_needs_assets() {
		if ( ! is_singular() ) {
			return false;
		}
		$post = get_post();
		return $post && ( has_shortcode( $post->post_content, 'fulfillmen_landing' ) || has_shortcode( $post->post_content, 'fulfillmen_thankyou' ) || self::TEMPLATE === get_page_template_slug( $post ) );
	}

	public static function assets() {
		if ( ! self::page_needs_assets() ) {
			return;
		}
		self::enqueue();
	}

	public static function enqueue() {
		if ( wp_style_is( 'fm-landing', 'enqueued' ) ) {
			return;
		}
		if ( fm_lp_opt( 'load_fonts' ) ) {
			wp_enqueue_style( 'fm-fonts', 'https://fonts.googleapis.com/css2?family=Geist:wght@300..700&family=Geist+Mono:wght@400;500&display=swap', array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
		wp_enqueue_style( 'fm-landing', FM_LP_URL . 'assets/css/landing.css', array(), FM_LP_VERSION );
		wp_register_script( 'fm-lenis', FM_LP_URL . 'assets/vendor/lenis.min.js', array(), '1.1.20', true );
		wp_register_script( 'fm-gsap', FM_LP_URL . 'assets/vendor/gsap.min.js', array(), '3.13.0', true );
		wp_register_script( 'fm-gsap-st', FM_LP_URL . 'assets/vendor/ScrollTrigger.min.js', array( 'fm-gsap' ), '3.13.0', true );
		wp_enqueue_script( 'fm-landing', FM_LP_URL . 'assets/js/landing.js', array( 'fm-lenis', 'fm-gsap', 'fm-gsap-st' ), FM_LP_VERSION, true );
		wp_localize_script(
			'fm-landing',
			'FM_LP',
			array(
				'endpoint' => esc_url_raw( rest_url( 'fulfillmen/v1/quote' ) ),
				'types'    => fm_lp_lead_types(),
			)
		);
	}

	public static function defer( $tag, $handle ) {
		if ( in_array( $handle, array( 'fm-lenis', 'fm-gsap', 'fm-gsap-st', 'fm-landing' ), true ) && false === strpos( $tag, ' defer' ) ) {
			$tag = str_replace( ' src=', ' defer src=', $tag );
		}
		return $tag;
	}

	public static function hints( $urls, $type ) {
		if ( 'preconnect' === $type && fm_lp_opt( 'load_fonts' ) && self::page_needs_assets() ) {
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		}
		return $urls;
	}

	/* ---------- Shortcodes ---------- */

	public static function landing() {
		self::enqueue();
		ob_start();
		include FM_LP_DIR . 'templates/landing.php';
		return ob_get_clean();
	}

	public static function thankyou() {
		self::enqueue();
		ob_start();
		include FM_LP_DIR . 'templates/thank-you.php';
		return ob_get_clean();
	}
}
