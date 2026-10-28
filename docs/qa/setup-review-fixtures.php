<?php
/** Reproduce the October spacing and current-menu reports on isolated QA sites. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
if ( ! in_array( home_url(), array( 'http://localhost:8089', 'http://localhost:8090' ), true ) ) {
	WP_CLI::error( 'Use an isolated Wild Lemon QA installation.' );
}

$owner  = 'review-2026-10-28';
$author = get_user_by( 'login', 'wildlemonqa' );
if ( ! $author ) {
	WP_CLI::error( 'The QA author is required.' );
}
$image = esc_url( get_theme_file_uri( 'assets/images/garden-walk.webp' ) );
$photo = '<!-- wp:image {"sizeSlug":"full","linkDestination":"none"} --><figure class="wp-block-image size-full"><img src="' . $image . '" alt="A garden path between herbs and terracotta pots."/></figure><!-- /wp:image -->';
$paragraph = '<!-- wp:paragraph --><p><strong>Lorem Ipsum</strong> is simply dummy text of the printing and typesetting industry. This sample content reproduces the reported spacing and navigation cases.</p><!-- /wp:paragraph -->';
$heading = '<!-- wp:heading --><h2 class="wp-block-heading">What is Lorem Ipsum?</h2><!-- /wp:heading -->';
$columns = '<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column">' . $photo . '</div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column">' . $paragraph . '</div><!-- /wp:column --></div><!-- /wp:columns -->';
$attachments = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'numberposts' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ids' ) );
$featured = $attachments ? (int) $attachments[0] : 0;
$cases = array(
	'post-columns'   => array( 'post', 'Test 2', $columns, 0 ),
	'post-paragraph' => array( 'post', 'Test 3', $paragraph, 0 ),
	'post-heading'   => array( 'post', 'A note with a heading', $heading . $paragraph, 0 ),
	'post-featured'  => array( 'post', 'A note with a featured photograph', $paragraph, $featured ),
	'page-heading'   => array( 'page', 'Test Page 26th October', $heading . $paragraph . $photo, 0 ),
	'page-paragraph' => array( 'page', 'A page beginning with a paragraph', $paragraph, 0 ),
	'page-featured'  => array( 'page', 'A page with a featured photograph', $heading . $paragraph, $featured ),
	'page-empty'     => array( 'page', 'An empty page', '', 0 ),
);
$fixtures = array();
foreach ( $cases as $slug => $case ) {
	$name = 'wl-review-' . $slug;
	$existing = get_page_by_path( $name, OBJECT, $case[0] );
	if ( $existing && get_post_meta( $existing->ID, '_wl_review_fixture', true ) !== $owner ) {
		WP_CLI::error( 'Refusing to replace an unrelated record: ' . $name );
	}
	$date = gmdate( 'Y-m-d H:i:s', strtotime( '2026-10-26 10:00:00 UTC' ) + count( $fixtures ) * HOUR_IN_SECONDS );
	$id = wp_insert_post( array(
		'ID' => $existing ? $existing->ID : 0, 'post_type' => $case[0], 'post_name' => $name,
		'post_title' => $case[1], 'post_content' => wp_slash( $case[2] ), 'post_status' => 'publish',
		'post_author' => $author->ID, 'post_date' => $date, 'post_date_gmt' => $date,
		'comment_status' => 'closed', 'ping_status' => 'closed',
		'meta_input' => array( '_wl_review_fixture' => $owner ),
	), true );
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	if ( $case[3] ) {
		set_post_thumbnail( $id, $case[3] );
	} else {
		delete_post_thumbnail( $id );
	}
	$fixtures[ $slug ] = array( 'id' => $id, 'url' => get_permalink( $id ), 'featured' => (bool) $case[3] );
}

// Extend only the QA menu selected by setup-official-fixtures.php.
$navigation = get_page_by_path( 'official-nested-pages', OBJECT, 'wp_navigation' );
if ( ! $navigation ) {
	WP_CLI::error( 'Run setup-official-fixtures.php first.' );
}
if ( false === strpos( $navigation->post_content, 'wl-review-post-columns' ) ) {
	$attributes = array( 'label' => 'Test Page 26th October', 'type' => 'page', 'kind' => 'post-type', 'id' => $fixtures['page-heading']['id'], 'url' => $fixtures['page-heading']['url'] );
	$menu = '<!-- wp:navigation-submenu ' . wp_json_encode( $attributes ) . ' -->';
	foreach ( array( 'post-columns' => 'Test 2', 'post-paragraph' => 'Test 3' ) as $slug => $label ) {
		$attributes = array( 'label' => $label, 'type' => 'post', 'kind' => 'post-type', 'id' => $fixtures[ $slug ]['id'], 'url' => $fixtures[ $slug ]['url'] );
		$menu .= '<!-- wp:navigation-link ' . wp_json_encode( $attributes ) . ' /-->';
	}
	$menu .= '<!-- /wp:navigation-submenu -->';
	wp_update_post( array( 'ID' => $navigation->ID, 'post_content' => wp_slash( $menu . $navigation->post_content ) ) );
}
WP_CLI::line( wp_json_encode( $fixtures, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
