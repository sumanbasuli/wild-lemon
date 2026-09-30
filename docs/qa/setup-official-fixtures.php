<?php
/** Add reproducible QA pages and the official nested menu to an isolated test site. */
if ( ! in_array( home_url(), array( 'http://localhost:8089', 'http://localhost:8090' ), true ) ) {
	WP_CLI::error( 'Use the isolated QA installation on port 8089 or 8090; this changes test content and its header.' );
}

function wild_lemon_qa_save( $type, $slug, $title, $content ) {
	$existing = get_page_by_path( $slug, OBJECT, $type );
	return wp_insert_post(
		array(
			'ID'             => $existing ? $existing->ID : 0,
			'post_type'      => $type,
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'post_content'   => wp_slash( $content ),
		),
		true
	);
}

$registry = WP_Block_Patterns_Registry::get_instance();
$content  = $registry->get_registered( 'wild-lemon/home-archive-list' )['content'];
$content  = str_replace( '"offset":4', '"offset":0', $content );
$content  = str_replace( '"search":""', '"search":"Edge Case: Many Categories"', $content );
wild_lemon_qa_save( 'page', 'archive-regression', 'Archive layout regression', $content );
wild_lemon_qa_save( 'page', 'pattern-gallery', 'Pattern Gallery', file_get_contents( get_theme_file_path( 'docs/qa/gallery-page.html' ) ) );
wild_lemon_qa_save( 'page', 'block-page-break-regression', 'Page Break block regression', '<!-- wp:paragraph --><p>Block page one.</p><!-- /wp:paragraph --><!-- wp:nextpage --><!--nextpage--><!-- /wp:nextpage --><!-- wp:paragraph --><p>Block page two.</p><!-- /wp:paragraph -->' );

$menu = wp_get_nav_menu_object( 'all-pages' );
if ( ! $menu ) {
	WP_CLI::error( 'Import the official Theme Unit Test data first.' );
}
$content = WP_Classic_To_Block_Menu_Converter::convert( $menu );
if ( is_wp_error( $content ) ) {
	WP_CLI::error( $content->get_error_message() );
}
$nav    = wild_lemon_qa_save( 'wp_navigation', 'official-nested-pages', 'Official nested pages', $content );
$header = $registry->get_registered( 'wild-lemon/header' )['content'];
$header = str_replace( '<!-- wp:navigation {', '<!-- wp:navigation {"ref":' . $nav . ',', $header );
$part   = wild_lemon_qa_save( 'wp_template_part', 'header', 'Header — QA navigation fixture', $header );
wp_set_object_terms( $part, 'wild-lemon', 'wp_theme' );
wp_set_object_terms( $part, 'header', 'wp_template_part_area' );
WP_CLI::success( 'QA pages and official nested menu are ready.' );
