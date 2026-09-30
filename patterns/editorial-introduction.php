<?php
/**
 * Title: A life well noticed
 * Slug: wild-lemon/editorial-introduction
 * Categories: wild-lemon, featured
 * Description: An editorial introduction with generous typography and a sunlit citrus still life.
 * Viewport width: 1200
 *
 * @package Wild_Lemon
 */
?>
<!-- wp:group {"align":"wide","className":"wl-pattern-intro","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide wl-pattern-intro">
	<!-- wp:columns {"verticalAlignment":"center","className":"wl-intro-layout"} -->
	<div class="wp-block-columns are-vertically-aligned-center wl-intro-layout">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:paragraph {"className":"wl-pattern-kicker"} -->
			<p class="wl-pattern-kicker"><?php esc_html_e( 'A slower kind of journal', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"className":"wl-pattern-intro-title"} -->
			<h2 class="wp-block-heading wl-pattern-intro-title"><?php esc_html_e( 'A life well noticed.', 'wild-lemon' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"wl-pattern-intro-copy"} -->
			<p class="wl-pattern-intro-copy"><?php esc_html_e( 'A place for seasonal food, unhurried days, and the small discoveries that stay with us.', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:paragraph {"className":"wl-pattern-footnote"} -->
			<p class="wl-pattern-footnote"><?php esc_html_e( 'Food. Place. Everyday life.', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:image {"aspectRatio":"4/3","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"wl-intro-image"} -->
			<figure class="wp-block-image size-full wl-intro-image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/citrus-still-life.webp' ) ); ?>" alt="<?php esc_attr_e( 'Sunlit lemons and a leafy branch beside a ceramic bowl on linen.', 'wild-lemon' ); ?>" style="aspect-ratio:4/3;object-fit:cover"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
