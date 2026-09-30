<?php
/**
 * Title: A day in pictures
 * Slug: wild-lemon/photo-essay
 * Categories: wild-lemon, gallery
 * Description: An editorial photo essay pairing a wide landscape with a portrait detail and descriptive captions.
 * Viewport width: 1200
 *
 * @package Wild_Lemon
 */
?>
<!-- wp:group {"align":"wide","className":"wl-pattern-photo-essay","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide wl-pattern-photo-essay">
	<!-- wp:paragraph {"className":"wl-pattern-kicker"} -->
	<p class="wl-pattern-kicker"><?php esc_html_e( 'A day in pictures', 'wild-lemon' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:heading {"className":"wl-pattern-feature-title"} -->
	<h2 class="wp-block-heading wl-pattern-feature-title"><?php esc_html_e( 'Out in the world. Back to yourself.', 'wild-lemon' ); ?></h2>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"wl-pattern-lede"} -->
	<p class="wl-pattern-lede"><?php esc_html_e( 'A walk without a destination. A few lines before the light goes. Two ways to pay attention to an ordinary day.', 'wild-lemon' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:columns {"className":"wl-photo-layout"} -->
	<div class="wp-block-columns wl-photo-layout">
		<!-- wp:column {"width":"66.667%"} -->
		<div class="wp-block-column" style="flex-basis:66.667%">
			<!-- wp:image {"aspectRatio":"3/2","scale":"cover","sizeSlug":"full","linkDestination":"none"} -->
			<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/garden-walk.webp' ) ); ?>" alt="<?php esc_attr_e( 'A stone path winds between herbs, terracotta pots, and an olive tree.', 'wild-lemon' ); ?>" style="aspect-ratio:3/2;object-fit:cover"/><figcaption class="wp-element-caption"><?php esc_html_e( '01 — Take the path you have time for.', 'wild-lemon' ); ?></figcaption></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"33.333%"} -->
		<div class="wp-block-column" style="flex-basis:33.333%">
			<!-- wp:image {"aspectRatio":"3/4","scale":"cover","sizeSlug":"full","linkDestination":"none"} -->
			<figure class="wp-block-image size-full"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/quiet-notebook.webp' ) ); ?>" alt="<?php esc_attr_e( 'An open notebook and pencil beside a cup of tea and an olive sprig.', 'wild-lemon' ); ?>" style="aspect-ratio:3/4;object-fit:cover"/><figcaption class="wp-element-caption"><?php esc_html_e( '02 — Bring a little of it home.', 'wild-lemon' ); ?></figcaption></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
