<?php
/**
 * Plugin Name:       Fulfillmen Landing (CRO)
 * Description:       Conversion-focused, animated quote landing page + thank-you page for Fulfillmen. Shortcodes [fulfillmen_landing] and [fulfillmen_thankyou], GoHighLevel webhook, lead_type + UTM capture, Meta Pixel / Google Ads conversion on the thank-you page only.
 * Version:           5.0.1
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Author:            Ajcreationz
 * License:           GPL-2.0-or-later
 * Text Domain:       fulfillmen-landing
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'FM_LP_VERSION', '5.0.1' );
define( 'FM_LP_FILE', __FILE__ );
define( 'FM_LP_DIR', plugin_dir_path( __FILE__ ) );
define( 'FM_LP_URL', plugin_dir_url( __FILE__ ) );

require_once FM_LP_DIR . 'includes/helpers.php';
require_once FM_LP_DIR . 'includes/class-fm-settings.php';
require_once FM_LP_DIR . 'includes/class-fm-leads.php';
require_once FM_LP_DIR . 'includes/class-fm-frontend.php';

FM_LP_Settings::init();
FM_LP_Leads::init();
FM_LP_Frontend::init();

register_activation_hook( __FILE__, 'fm_lp_activate' );

/**
 * On activation: create the landing + thank-you pages as drafts (if missing)
 * and point the settings at them. Nothing goes live until an admin publishes.
 */
function fm_lp_activate() {
	$opts = fm_lp_options();

	if ( empty( $opts['landing_page_id'] ) || ! get_post( $opts['landing_page_id'] ) ) {
		$opts['landing_page_id'] = wp_insert_post(
			array(
				'post_title'   => 'Free Landed-Cost Quote',
				'post_name'    => 'china-fulfillment-quote',
				'post_status'  => 'draft',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:shortcode -->[fulfillmen_landing]<!-- /wp:shortcode -->',
				'meta_input'   => array( '_wp_page_template' => 'fm-landing-canvas' ),
			)
		);
	}

	if ( empty( $opts['thankyou_page_id'] ) || ! get_post( $opts['thankyou_page_id'] ) ) {
		$opts['thankyou_page_id'] = wp_insert_post(
			array(
				'post_title'   => 'Your Quote Is On Its Way',
				'post_name'    => 'quote-thank-you',
				'post_status'  => 'draft',
				'post_type'    => 'page',
				'post_content' => '<!-- wp:shortcode -->[fulfillmen_thankyou]<!-- /wp:shortcode -->',
				'meta_input'   => array( '_wp_page_template' => 'fm-landing-canvas' ),
			)
		);
	}

	update_option( FM_LP_Settings::OPTION, $opts );
	FM_LP_Leads::register_post_type();
	flush_rewrite_rules();
}
