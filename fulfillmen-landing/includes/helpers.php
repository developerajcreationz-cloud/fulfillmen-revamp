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
		'privacy_url'       => '/privacy-policies/',
		// Fulfillmen's own photography (already on fulfillmen.com). Replace in settings any time.
		'hero_image'        => 'https://fulfillmen.com/wp-content/uploads/2026/05/Fulfillmen-China-sourcing-and-fulfillment-team-e1779592427124.webp',
		'img_fba'           => 'https://fulfillmen.com/wp-content/uploads/2026/03/hero-image-fulfillmen-02-e1773486767922.webp',
		'img_dropshipping'  => 'https://fulfillmen.com/wp-content/uploads/2026/06/reviewing-supplier-incoterms-purchase.webp',
		'img_own_brand'     => 'https://fulfillmen.com/wp-content/uploads/2026/06/hero-image-fulfillmen-1-03-e1776.webp',
		'platform_logos'    => "https://fulfillmen.com/wp-content/uploads/2026/04/sys_amazon.png|Amazon\nhttps://fulfillmen.com/wp-content/uploads/2026/04/sys_shopify.png|Shopify\nhttps://fulfillmen.com/wp-content/uploads/2026/04/sys_ebay.png|eBay\nhttps://fulfillmen.com/wp-content/uploads/2026/04/sys_woo.png|WooCommerce\nhttps://fulfillmen.com/wp-content/uploads/2026/04/sys_etsy.png|Etsy\nhttps://fulfillmen.com/wp-content/uploads/2026/04/sys_lazada.png|Lazada",
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

/** Deterministic barcode (SVG rects) for labels/waybills. */
function fm_lp_barcode( $seed, $w = 220, $h = 44 ) {
	$x    = 0;
	$out  = '';
	$hash = md5( $seed ) . md5( strrev( $seed ) );
	for ( $k = 0; $k < strlen( $hash ) && $x < $w; $k++ ) {
		$n   = hexdec( $hash[ $k ] );
		$bw  = 1 + ( $n % 3 );
		$gap = 1 + ( ( $n >> 2 ) % 2 );
		$out .= '<rect x="' . $x . '" y="0" width="' . $bw . '" height="' . $h . '"/>';
		$x   += $bw + $gap;
	}
	return '<svg class="fm-barcode" viewBox="0 0 ' . $x . ' ' . $h . '" preserveAspectRatio="none" aria-hidden="true">' . $out . '</svg>';
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
		'upright' => '<path d="M7 17 17 7M8 7h9v9"/>',
		'bag'     => '<path d="M5 8h14l-1 12H6z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/>',
		'truck'   => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7"/><circle cx="7" cy="18" r="1.8"/><circle cx="17" cy="18" r="1.8"/>',
		'barcode' => '<path d="M4 6v12M7 6v12M10 6v12M14 6v12M16 6v12M20 6v12"/>',
		'grid'    => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
		'bolt'    => '<path d="M13 3 5 14h6l-1 7 8-11h-6z"/>',
		'link'    => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
		'globe'   => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18"/>',
	);
	$d = isset( $p[ $name ] ) ? $p[ $name ] : '';
	return '<svg class="fm-ico" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg>';
}
