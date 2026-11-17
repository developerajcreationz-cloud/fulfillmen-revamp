<?php
/**
 * Floating dark-glass pill header (Raze-style), in Fulfillmen teal.
 * Expects $fm_is_land (bool): landing page shows section links.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$fm_logo = fm_lp_opt( 'logo_url' );
$fm_is_land = ! empty( $fm_is_land );
?>
<a class="fm-skip" href="#fm-main">Skip to content</a>
<span id="top"></span>
<header class="fm-header" data-fm-header>
	<div class="fm-nav">
		<a class="fm-nav__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Fulfillmen home">
			<?php if ( $fm_logo ) : ?>
				<img src="<?php echo esc_url( $fm_logo ); ?>" alt="Fulfillmen" width="128" height="37" decoding="async" fetchpriority="high">
			<?php else : ?>
				<span>Fulfillmen</span>
			<?php endif; ?>
		</a>
		<?php if ( $fm_is_land ) : ?>
			<nav class="fm-nav__links" aria-label="Page sections">
				<a href="#path">Your path</a><i aria-hidden="true">·</i>
				<a href="#how">Process</a><i aria-hidden="true">·</i>
				<a href="#proof">Results</a>
				<a class="fm-nav__mark" href="#top" aria-label="Back to top">f</a>
				<a href="#inspection">Free QC</a><i aria-hidden="true">·</i>
				<a href="#faq">FAQ</a><i aria-hidden="true">·</i>
				<a href="#quote">Quote</a>
			</nav>
			<a class="fm-nav__cta" href="#quote" data-fm-cta="header">Get my free quote</a>
		<?php else : ?>
			<a class="fm-nav__cta" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to fulfillmen.com</a>
		<?php endif; ?>
	</div>
</header>
