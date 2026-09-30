<?php
/**
 * Wild Lemon functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Wild_Lemon
 * @since Wild Lemon 1.0
 */

if ( ! function_exists( 'wild_lemon_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_setup() {
		add_editor_style( 'style.css' );
	}
endif;
add_action( 'after_setup_theme', 'wild_lemon_setup' );

if ( ! function_exists( 'wild_lemon_enqueue_styles' ) ) :
	/**
	 * Enqueues the theme stylesheet.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_enqueue_styles() {
		$wild_lemon_version = wp_get_theme()->get( 'Version' );
		if ( wp_is_development_mode( 'theme' ) ) {
			$wild_lemon_version = (string) filemtime( get_stylesheet_directory() . '/style.css' );
		}
		wp_enqueue_style(
			'wild-lemon-style',
			get_stylesheet_uri(),
			array(),
			$wild_lemon_version
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'wild_lemon_enqueue_styles' );

if ( ! function_exists( 'wild_lemon_block_styles' ) ) :
	/**
	 * Registers the block style variations bundled with the theme.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_block_styles() {
		register_block_style(
			'core/post-terms',
			array(
				'name'  => 'wild-lemon-pill',
				'label' => __( 'Lemon Pill', 'wild-lemon' ),
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'  => 'wild-lemon-pill-outline',
				'label' => __( 'Outline Pill', 'wild-lemon' ),
			)
		);
	}
endif;
add_action( 'init', 'wild_lemon_block_styles' );

if ( ! function_exists( 'wild_lemon_pattern_categories' ) ) :
	/**
	 * Registers the block pattern category used by the theme.
	 *
	 * @since Wild Lemon 1.0
	 *
	 * @return void
	 */
	function wild_lemon_pattern_categories() {
		register_block_pattern_category(
			'wild-lemon',
			array(
				'label'       => __( 'Wild Lemon', 'wild-lemon' ),
				'description' => __( 'Patterns bundled with the Wild Lemon theme.', 'wild-lemon' ),
			)
		);
	}
endif;
add_action( 'init', 'wild_lemon_pattern_categories' );

/**
 * Remember a Query Loop's initial offset separately from its paged offset.
 *
 * Core adds the current page's offset when building the query, but WP_Query
 * includes the initially skipped posts in its pagination total. Tag only
 * queries built by core Query Loop blocks; leave other WP_Query instances alone.
 *
 * @param array    $query Query arguments supplied by the core block.
 * @param WP_Block $block Block using the query context.
 * @return array
 */
function wild_lemon_query_loop_offset( $query, $block ) {
	$wild_lemon_offset = absint( $block->context['query']['offset'] ?? 0 );
	if ( $wild_lemon_offset && empty( $block->context['query']['inherit'] ) ) {
		$query['wild_lemon_initial_offset'] = $wild_lemon_offset;
	}
	return $query;
}
add_filter( 'query_loop_block_query_vars', 'wild_lemon_query_loop_offset', 10, 2 );

/**
 * Count only posts available after a core Query Loop's initial offset.
 *
 * @link https://developer.wordpress.org/reference/hooks/found_posts/
 *
 * @param int      $found_posts Total matching posts, before the offset.
 * @param WP_Query $query       Query being counted.
 * @return int
 */
function wild_lemon_query_loop_found_posts( $found_posts, $query ) {
	$wild_lemon_offset = (int) $query->get( 'wild_lemon_initial_offset' );
	if ( $wild_lemon_offset > 0 ) {
		return max( 0, $found_posts - $wild_lemon_offset );
	}
	return $found_posts;
}
add_filter( 'found_posts', 'wild_lemon_query_loop_found_posts', 10, 2 );
