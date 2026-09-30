<?php
/**
 * Title: A slower weekend
 * Slug: wild-lemon/slow-weekend
 * Categories: wild-lemon, text
 * Description: A quiet two-column checklist with three expandable notes, built with native Details blocks.
 * Viewport width: 1200
 *
 * @package Wild_Lemon
 */
?>
<!-- wp:group {"align":"wide","className":"wl-pattern-weekend","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide wl-pattern-weekend">
	<!-- wp:columns {"className":"wl-weekend-layout"} -->
	<div class="wp-block-columns wl-weekend-layout">
		<!-- wp:column {"width":"40%"} -->
		<div class="wp-block-column" style="flex-basis:40%">
			<!-- wp:paragraph {"className":"wl-pattern-kicker"} -->
			<p class="wl-pattern-kicker"><?php esc_html_e( 'A little room to breathe', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"className":"wl-pattern-feature-title"} -->
			<h2 class="wp-block-heading wl-pattern-feature-title"><?php esc_html_e( 'Make a little less of a plan.', 'wild-lemon' ); ?></h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"wl-pattern-lede"} -->
			<p class="wl-pattern-lede"><?php esc_html_e( 'A few gentle starting points for a weekend that feels like your own.', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column {"width":"60%","className":"wl-weekend-notes"} -->
		<div class="wp-block-column wl-weekend-notes" style="flex-basis:60%">
			<!-- wp:details {"showContent":true,"className":"wl-weekend-detail"} -->
			<details class="wp-block-details wl-weekend-detail" open><summary><?php esc_html_e( 'Bring something fresh home', 'wild-lemon' ); ?></summary>
				<!-- wp:paragraph -->
				<p><?php esc_html_e( 'Visit the market with one bag and an open mind. A bunch of herbs or a handful of ripe fruit is enough to begin with.', 'wild-lemon' ); ?></p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->
			<!-- wp:details {"className":"wl-weekend-detail"} -->
			<details class="wp-block-details wl-weekend-detail"><summary><?php esc_html_e( 'Spend an hour outside', 'wild-lemon' ); ?></summary>
				<!-- wp:paragraph -->
				<p><?php esc_html_e( 'Find a patch of sunlight, follow a familiar path, or sit under a tree. Leave the headphones behind and see what you notice.', 'wild-lemon' ); ?></p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->
			<!-- wp:details {"className":"wl-weekend-detail"} -->
			<details class="wp-block-details wl-weekend-detail"><summary><?php esc_html_e( 'Save one small memory', 'wild-lemon' ); ?></summary>
				<!-- wp:paragraph -->
				<p><?php esc_html_e( 'Write a sentence, take a photograph, or press a leaf between pages. Keep something that brings the day back to you.', 'wild-lemon' ); ?></p>
				<!-- /wp:paragraph -->
			</details>
			<!-- /wp:details -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
