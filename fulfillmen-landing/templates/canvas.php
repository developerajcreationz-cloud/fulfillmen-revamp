<?php
/**
 * Fulfillmen Canvas: full-width page with the floating pill header and the
 * dark footer (no theme navigation leaks). Renders the page content (shortcodes).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$fm_post    = get_post();
$fm_is_land = $fm_post && has_shortcode( $fm_post->post_content, 'fulfillmen_landing' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'fm-canvas' ); ?>>
<?php wp_body_open(); ?>
<?php include FM_LP_DIR . 'templates/partials/header.php'; ?>
<main id="fm-main" class="fm-main">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php include FM_LP_DIR . 'templates/partials/footer.php'; ?>
<?php wp_footer(); ?>
</body>
</html>
