<?php
/**
 * Title: Notes on the everyday
 * Slug: wild-lemon/two-column-notes
 * Categories: wild-lemon, text
 * Description: A spacious notebook layout with a section introduction and two numbered notes.
 * Viewport width: 1200
 *
 * @package Wild_Lemon
 */
?>
<!-- wp:group {"align":"wide","className":"wl-pattern-notes","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide wl-pattern-notes">
	<!-- wp:group {"className":"wl-notes-heading","layout":{"type":"default"}} -->
	<div class="wp-block-group wl-notes-heading">
		<!-- wp:paragraph {"className":"wl-pattern-kicker"} -->
		<p class="wl-pattern-kicker"><?php esc_html_e( 'From the notebook', 'wild-lemon' ); ?></p>
		<!-- /wp:paragraph -->
		<!-- wp:heading -->
		<h2 class="wp-block-heading"><?php esc_html_e( 'Small rituals. Rich days.', 'wild-lemon' ); ?></h2>
		<!-- /wp:heading -->
	</div>
	<!-- /wp:group -->
	<!-- wp:columns {"className":"wl-pattern-note-columns"} -->
	<div class="wp-block-columns wl-pattern-note-columns">
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"className":"wl-note-number"} -->
			<p class="wl-note-number"><?php esc_html_e( '01', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php esc_html_e( 'Follow your curiosity', 'wild-lemon' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><?php esc_html_e( 'Take the long way home. Stop at the market. Leave a little room in the day for something you did not plan.', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:paragraph {"className":"wl-note-number"} -->
			<p class="wl-note-number"><?php esc_html_e( '02', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
			<!-- wp:heading {"level":3} -->
			<h3 class="wp-block-heading"><?php esc_html_e( 'Make something to share', 'wild-lemon' ); ?></h3>
			<!-- /wp:heading -->
			<!-- wp:paragraph -->
			<p><?php esc_html_e( 'A meal, a story, a handful of flowers. The ordinary things become memorable when we offer them to someone else.', 'wild-lemon' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</div>
<!-- /wp:group -->
