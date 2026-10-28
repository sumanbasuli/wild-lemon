<?php
/** Read-only backend gates run through WP-CLI in the minimum PHP runtime. */
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'http://localhost:8090') {
    throw new RuntimeException('Minimum QA installation only.');
}
$root = get_template_directory();
$report = array(
    'url' => home_url(), 'wordpress' => $GLOBALS['wp_version'], 'php' => PHP_VERSION,
    'themeVersion' => wp_get_theme()->get('Version'),
    'sourceSha256' => array(), 'phpLint' => array(), 'nativeBlocks' => array(),
    'templateContentSpacing' => array(), 'patternReferences' => array(), 'issues' => array(),
);
$walk = function ($blocks, &$names, &$references) use (&$walk) {
    foreach ($blocks as $block) {
        if ($block['blockName']) {
            $names[$block['blockName']] = true;
        }
        if ('core/pattern' === $block['blockName']) {
            $references[$block['attrs']['slug']] = true;
        }
        $walk($block['innerBlocks'], $names, $references);
    }
};
$check_blocks = function ($label, $content) use (&$report, $walk) {
    $blocks = parse_blocks($content);
    $serialized = serialize_blocks($blocks);
    $roundtrip = parse_blocks($serialized) === $blocks;
    $names = array(); $references = array();
    $walk($blocks, $names, $references);
    $unregistered = array();
    foreach (array_keys($names) as $name) {
        if (0 !== strpos($name, 'core/') || !WP_Block_Type_Registry::get_instance()->is_registered($name)) {
            $unregistered[] = $name;
        }
    }
    foreach (array_keys($references) as $slug) {
        $registered = WP_Block_Patterns_Registry::get_instance()->is_registered($slug);
        $report['patternReferences'][] = array('from' => $label, 'slug' => $slug, 'registered' => $registered);
        if (!$registered) $report['issues'][] = 'Unregistered pattern: ' . $slug;
    }
    $pass = $roundtrip && !$unregistered;
    $report['nativeBlocks'][] = array('file' => $label, 'blocks' => array_keys($names), 'parseSerializeRoundtrip' => $roundtrip, 'unregistered' => $unregistered, 'pass' => $pass);
    if (!$pass) $report['issues'][] = 'Native block structure: ' . $label;
};

foreach (array_merge(array($root . '/functions.php'), glob($root . '/patterns/*.php')) as $file) {
    $relative = substr($file, strlen($root) + 1);
    $output = array(); $status = 0;
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1', $output, $status);
    $report['phpLint'][] = array('file' => $relative, 'pass' => 0 === $status, 'output' => implode("\n", $output));
    $report['sourceSha256'][$relative] = hash_file('sha256', $file);
    if ($status) $report['issues'][] = 'PHP lint: ' . $relative;
}

foreach (array_merge(glob($root . '/templates/*.html'), glob($root . '/parts/*.html')) as $file) {
    $relative = substr($file, strlen($root) + 1);
    $content = file_get_contents($file);
    $check_blocks($relative, $content);
    $report['sourceSha256'][$relative] = hash_file('sha256', $file);
    if (in_array($relative, array('templates/page.html', 'templates/single.html'), true)) {
        $pending = parse_blocks($content);
        while ($pending) {
            $block = array_shift($pending);
            if ('core/post-content' === $block['blockName']) {
                $spacing = $block['attrs']['style']['spacing'];
                $report['templateContentSpacing'][$relative] = $spacing;
                if (!isset($spacing['padding']['top']) || 'var:preset|spacing|60' !== $spacing['padding']['top'] || isset($spacing['margin']['top'])) {
                    $report['issues'][] = 'Unsupported content spacing remains: ' . $relative;
                }
            }
            $pending = array_merge($pending, $block['innerBlocks']);
        }
    }
}

$registered = WP_Block_Patterns_Registry::get_instance()->get_all_registered();
foreach ($registered as $pattern) {
    if (0 === strpos($pattern['name'], 'wild-lemon/')) {
        $check_blocks('registered-pattern:' . $pattern['name'], $pattern['content']);
    }
}
$report['themeJsonValid'] = is_array(json_decode(file_get_contents($root . '/theme.json'), true));
if (!$report['themeJsonValid']) $report['issues'][] = 'Invalid theme.json';
$report['postContentSpacingSupport'] = WP_Block_Type_Registry::get_instance()->get_registered('core/post-content')->supports['spacing'];
$report['readingTimeBlockAvailable'] = WP_Block_Type_Registry::get_instance()->is_registered('core/post-time-to-read');
$reading_pattern = WP_Block_Patterns_Registry::get_instance()->get_registered('wild-lemon/hidden-reading-time');
$report['optionalReadingTimePattern'] = array('registered' => (bool) $reading_pattern, 'emptyOnUnsupportedCore' => !$report['readingTimeBlockAvailable'] && '' === trim($reading_pattern['content']));
if (!$report['readingTimeBlockAvailable'] && !$report['optionalReadingTimePattern']['emptyOnUnsupportedCore']) $report['issues'][] = 'Unsupported reading time is emitted';
$report['paginationFixtureLeftovers'] = (int) $GLOBALS['wpdb']->get_var("SELECT COUNT(*) FROM {$GLOBALS['wpdb']->posts} WHERE post_title LIKE 'Wild Lemon pagination fixture %'");
if ($report['paginationFixtureLeftovers']) $report['issues'][] = 'Pagination fixtures left behind';
$debug = WP_CONTENT_DIR . '/debug.log';
$report['debugLog'] = array('exists' => is_file($debug), 'bytes' => is_file($debug) ? filesize($debug) : 0);
$report['sourceSha256']['style.css'] = hash_file('sha256', $root . '/style.css');
$report['sourceSha256']['theme.json'] = hash_file('sha256', $root . '/theme.json');
$report['passed'] = !$report['issues'];
echo wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
if (!$report['passed']) WP_CLI::error('Minimum backend checks failed; see JSON issues.');
