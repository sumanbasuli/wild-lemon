<?php
/**
 * Title: A thought to keep
 * Slug: wild-lemon/featured-quotation
 * Categories: wild-lemon, text
 * Description: A typographic quotation with a quiet editorial label and generous space.
 * Viewport width: 1200
 *
 * @package Wild_Lemon
 */
?>
<!-- wp:group {"align":"wide","className":"wl-pattern-quotation","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide wl-pattern-quotation">
	<!-- wp:paragraph {"className":"wl-pattern-kicker"} -->
	<p class="wl-pattern-kicker"><?php esc_html_e( 'A thought to keep', 'wild-lemon' ); ?></p>
	<!-- /wp:paragraph -->
	<!-- wp:quote {"className":"wl-editorial-quote"} -->
	<blockquote class="wp-block-quote wl-editorial-quote">
		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'There is a whole world in the things we walk past every day.', 'wild-lemon' ); ?></p>
		<!-- /wp:paragraph -->
		<cite><?php esc_html_e( 'From the journal', 'wild-lemon' ); ?></cite>
	</blockquote>
	<!-- /wp:quote -->
</div>
<!-- /wp:group -->
