<?php
/**
 * Fulfillmen Canvas: full-width page with a minimal conversion header
 * (no site navigation leaks). Renders the page content (shortcodes).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$fm_post    = get_post();
$fm_is_land = $fm_post && has_shortcode( $fm_post->post_content, 'fulfillmen_landing' );
$fm_logo    = fm_lp_opt( 'logo_url' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'fm-canvas' ); ?>>
<?php wp_body_open(); ?>
<a class="fm-skip" href="#fm-main">Skip to content</a>
<span id="top"></span>
<header class="fm-header" data-fm-header>
	<div class="fm-header__strip"><div class="fm-header__strip-in"><span><i class="fm-live"></i> <?php echo esc_html( fm_lp_opt( 'delivery_proof' ) ); ?></span><span>Quotes answered within 24 h</span><span>No minimums · No lock-in</span></div></div>
	<div class="fm-header__pill">
		<a class="fm-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Fulfillmen home">
			<?php if ( $fm_logo ) : ?>
				<img src="<?php echo esc_url( $fm_logo ); ?>" alt="Fulfillmen" width="120" height="34" decoding="async" fetchpriority="high">
			<?php else : ?>
				<span>Fulfillmen</span>
			<?php endif; ?>
		</a>
		<?php if ( $fm_is_land ) : ?>
			<nav class="fm-header__nav" aria-label="Page sections">
				<a href="#path">Your path</a>
				<a href="#how">Process</a>
				<a href="#proof">Results</a>
				<a href="#faq">FAQ</a>
			</nav>
			<a class="fm-btn fm-btn--teal fm-btn--sm" href="#quote" data-fm-cta="header">Get my free quote</a>
		<?php else : ?>
			<a class="fm-btn fm-btn--teal fm-btn--sm" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to fulfillmen.com</a>
		<?php endif; ?>
	</div>
</header>
<main id="fm-main" class="fm-main">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php wp_footer(); ?>
</body>
</html>
