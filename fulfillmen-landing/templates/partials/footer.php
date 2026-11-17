<?php
/**
 * Dark footer with brand block, link columns and a glow (Raze-style), in Fulfillmen teal.
 * Links point at fulfillmen.com's existing service pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$fm_logo    = fm_lp_opt( 'logo_url' );
$fm_is_land = ! empty( $fm_is_land );
$fm_quote   = $fm_is_land ? '#quote' : home_url( '/' );
$fm_wa      = fm_lp_opt( 'whatsapp' );
$fm_cols    = array(
	'This page' => $fm_is_land ? array(
		'#path'       => 'Pick your path',
		'#how'        => 'How it works',
		'#inspection' => 'Free photo inspection',
		'#proof'      => 'Results',
		'#faq'        => 'FAQ',
		'#quote'      => 'Get a quote',
	) : array( home_url( '/' ) => 'Home' ),
	'Services'  => array(
		home_url( '/sourcing-purchase/' )     => 'Sourcing & purchase',
		home_url( '/fulfillment-services/' )  => 'Fulfillment services',
		home_url( '/warehouse-services/' )    => 'Warehouse services',
		home_url( '/fba-prep-service/' )      => 'Amazon FBA prep',
		home_url( '/dropshipping-services/' ) => 'Dropshipping',
		home_url( '/logistics-services/' )    => 'Logistics & shipping',
	),
	'Company'   => array(
		home_url( '/about-us/' )      => 'About us',
		home_url( '/why-fulfillmen/' ) => 'Why Fulfillmen',
		home_url( '/our-clients/' )   => 'Our clients',
		home_url( '/blog/' )          => 'Blog',
		home_url( '/contact-us/' )    => 'Contact us',
	),
);
?>
<footer class="fm-footer">
	<div class="fm-footer__glow" aria-hidden="true"></div>
	<div class="fm-footer__in">
		<div class="fm-footer__brand">
			<a class="fm-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Fulfillmen home">
				<?php if ( $fm_logo ) : ?>
					<img src="<?php echo esc_url( $fm_logo ); ?>" alt="Fulfillmen" width="160" height="46" loading="lazy">
				<?php else : ?>
					<span>Fulfillmen</span>
				<?php endif; ?>
			</a>
			<p>Sourcing, quality checks, warehousing and global shipping from China. One team, one dashboard, no surprise costs.</p>
			<a class="fm-footer__cta" href="<?php echo esc_url( $fm_quote ); ?>" data-fm-cta="footer">Get my free quote <?php echo fm_lp_icon( 'arrow' ); // phpcs:ignore ?></a>
		</div>
		<?php foreach ( $fm_cols as $fm_title => $fm_links ) : ?>
			<nav class="fm-footer__col" aria-label="<?php echo esc_attr( $fm_title ); ?>">
				<p><?php echo esc_html( $fm_title ); ?></p>
				<?php foreach ( $fm_links as $fm_href => $fm_label ) : ?>
					<a href="<?php echo esc_url( $fm_href ); ?>"><?php echo esc_html( $fm_label ); ?></a>
				<?php endforeach; ?>
			</nav>
		<?php endforeach; ?>
		<div class="fm-footer__col">
			<p>Visit us</p>
			<span class="fm-footer__addr"><?php echo fm_lp_icon( 'pin' ); // phpcs:ignore ?> <?php echo esc_html( fm_lp_opt( 'address' ) ); ?></span>
			<?php if ( $fm_wa ) : ?>
				<a href="https://wa.me/<?php echo esc_attr( $fm_wa ); ?>" target="_blank" rel="noopener"><?php echo fm_lp_icon( 'chat' ); // phpcs:ignore ?> WhatsApp us</a>
			<?php endif; ?>
		</div>
	</div>
	<div class="fm-footer__bar">
		<p>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Fulfillmen. All rights reserved. · <a href="<?php echo esc_url( fm_lp_opt( 'privacy_url' ) ); ?>">Privacy policy</a></p>
		<a href="#top" data-fm-top>Back to top ↑</a>
	</div>
</footer>
