<?php
/**
 * Title: A table for the season
 * Slug: wild-lemon/seasonal-table
 * Categories: wild-lemon, featured
 * Description: A seasonal food feature with a generous photograph, editorial introduction, and two short serving notes.
 * Viewport width: 1200
 *
 * @package Wild_Lemon
 */
?>
<!-- wp:group {"align":"wide","className":"wl-pattern-table","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide wl-pattern-table">
	<!-- wp:columns {"verticalAlignment":"center","className":"wl-table-layout"} -->
	<div class="wp-block-columns are-vertically-aligned-center wl-table-layout">
		<!-- wp:column {"verticalAlignment":"center"} -->
		<div class="wp-block-column is-vertically-aligned-center">
			<!-- wp:image {"aspectRatio":"4/5","scale":"cover","sizeSlug":"full","linkDestination":"none","className":"wl-table-image"} -->
			<figure class="wp-block-image size-full wl-table-image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/seasonal-table.webp' ) ); ?>" alt="<?php esc_attr_e( 'Roasted vegetables, olives, and sourdough on a linen-covered table.', 'wild-lemon' ); ?>" style="aspect-ratio:4/5;object-fit:cover"/></figure>
			<!-- /wp:image -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"verticalAlignment":"center","className":"wl-table-copy"} -->
		<div class="wp-block-column is-vertically-aligned-center wl-table-copy">
			<!-- wp:paragraph {"className":"wl-pattern-kicker"} -->
			<p class="wl-pattern-kicker"><?php esc_html_e( 'At the table', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"className":"wl-pattern-feature-title"} -->
			<h2 class="wp-block-heading wl-pattern-feature-title"><?php esc_html_e( 'Good things, in season.', 'wild-lemon' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"wl-pattern-lede"} -->
			<p class="wl-pattern-lede"><?php esc_html_e( 'Something from the garden, a loaf still warm, and a little time to sit together. A good lunch can be that simple.', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:columns {"className":"wl-table-notes"} -->
			<div class="wp-block-columns wl-table-notes">
				<!-- wp:column -->
				<div class="wp-block-column">
					<!-- wp:heading {"level":3} -->
					<h3 class="wp-block-heading"><?php esc_html_e( 'Keep it seasonal', 'wild-lemon' ); ?></h3>
					<!-- /wp:heading -->
					<!-- wp:paragraph -->
					<p><?php esc_html_e( 'Start with what looks best at the market. Let the rest follow.', 'wild-lemon' ); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:column -->
				<!-- wp:column -->
				<div class="wp-block-column">
					<!-- wp:heading {"level":3} -->
					<h3 class="wp-block-heading"><?php esc_html_e( 'Leave room', 'wild-lemon' ); ?></h3>
					<!-- /wp:heading -->
					<!-- wp:paragraph -->
					<p><?php esc_html_e( 'For another chair, a second helping, and an unhurried afternoon.', 'wild-lemon' ); ?></p>
					<!-- /wp:paragraph -->
				</div>
				<!-- /wp:column -->
			</div>
			<!-- /wp:columns -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
