<?php
/** Persistent, repeatable edge cases for the isolated WordPress QA installation. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}
if ( ! in_array( home_url(), array( 'http://localhost:8089', 'http://localhost:8090' ), true ) ) {
	WP_CLI::error( 'Use the isolated QA installation on port 8089 or 8090.' );
}

$owner   = 'polish-2026-10-02';
$authors = get_users( array( 'number' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ID' ) );
if ( ! $authors ) {
	WP_CLI::error( 'An existing WordPress user is required.' );
}
$author = (int) $authors[0];

// Populate missing QA profile content without replacing a biography edited by a reviewer.
if ( '' === get_user_meta( $author, 'description', true ) ) {
	update_user_meta( $author, 'description', 'Writing about seasonal cooking, small journeys, and the details that make everyday life worth noticing. These notes bring together recipes, field observations, and ideas for a slower weekend, with an eye for practical pleasures and a life well noticed.' );
}

$save = function ( $type, $slug, $title, $content, $categories = array(), $comments = 'closed' ) use ( $owner, $author ) {
	$slug     = 'wl-polish-' . $slug;
	$existing = get_page_by_path( $slug, OBJECT, $type );
	if ( $existing && get_post_meta( $existing->ID, '_wl_polish_fixture', true ) !== $owner ) {
		WP_CLI::error( 'Refusing to replace an unrelated record: ' . $slug );
	}
	$id = wp_insert_post(
		array(
			'ID'             => $existing ? $existing->ID : 0,
			'post_type'      => $type,
			'post_name'      => $slug,
			'post_title'     => $title,
			'post_content'   => wp_slash( $content ),
			'post_author'    => $author,
			'post_status'    => 'publish',
			'post_date'      => '2000-01-15 10:00:00',
			'post_date_gmt'  => get_gmt_from_date( '2000-01-15 10:00:00' ),
			'comment_status' => $comments,
			'ping_status'    => 'closed',
			'meta_input'     => array( '_wl_polish_fixture' => $owner ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		WP_CLI::error( $id->get_error_message() );
	}
	if ( 'post' === $type ) {
		$result = wp_set_post_terms( $id, $categories, 'category' );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
	}
	return array( 'id' => $id, 'url' => get_permalink( $id ), 'categories' => count( $categories ), 'comments' => $comments );
};

$category_names = array(
	'essays'       => 'Essays',
	'field-notes'  => 'Field Notes',
	'recipes'      => 'Recipes',
	'places'       => 'Places',
	'garden'       => 'The Garden',
	'long-label'   => 'Seasonal food, small discoveries and unhurried days in the countryside',
	'unbroken'     => 'ExtraordinaryEverydayDiscoveriesAcrossTheEntireSeasonWithoutASingleBreak',
);
$categories = array();
foreach ( $category_names as $slug => $name ) {
	$term = get_term_by( 'slug', 'wl-polish-' . $slug, 'category' );
	if ( $term && get_term_meta( $term->term_id, '_wl_polish_fixture', true ) !== $owner ) {
		WP_CLI::error( 'Refusing to replace an unrelated category: ' . $slug );
	}
	if ( ! $term ) {
		$result = wp_insert_term( $name, 'category', array( 'slug' => 'wl-polish-' . $slug ) );
		if ( is_wp_error( $result ) ) {
			WP_CLI::error( $result->get_error_message() );
		}
		$id = (int) $result['term_id'];
	} else {
		$id = (int) $term->term_id;
		wp_update_term( $id, 'category', array( 'name' => $name ) );
	}
	update_term_meta( $id, '_wl_polish_fixture', $owner );
	$categories[] = $id;
}

$citrus  = esc_url( get_theme_file_uri( 'assets/images/citrus-still-life.webp' ) );
$garden  = esc_url( get_theme_file_uri( 'assets/images/garden-walk.webp' ) );
$notes   = esc_url( get_theme_file_uri( 'assets/images/quiet-notebook.webp' ) );
$home    = esc_url( home_url( '/' ) );
$intro   = '<!-- wp:paragraph --><p>A Saturday walk begins at the kitchen table. These notes collect a few recipes, places and observations from a slower day.</p><!-- /wp:paragraph -->';
$mixed   = $intro . <<<HTML
<!-- wp:heading --><h2 class="wp-block-heading">At the market</h2><!-- /wp:heading -->
<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:paragraph --><p>Look for fruit that smells like the season. Keep a little space in the bag for something unexpected.</p><!-- /wp:paragraph --><!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>One bunch of herbs</li><!-- /wp:list-item --><!-- wp:list-item --><li>A loaf to share</li><!-- /wp:list-item --><!-- wp:list-item --><li>Lemons for the afternoon</li><!-- /wp:list-item --></ul><!-- /wp:list --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"sizeSlug":"full","linkDestination":"none"} --><figure class="wp-block-image size-full"><img src="$citrus" alt="Lemons and a leafy branch beside a ceramic bowl on linen."/><figcaption class="wp-element-caption">Small things brought home.</figcaption></figure><!-- /wp:image --></div><!-- /wp:column --></div><!-- /wp:columns -->
<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>Give the ordinary moments enough time to become memorable.</p><!-- /wp:paragraph --><cite>A note from the kitchen</cite></blockquote><!-- /wp:quote -->
<!-- wp:heading --><h2 class="wp-block-heading">A small seasonal table</h2><!-- /wp:heading -->
<!-- wp:table {"hasFixedLayout":false} --><figure class="wp-block-table"><table><thead><tr><th scope="col">Ingredient</th><th scope="col">When to use it</th><th scope="col">A longer serving suggestion</th></tr></thead><tbody><tr><td>Lemons</td><td>Early afternoon</td><td>With a pot of tea and a slice of cake still warm from the oven</td></tr><tr><td>Fresh herbs</td><td>At lunch</td><td>Toss through roasted vegetables with good olive oil</td></tr><tr><td>SlowlyGrownSeasonalVegetablesWithAnUnexpectedlyLongName</td><td>At supper</td><td>Serve on the largest plate and leave room for another chair</td></tr></tbody></table><figcaption class="wp-element-caption">A table with descriptive headers and a deliberately long cell.</figcaption></figure><!-- /wp:table -->
<!-- wp:gallery {"columns":2,"linkTo":"none"} --><figure class="wp-block-gallery has-nested-images columns-2 is-cropped"><!-- wp:image {"sizeSlug":"full","linkDestination":"none"} --><figure class="wp-block-image size-full"><img src="$garden" alt="A garden path between herbs and terracotta pots."/><figcaption class="wp-element-caption">The long way home.</figcaption></figure><!-- /wp:image --><!-- wp:image {"sizeSlug":"full","linkDestination":"none"} --><figure class="wp-block-image size-full"><img src="$notes" alt="An open notebook with tea and an olive sprig."/><figcaption class="wp-element-caption">A few lines before the light goes.</figcaption></figure><!-- /wp:image --></figure><!-- /wp:gallery -->
<!-- wp:details --><details class="wp-block-details"><summary>A note to open with a keyboard or a pointer</summary><!-- wp:paragraph --><p>Leave the afternoon unplanned. Read a page, take a walk, or make something for someone else.</p><!-- /wp:paragraph --></details><!-- /wp:details -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="$home">Return to the journal</a></div><!-- /wp:button --><!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="#a-longer-thought">Read the longer thought</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
<!-- wp:heading {"anchor":"a-longer-thought"} --><h2 class="wp-block-heading" id="a-longer-thought">A longer thought</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Simple food, good company and a little attention are enough. This final paragraph also gives the buttons a meaningful destination within the page.</p><!-- /wp:paragraph -->
HTML;

$fixtures = array();
$fixtures['one-category']   = $save( 'post', 'one-category', 'A small discovery', $intro, array_slice( $categories, 0, 1 ) );
$fixtures['four-categories'] = $save( 'post', 'four-categories', 'A day between the market and the garden', $mixed, array_slice( $categories, 0, 4 ), 'open' );
$fixtures['seven-categories'] = $save( 'post', 'seven-categories', 'The small things we brought home from an unhurried Saturday in the countryside', $mixed, $categories );
$fixtures['brief-title']    = $save( 'post', 'brief-title', 'Tea', $intro, array_slice( $categories, 0, 1 ) );
$fixtures['long-title']     = $save( 'post', 'long-title', 'Some days ask us to slow down, take the longer path through the garden, find something good at the market and leave enough room at the table for an unexpected guest', $mixed, array( $categories[5] ) );
$fixtures['unbroken-title'] = $save( 'post', 'unbroken-title', 'AnExtraordinarilyLongUnbrokenTitleAboutSeasonalFoodAndEverydayDiscoveriesThatContinuesWithoutAnySpaces', $intro, array( $categories[6] ) );
$fixtures['no-featured-image'] = $save( 'post', 'no-featured-image', 'Notes without a photograph', $intro, array_slice( $categories, 0, 4 ) );

$images = get_posts( array( 'post_type' => 'attachment', 'post_mime_type' => 'image', 'posts_per_page' => 1, 'orderby' => 'ID', 'order' => 'ASC', 'fields' => 'ids' ) );
if ( $images ) {
	set_post_thumbnail( $fixtures['four-categories']['id'], $images[0] );
	set_post_thumbnail( $fixtures['seven-categories']['id'], $images[0] );
}
delete_post_thumbnail( $fixtures['no-featured-image']['id'] );

$registry = WP_Block_Patterns_Registry::get_instance();
$patterns = '';
foreach ( array( 'editorial-introduction', 'seasonal-table', 'photo-essay', 'two-column-notes', 'slow-weekend', 'featured-quotation' ) as $slug ) {
	$pattern = $registry->get_registered( 'wild-lemon/' . $slug );
	if ( ! $pattern ) {
		WP_CLI::error( 'The active Wild Lemon theme must register pattern: ' . $slug );
	}
	$patterns .= $pattern['content'] . "\n";
}
$fixtures['patterns-normal'] = $save( 'page', 'patterns-normal', 'A collection of everyday notes', $patterns );
foreach ( array( 320, 600 ) as $width ) {
	$content = '<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column {"width":"' . $width . 'px"} --><div class="wp-block-column" style="flex-basis:' . $width . 'px">' . $patterns . '</div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column">' . $intro . '</div><!-- /wp:column --></div><!-- /wp:columns -->';
	$fixtures[ 'patterns-column-' . $width ] = $save( 'page', 'patterns-column-' . $width, 'Notes inside a ' . $width . '-pixel column', $content );
}
$fixtures['page-comments'] = $save( 'page', 'page-comments', 'A place for conversation', $intro, array(), 'open' );
$fixtures['short-page']    = $save( 'page', 'short-page', 'A quiet note', '<!-- wp:paragraph --><p>Thank you for stopping by.</p><!-- /wp:paragraph -->' );

WP_CLI::line( wp_json_encode( array( 'owner' => $owner, 'author' => $author, 'author_biography' => get_user_meta( $author, 'description', true ), 'author_archive' => get_author_posts_url( $author ), 'categories' => $categories, 'fixtures' => $fixtures ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
