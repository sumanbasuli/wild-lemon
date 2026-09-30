<?php
/**
 * Run with WP-CLI on the local preview:
 * wp eval-file wp-content/themes/wild-lemon/docs/qa/check-pagination.php
 *
 * Draft fixtures are visible only to this process and deleted in finally.
 * Existing posts, options, and templates are not modified.
 */

$wild_lemon_test_ids     = array();
$wild_lemon_test_visible = 0;
$wild_lemon_test_get     = $_GET;
$wild_lemon_test_results = array();
$wild_lemon_test_error   = null;
$wild_lemon_test_filter  = static function ( $query ) use ( &$wild_lemon_test_ids, &$wild_lemon_test_visible ) {
	if ( 'post' === $query->get( 'post_type' ) ) {
		$query->set( 'post_status', 'draft' );
		$query->set( 'post__in', array_slice( $wild_lemon_test_ids, 0, $wild_lemon_test_visible ) ?: array( 0 ) );
	}
};

try {
	for ( $i = 0; $i < 13; ++$i ) {
		$id = wp_insert_post(
			array(
				'post_title'  => 'Wild Lemon pagination fixture ' . $i,
				'post_status' => 'draft',
				'post_date'   => sprintf( '2020-01-%02d 12:00:00', 14 - $i ),
			),
			true
		);
		if ( is_wp_error( $id ) ) {
			throw new RuntimeException( $id->get_error_message() );
		}
		$wild_lemon_test_ids[] = $id;
	}
	add_action( 'pre_get_posts', $wild_lemon_test_filter );
	$home = file_get_contents( get_template_directory() . '/templates/home.html' );
	libxml_use_internal_errors( true );

	foreach ( array( 0, 1, 4, 5, 8, 9, 12, 13 ) as $count ) {
		$wild_lemon_test_visible = $count;
		foreach ( array( 1, 2, 3, 99 ) as $page ) {
			$_GET = array( 'query-11-page' => (string) $page );
			$html = do_blocks( $home );
			$dom  = new DOMDocument();
			$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
			$xpath = new DOMXPath( $dom );
			$loops = $xpath->query( '//*[contains(concat(" ", normalize-space(@class), " "), " wp-block-query ")]' );
			if ( 3 !== $loops->length ) {
				throw new RuntimeException( 'Expected the three original homepage Query Loops.' );
			}
			$expected_rows = array( min( 1, $count ), min( 3, max( 0, $count - 1 ) ), min( 4, max( 0, $count - 4 - 4 * ( $page - 1 ) ) ) );
			foreach ( $loops as $index => $loop ) {
				$rows  = $xpath->query( './/li[contains(concat(" ", normalize-space(@class), " "), " wp-block-post ")]', $loop )->length;
				$empty = $xpath->query( './/*[contains(concat(" ", normalize-space(@class), " "), " wp-block-query-no-results ")]', $loop );
				if ( $rows !== $expected_rows[ $index ] || ( 0 === $rows ) !== ( 1 === $empty->length ) ) {
					throw new RuntimeException( "Unexpected rows/empty state: {$count} posts, page {$page}, loop {$index}." );
				}
				if ( 0 === $rows && false === strpos( $empty->item( 0 )->textContent, 'No posts found.' ) ) {
					throw new RuntimeException( 'The empty state must contain its message.' );
				}
			}
			$archive    = $loops->item( 2 );
			$pages      = (int) ceil( max( 0, $count - 4 ) / 4 );
			$pagination = $xpath->query( './/nav', $archive )->length;
			$next       = $xpath->query( './/a[contains(@class, "wp-block-query-pagination-next")]', $archive )->length;
			$previous   = $xpath->query( './/a[contains(@class, "wp-block-query-pagination-previous")]', $archive )->length;
			if ( ( $pagination > 0 ) !== ( $pages > 1 && $page <= $pages ) || ( $next > 0 ) !== ( $page < $pages ) || ( $previous > 0 ) !== ( $page > 1 && $page <= $pages ) ) {
				throw new RuntimeException( "Unexpected pagination: {$count} posts, page {$page}." );
			}
			$actual_ids = array();
			foreach ( $xpath->query( './/li[contains(concat(" ", normalize-space(@class), " "), " wp-block-post ")]', $archive ) as $row ) {
				preg_match( '/\bpost-(\d+)\b/', $row->getAttribute( 'class' ), $match );
				$actual_ids[] = (int) $match[1];
			}
			$expected_ids = array_slice( array_slice( $wild_lemon_test_ids, 0, $count ), 4 + 4 * ( $page - 1 ), 4 );
			if ( $actual_ids !== $expected_ids ) {
				throw new RuntimeException( 'Archive pagination duplicated or skipped a post.' );
			}
			$wild_lemon_test_results[] = array( 'posts' => $count, 'page' => $page, 'archive_rows' => count( $actual_ids ), 'pagination' => (bool) $pagination, 'pass' => true );
			libxml_clear_errors();
		}
	}
	$control = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 4, 'offset' => 4 ) );
	if ( 13 !== $control->found_posts ) {
		throw new RuntimeException( 'An unrelated WP_Query was changed.' );
	}
} catch ( Throwable $error ) {
	$wild_lemon_test_error = $error->getMessage();
} finally {
	remove_action( 'pre_get_posts', $wild_lemon_test_filter );
	$_GET = $wild_lemon_test_get;
	foreach ( $wild_lemon_test_ids as $id ) {
		wp_delete_post( $id, true );
	}
}

if ( $wild_lemon_test_error ) {
	WP_CLI::error( $wild_lemon_test_error );
}
echo wp_json_encode( array( 'cases' => $wild_lemon_test_results, 'unrelated_queries_unchanged' => true, 'fixtures_removed' => true ), JSON_PRETTY_PRINT ) . "\n";
