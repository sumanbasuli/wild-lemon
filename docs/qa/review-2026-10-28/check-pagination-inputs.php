<?php
/** Exercise real pagination input coercion without executing its post queries. */
require '/var/www/html/wp-load.php';

if ( ! in_array( home_url(), array( 'http://localhost:8089', 'http://localhost:8090' ), true ) ) {
	throw new RuntimeException( 'Use an isolated QA installation.' );
}

$inputs = array(
	'missing'        => array( false, null ),
	'null'           => array( true, null ),
	'empty'          => array( true, '' ),
	'zero'           => array( true, '0' ),
	'negative-zero'  => array( true, '-0' ),
	'negative'       => array( true, '-3' ),
	'negative-large' => array( true, '-9999999999999999999999' ),
	'first'          => array( true, '1' ),
	'second'         => array( true, '2' ),
	'out-of-range'   => array( true, '99' ),
	'integer'        => array( true, 3 ),
	'float'          => array( true, 3.8 ),
	'true'           => array( true, true ),
	'false'          => array( true, false ),
	'word'           => array( true, 'garden' ),
	'numeric-tail'   => array( true, 'garden12' ),
	'numeric-prefix' => array( true, '12garden' ),
	'decimal'        => array( true, '3.8' ),
	'scientific'     => array( true, '3e2' ),
	'signed-spaces'  => array( true, '  +3 ' ),
	'empty-array'    => array( true, array() ),
	'value-array'    => array( true, array( '2' ) ),
	'nested-array'   => array( true, array( array( '2' ) ) ),
	'keyed-array'    => array( true, array( 'page' => '2' ) ),
	'html'           => array( true, '<b>12</b>' ),
	'html-suffix'    => array( true, '2<b>12</b>' ),
	'url-encoding'   => array( true, '12%2034' ),
	'underscore'     => array( true, '1_000' ),
	'leading-slash'  => array( true, '\\3' ),
	'internal-slash' => array( true, '2\\3' ),
	'leading-quote'  => array( true, "'2" ),
	'internal-quote' => array( true, "2'3" ),
	'leading-null'   => array( true, "\0" . '3' ),
	'internal-null'  => array( true, '2' . "\0" . '3' ),
	'large-number'   => array( true, '999999999999999999999999999999' ),
);
$original_get = $_GET;
$observed     = null;
$capture      = static function ( $query, $block, $page ) use ( &$observed ) {
	$observed = $page;
	return $query;
};
$short_query = static function () {
	return array();
};
$block       = new WP_Block(
	array(
		'blockName'    => 'core/query-pagination',
		'attrs'        => array( 'className' => 'wl-pagination' ),
		'innerBlocks'  => array(),
		'innerHTML'    => '',
		'innerContent' => array(),
	)
);
$block->context = array(
	'queryId' => 11,
	'query'   => array(
		'perPage'  => 4,
		'pages'    => 0,
		'offset'   => 0,
		'postType' => 'post',
		'order'    => 'desc',
		'orderBy'  => 'date',
		'inherit'  => false,
	),
);
$results = array();

add_filter( 'query_loop_block_query_vars', $capture, PHP_INT_MAX, 3 );
add_filter( 'posts_pre_query', $short_query, PHP_INT_MAX, 2 );
try {
	foreach ( $inputs as $name => $test ) {
		// WordPress adds slashes to real request globals before theme callbacks run.
		$_GET = $test[0] ? array( 'query-11-page' => wp_slash( $test[1] ) ) : array();
		$expected = max( 1, (int) ( $_GET['query-11-page'] ?? 1 ) );
		$observed = null;
		wild_lemon_query_pagination_visibility( '<nav>Fixture</nav>', array( 'attrs' => array( 'className' => 'wl-pagination' ) ), $block );
		if ( $observed !== $expected ) {
			throw new RuntimeException( $name . ': expected ' . $expected . ', got ' . var_export( $observed, true ) );
		}
		$results[] = array( 'case' => $name, 'input' => $test[1], 'expectedPage' => $expected, 'actualPage' => $observed, 'pass' => true );
	}
} finally {
	$_GET = $original_get;
	remove_filter( 'query_loop_block_query_vars', $capture, PHP_INT_MAX );
	remove_filter( 'posts_pre_query', $short_query, PHP_INT_MAX );
}

echo json_encode(
	array(
		'wordpress' => $wp_version,
		'php'       => PHP_VERSION,
		'cases'     => count( $results ),
		'scope'     => 'Actual theme pagination callback; native query page captured; post queries short-circuited without changing content',
		'results'   => $results,
	),
	JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
), "\n";
