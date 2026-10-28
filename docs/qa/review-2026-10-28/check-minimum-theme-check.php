<?php
if (!defined('WP_CLI') || !WP_CLI || home_url() !== 'http://localhost:8090') throw new RuntimeException('Minimum QA site only.');
global $themechecks, $checkcount;
require_once WP_PLUGIN_DIR . '/theme-check/checkbase.php';
$theme = wp_get_theme('wild-lemon', '/tmp/wildlemon-oct28-theme-check');
if (!$theme->exists()) throw new RuntimeException('Clean production copy missing.');
$pass = (bool) run_themechecks_against_theme($theme, 'wild-lemon');
$messages = array();
foreach ($themechecks as $check) {
    foreach ((array) $check->getError() as $error) {
        if (!$error) continue;
        $value = html_entity_decode(wp_strip_all_tags($error), ENT_QUOTES, 'UTF-8');
        preg_match('/\b(REQUIRED|WARNING|RECOMMENDED|INFO)\b/', $value, $type);
        $messages[] = array('type' => $type[1] ?? 'UNKNOWN', 'value' => $value);
    }
}
$blocking = array_values(array_filter($messages, function ($m) { return 'INFO' !== $m['type']; }));
$manifest = array();
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($theme->get_stylesheet_directory(), FilesystemIterator::SKIP_DOTS)) as $file) {
    if (!$file->isFile()) continue;
    $name = substr($file->getPathname(), strlen($theme->get_stylesheet_directory()) + 1);
    $manifest[$name] = hash_file('sha256', $file->getPathname());
}
ksort($manifest);
$report = array('url' => home_url(), 'wordpress' => $GLOBALS['wp_version'], 'php' => PHP_VERSION, 'themeVersion' => $theme->get('Version'), 'pluginVersion' => '20260901', 'scope' => 'Isolated clean production-file copy with build exclusions; active theme unchanged', 'passed' => $pass && !$blocking, 'checks' => $checkcount, 'messages' => $messages, 'blocking' => $blocking, 'files' => $manifest);
echo wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
if (!$report['passed']) WP_CLI::error('Minimum Theme Check failed.');
