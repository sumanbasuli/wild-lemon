<?php
/**
 * Title: Reading Time
 * Slug: wild-lemon/hidden-reading-time
 * Categories: wild-lemon
 * Description: Native reading-time estimate for the current post.
 * Inserter: no
 *
 * @package Wild_Lemon
 */

// The core block was introduced in WordPress 6.9; keep older editors compatible.
if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'core/post-time-to-read' ) ) {
	return;
}

?>
<!-- wp:post-time-to-read {"displayAsRange":false,"className":"wl-reading-time","textColor":"muted-2","fontSize":"small"} /-->
