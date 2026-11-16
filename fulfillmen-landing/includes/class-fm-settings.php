<?php
/**
 * Admin settings screen: Settings → Fulfillmen Landing.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FM_LP_Settings {

	const OPTION = 'fm_lp_options';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( FM_LP_FILE ), array( __CLASS__, 'action_link' ) );
	}

	public static function action_link( $links ) {
		array_unshift( $links, '<a href="' . esc_url( admin_url( 'options-general.php?page=fm-landing' ) ) . '">Settings</a>' );
		return $links;
	}

	public static function menu() {
		add_options_page( 'Fulfillmen Landing', 'Fulfillmen Landing', 'manage_options', 'fm-landing', array( __CLASS__, 'render' ) );
	}

	/** Field map: key => [label, type, help]. */
	private static function fields() {
		return array(
			'Pages & routing'   => array(
				'landing_page_id'  => array( 'Landing page', 'page', 'Page containing [fulfillmen_landing]. Use the "Fulfillmen Canvas" page template for a distraction-free layout.' ),
				'thankyou_page_id' => array( 'Thank-you page', 'page', 'Form redirects here. Conversion tags fire on this page only.' ),
				'redirect_from'    => array( '301 redirect from', 'text', 'Duplicate signup URL to retire, e.g. /signup1/' ),
				'redirect_to'      => array( '301 redirect to', 'text', 'Surviving URL, e.g. /sign-up/ (or the landing page path).' ),
			),
			'Lead delivery'     => array(
				'ghl_webhook'  => array( 'GoHighLevel inbound webhook URL', 'url', 'Automation → Workflows → trigger "Inbound Webhook". Payload includes lead_type, tags[] and UTM fields.' ),
				'notify_email' => array( 'Notification email', 'email', 'Backup copy of every lead. Leads are also stored under Quote Leads in wp-admin.' ),
			),
			'Conversion tracking (thank-you page only)' => array(
				'meta_pixel_id' => array( 'Meta Pixel ID', 'text', 'Fires fbq("track","Lead"). If the base pixel is already on the site it is reused.' ),
				'gads_id'       => array( 'Google Ads ID', 'text', 'Format AW-123456789' ),
				'gads_label'    => array( 'Google Ads conversion label', 'text', 'The label after the slash in send_to.' ),
				'calendar_url'  => array( 'Booking calendar URL', 'url', 'GHL / Calendly booking link, embedded on the thank-you page.' ),
			),
			'Brand & proof'     => array(
				'logo_url'        => array( 'Logo URL', 'url', '' ),
				'address'         => array( 'Physical address (footer)', 'text', '' ),
				'whatsapp'        => array( 'WhatsApp number', 'text', 'International format, digits only, e.g. 8613800000000. Optional.' ),
				'privacy_url'     => array( 'Privacy policy URL', 'text', '' ),
				'delivery_proof'  => array( 'Delivery-time proof line', 'text', '' ),
				'stats'           => array( 'Key numbers', 'textarea', 'One per line: value | suffix | label   e.g. 20 | + | Years fulfilling globally' ),
				'testimonials'    => array( 'Testimonials (3–5, real clients only)', 'textarea', 'One per line: Name | Store type | Result | Quote. Hidden from visitors until filled.' ),
				'warehouse_media' => array( 'Warehouse photos / video', 'textarea', 'One URL per line (.jpg/.webp/.png or .mp4). Videos lazy-load. Hidden from visitors until filled.' ),
				'load_fonts'      => array( 'Load Poppins + Archivo from Google Fonts', 'checkbox', 'Untick if your theme already loads them.' ),
			),
		);
	}

	public static function register() {
		register_setting(
			'fm_lp_group',
			self::OPTION,
			array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) )
		);
	}

	public static function sanitize( $in ) {
		$out = fm_lp_options();
		$in  = is_array( $in ) ? $in : array();
		foreach ( self::fields() as $group ) {
			foreach ( $group as $key => $f ) {
				$v = isset( $in[ $key ] ) ? wp_unslash( $in[ $key ] ) : '';
				switch ( $f[1] ) {
					case 'page':
						$out[ $key ] = absint( $v );
						break;
					case 'url':
						$out[ $key ] = esc_url_raw( trim( $v ) );
						break;
					case 'email':
						$out[ $key ] = sanitize_email( $v );
						break;
					case 'textarea':
						$out[ $key ] = sanitize_textarea_field( $v );
						break;
					case 'checkbox':
						$out[ $key ] = empty( $v ) ? 0 : 1;
						break;
					default:
						$out[ $key ] = sanitize_text_field( $v );
				}
			}
		}
		$out['whatsapp'] = preg_replace( '/\D+/', '', $out['whatsapp'] );
		$out['gads_id']  = strtoupper( $out['gads_id'] );
		return $out;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$o = fm_lp_options();
		?>
		<div class="wrap">
			<h1>Fulfillmen Landing</h1>
			<p>Shortcodes: <code>[fulfillmen_landing]</code> and <code>[fulfillmen_thankyou]</code>. Leads are listed under <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=fm_lead' ) ); ?>">Quote Leads</a>.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'fm_lp_group' ); ?>
				<?php foreach ( self::fields() as $title => $group ) : ?>
					<h2 class="title"><?php echo esc_html( $title ); ?></h2>
					<table class="form-table" role="presentation">
						<?php
						foreach ( $group as $key => $f ) :
							$name = self::OPTION . '[' . $key . ']';
							$id   = 'fm_' . $key;
							?>
							<tr>
								<th scope="row"><label for="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $f[0] ); ?></label></th>
								<td>
									<?php
									switch ( $f[1] ) {
										case 'page':
											wp_dropdown_pages(
												array(
													'name' => esc_attr( $name ),
													'id'   => esc_attr( $id ),
													'selected' => (int) $o[ $key ],
													'show_option_none' => '— Select —',
													'option_none_value' => 0,
													'post_status' => array( 'publish', 'draft', 'private' ),
												)
											);
											break;
										case 'textarea':
											printf( '<textarea class="large-text code" rows="5" id="%s" name="%s">%s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( $o[ $key ] ) );
											break;
										case 'checkbox':
											printf( '<input type="checkbox" id="%s" name="%s" value="1" %s />', esc_attr( $id ), esc_attr( $name ), checked( 1, (int) $o[ $key ], false ) );
											break;
										default:
											printf( '<input type="%s" class="regular-text" id="%s" name="%s" value="%s" />', 'email' === $f[1] ? 'email' : ( 'url' === $f[1] ? 'url' : 'text' ), esc_attr( $id ), esc_attr( $name ), esc_attr( $o[ $key ] ) );
									}
									if ( $f[2] ) {
										echo '<p class="description">' . esc_html( $f[2] ) . '</p>';
									}
									?>
								</td>
							</tr>
						<?php endforeach; ?>
					</table>
				<?php endforeach; ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
