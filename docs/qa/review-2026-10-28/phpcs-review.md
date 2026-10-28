# Production PHP standards review — 28 October 2026

## Scope and tools

The scan targets exactly `functions.php` and the 21 PHP files in `patterns/`. Development, QA, documentation, build output and vendor code are excluded. The focused cleanup changes only numeric pagination input handling in `functions.php` and one missing blank line in each of six pattern PHP headers. Theme templates and CSS are outside this subtask.

Temporary Composer projects are in `/private/tmp/wildlemon-phpcs-20261028/{modern,review}`. Composer scripts/plugins were disabled during installation. PHP_CodeSniffer 3.13.6 runs under host PHP 8.5.3; runtime dependency deprecation notices are suppressed for the scanner only. The theme PHP is analyzed statically, rather than executed by these scans. Actual frontend and minimum-runtime tests remain separate.

| Standard | Dependency versions | Raw result |
| --- | --- | --- |
| WordPress | WPCS 3.4.1 | 0 errors, 0 warnings |
| WPThemeReview | 0.2.1, its required WPCS 2.3.0 | 5 legacy findings, 0 warnings |
| PHPCompatibilityWP | 2.1.8, PHPCompatibility 9.3.5, `testVersion=7.4-` | 0 errors, 0 warnings |

The published WPThemeReview release depends on the older WPCS major version, so it is installed separately from current WPCS. The compatibility package tests the declared PHP floor using the checks available in its engine; it does not establish coverage of every later PHP release.

## Triage and focused corrections

The initial unfiltered scan is retained in `phpcs-*-before.json/.txt`. Final unfiltered scans use the base filenames.

### Current WPCS — corrected

- The initial `functions.php` public pagination read was flagged for unslashing/sanitization and missing nonce verification. It now guards scalar input, unslashes it, applies explicit `intval()` numeric sanitation and retains the `max(1, ...)` lower bound. A nonce exemption is limited to that public read-only query-parameter read; ordinary archive links do not change site data. Using numeric sanitation preserves the previous behavior of malformed HTML instead of stripping markup into a different page number.
- Six public pattern headers now have the required blank line after the file comment: `editorial-introduction.php`, `featured-quotation.php`, `photo-essay.php`, `seasonal-table.php`, `slow-weekend.php`, and `two-column-notes.php`. These are formatting findings rather than rendered UX failures.

### Legacy WPThemeReview

- Three plugin-territory errors flag `register_block_style()` and `register_block_pattern_category()`. These are documented theme features in the current [Block Style Variations](https://developer.wordpress.org/themes/features/block-style-variations/) and [Registering Patterns](https://developer.wordpress.org/themes/patterns/registering-patterns/) handbooks. Removing them to satisfy this old rule would remove legitimate theme functionality.
- Two reserved-template-prefix errors flag `patterns/author-bio.php` and `patterns/author-header.php`. These files are auto-registered block patterns with pattern metadata, not classic root-level author templates. The [patterns directory documentation](https://developer.wordpress.org/themes/patterns/registering-patterns/) describes that registration mechanism.
- Its initial two input warnings were resolved by the same focused pagination correction.

Raw results are retained without exclusions in `phpcs-wordpress.json/.txt`, `phpcs-wpthemereview.json/.txt`, and `phpcs-phpcompatibility.json/.txt`. `phpcs-summary.json` records exact arguments, dependency versions and production file hashes. Any post-review source change requires a rerun to make a current pass/fail statement.

## Input behavior verification

`check-pagination-inputs.php` invokes the actual theme callback, captures the page argument received by the native query builder, and short-circuits its post queries. On WordPress 7.1.3 / PHP 8.3.36, all **35 cases** preserve the old signed-integer/lower-bound results using WordPress-slashed request globals. Cases include absence/null, negative values, arrays, HTML, numeric prefixes, decimals/scientific notation, slashes/quotes/NUL bytes, and oversized numeric input. No posts, options or templates are changed. Results: `pagination-inputs.json`. The existing 32-case full query/pagination harness remains a separate gate.

The same **35 input cases** also pass on WordPress **6.7.10 / PHP 7.4.33**; see `pagination-inputs-minimum.json`. Minimum-runtime PHP lint and the full 32-case pagination harness are recorded separately by the compatibility pass.

The final standards rerun also includes the root editor pass's nonvisual `patterns/archive-header.php` paragraph block-metadata correction. Its current file hash is recorded in `phpcs-summary.json`; scanner outcomes remain unchanged.
