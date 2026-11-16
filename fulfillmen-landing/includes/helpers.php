<?php
/**
 * Shared helpers: defaults, option access, fixed vocabularies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings. Everything client-specific is editable in
 * Settings → Fulfillmen Landing; these are safe starting values only.
 */
function fm_lp_defaults() {
	return array(
		'landing_page_id'   => 0,
		'thankyou_page_id'  => 0,
		'ghl_webhook'       => '',
		'notify_email'      => get_option( 'admin_email' ),
		'meta_pixel_id'     => '',
		'gads_id'           => '',
		'gads_label'        => '',
		'calendar_url'      => '',
		'whatsapp'          => '',
		'logo_url'          => 'https://fulfillmen.com/wp-content/uploads/2025/04/logo-png.png',
		'address'           => 'Huizhou, Guangdong, China',
		'privacy_url'       => '/privacy-policy/',
		// value|suffix|label  (numbers taken from the current fulfillmen.com homepage).
		'stats'             => "2.5|×|Faster dispatch than industry average\n20|%|Lower shipping costs vs typical carrier rates\n20|+|Years fulfilling globally",
		// Name | Store type | Result | Quote   — leave empty until the client supplies real ones.
		'testimonials'      => '',
		// One image or .mp4 URL per line (real warehouse footage from the client).
		'warehouse_media'   => '',
		'delivery_proof'    => 'B2C special lines: 7–15 days door-to-door, fully tracked',
		'redirect_from'     => '/signup1/',
		'redirect_to'       => '/sign-up/',
		'load_fonts'        => 1,
	);
}

function fm_lp_options() {
	$saved = get_option( 'fm_lp_options', array() );
	return wp_parse_args( is_array( $saved ) ? $saved : array(), fm_lp_defaults() );
}

function fm_lp_opt( $key ) {
	$o = fm_lp_options();
	return isset( $o[ $key ] ) ? $o[ $key ] : '';
}

/** lead_type values from the brief → human labels. */
function fm_lp_lead_types() {
	return array(
		'fba'          => 'Amazon FBA seller',
		'dropshipping' => 'Dropshipper',
		'own_brand'    => 'Brand with own stock',
	);
}

/** Monthly order volume dropdown from the brief. */
function fm_lp_volumes() {
	return array(
		'under_100'  => 'Under 100 orders / month',
		'100_500'    => '100–500 orders / month',
		'500_2000'   => '500–2,000 orders / month',
		'2000_plus'  => '2,000+ orders / month',
	);
}

/** Attribution params captured into hidden fields. */
function fm_lp_tracking_keys() {
	return array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'fbclid' );
}

/** Split a "a|b|c" per-line textarea into rows of trimmed cells. */
function fm_lp_rows( $text, $min_cols = 1 ) {
	$rows = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$cells = array_map( 'trim', explode( '|', $line ) );
		if ( count( $cells ) >= $min_cols ) {
			$rows[] = $cells;
		}
	}
	return $rows;
}

/** Placeholders for missing proof assets are shown to admins (or ?fm_preview=1) only. */
function fm_lp_show_placeholders() {
	return current_user_can( 'edit_pages' ) || isset( $_GET['fm_preview'] ); // phpcs:ignore WordPress.Security.NonceVerification
}

function fm_lp_thankyou_url() {
	$id = (int) fm_lp_opt( 'thankyou_page_id' );
	$url = $id ? get_permalink( $id ) : '';
	return $url ? $url : home_url( '/quote-thank-you/' );
}

/** Inline SVG icon set (stroke icons, currentColor). */
function fm_lp_icon( $name ) {
	$p = array(
		'amazon'  => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/><path d="M7 15.5c3 1.6 7 1.6 10 0"/>',
		'drop'    => '<path d="M4 17h3l2-9h8l2 5"/><circle cx="8" cy="19" r="1.6"/><circle cx="17" cy="19" r="1.6"/><path d="M14 4h6v6"/><path d="m20 4-7 7"/>',
		'brand'   => '<path d="M3 21V9l9-5 9 5v12"/><path d="M7 21v-7h10v7"/><path d="M7 17h10"/>',
		'search'  => '<circle cx="11" cy="11" r="6"/><path d="m20 20-4.2-4.2"/>',
		'camera'  => '<path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/>',
		'box'     => '<path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/>',
		'plane'   => '<path d="M2 16l20-8-3 12-6-3-3 5v-6l9-8"/>',
		'check'   => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
		'arrow'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'shield'  => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
		'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'pin'     => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'chat'    => '<path d="M4 5h16v11H9l-5 4z"/>',
		'star'    => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
		'x'       => '<path d="M6 6l12 12M18 6 6 18"/>',
	);
	$d = isset( $p[ $name ] ) ? $p[ $name ] : '';
	return '<svg class="fm-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg>';
}
