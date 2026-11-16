<?php
/**
 * [fulfillmen_thankyou] — confirmation, booking calendar, and the ONLY place
 * the Meta Pixel Lead event and Google Ads conversion fire (brief §6).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$o     = fm_lp_options();
$types = fm_lp_lead_types();
// phpcs:disable WordPress.Security.NonceVerification
$lt  = isset( $_GET['lt'] ) ? sanitize_key( wp_unslash( $_GET['lt'] ) ) : '';
$ref = isset( $_GET['ref'] ) ? sanitize_key( wp_unslash( $_GET['ref'] ) ) : '';
// phpcs:enable
$cal = $o['calendar_url'];
?>
<div class="fm-lp fm-ty" data-fm-lp>
	<section class="fm-hero fm-ty__hero">
		<div class="fm-hero__glow" aria-hidden="true"></div>
		<div class="fm-wrap fm-ty__in">
			<div class="fm-ty__badge fm-rise" style="--d:0" aria-hidden="true">
				<svg viewBox="0 0 80 80"><circle class="fm-ty__ring" cx="40" cy="40" r="34"/><path class="fm-ty__check" d="M25 41 L36 52 L56 30"/></svg>
			</div>
			<h1 class="fm-h1 fm-rise" style="--d:1"><span class="fm-ink">Your quote is on its way.</span> <span class="fm-mute">Expect it within 24 hours.</span></h1>
			<p class="fm-hero__sub fm-rise" style="--d:2">
				<?php if ( isset( $types[ $lt ] ) ) : ?>
					We're pricing everything for you as an <b><?php echo esc_html( strtolower( $types[ $lt ] ) ); ?></b>: product cost, QC, prep, warehousing and shipping, line by line.
				<?php else : ?>
					We're pricing everything line by line: product cost, QC, prep, warehousing and shipping.
				<?php endif; ?>
			</p>
			<ol class="fm-ty__next fm-rise" style="--d:3">
				<li><span>1</span><div><b>Check your inbox</b><small>A confirmation is on its way now.</small></div></li>
				<li><span>2</span><div><b>We build your quote</b><small>Our China team checks your products and routes.</small></div></li>
				<li><span>3</span><div><b>You get full pricing</b><small>By email and WhatsApp within 24 hours.</small></div></li>
			</ol>
		</div>
	</section>

	<section class="fm-section fm-ty__cal" aria-labelledby="fm-cal-title">
		<div class="fm-wrap">
			<div class="fm-head fm-reveal">
				<h2 id="fm-cal-title" class="fm-h2">Want to talk it through? Book a 15-minute call.</h2>
				<p>Pick a time that suits you and speak with the team who'll handle your orders.</p>
			</div>
			<?php if ( $cal ) : ?>
				<div class="fm-cal fm-reveal">
					<iframe src="<?php echo esc_url( $cal ); ?>" title="Book a 15-minute call with Fulfillmen" loading="lazy" allow="payment"></iframe>
				</div>
			<?php elseif ( fm_lp_show_placeholders() ) : ?>
				<div class="fm-cal fm-ph"><span>Booking calendar<br><small>Add the calendar URL in Settings → Fulfillmen Landing (visible to admins only)</small></span></div>
			<?php endif; ?>
		</div>
	</section>

	<footer class="fm-foot">
		<div class="fm-wrap fm-foot__in">
			<p><?php echo fm_lp_icon( 'pin' ); // phpcs:ignore ?> Fulfillmen · <?php echo esc_html( $o['address'] ); ?></p>
			<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Fulfillmen</p>
		</div>
	</footer>
</div>
<?php
$pixel = preg_replace( '/\D+/', '', $o['meta_pixel_id'] );
$aw    = preg_replace( '/[^A-Z0-9\-]/', '', $o['gads_id'] );
$label = preg_replace( '/[^A-Za-z0-9_\-]/', '', $o['gads_label'] );
$cfg   = array(
	'pixel'    => $pixel,
	'aw'       => $aw,
	'sendTo'   => ( $aw && $label ) ? $aw . '/' . $label : '',
	'leadType' => isset( $types[ $lt ] ) ? $lt : 'unknown',
	'ref'      => $ref,
);
?>
<script>
(function (c) {
	// Fire once per lead (refreshing the thank-you page must not double count).
	var key = 'fm_conv_' + (c.ref || 'noref');
	try { if (c.ref && sessionStorage.getItem(key)) { return; } } catch (e) {}

	window.dataLayer = window.dataLayer || [];
	window.dataLayer.push({ event: 'fm_lead', lead_type: c.leadType });

	if (c.pixel) {
		if (typeof window.fbq !== 'function') {
			!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
			fbq('init', c.pixel);
			fbq('track', 'PageView');
		}
		fbq('track', 'Lead', { content_category: c.leadType });
	}

	if (c.sendTo) {
		if (typeof window.gtag !== 'function') {
			var s = document.createElement('script');
			s.async = true;
			s.src = 'https://www.googletagmanager.com/gtag/js?id=' + c.aw;
			document.head.appendChild(s);
			window.gtag = function () { window.dataLayer.push(arguments); };
			gtag('js', new Date());
			gtag('config', c.aw);
		}
		gtag('event', 'conversion', { send_to: c.sendTo, transaction_id: c.ref || undefined });
	}

	try { if (c.ref) { sessionStorage.setItem(key, '1'); } } catch (e) {}
})(<?php echo wp_json_encode( $cfg ); ?>);
</script>
