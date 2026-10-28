# Minimum compatibility and additional browsers — 28 October 2026

## Fresh compatibility site

The isolated Compose project `wild-lemon-oct28-min` serves <http://localhost:8090>. It runs official WordPress **6.7.10** and PHP **7.4.33**. Official WP-CLI **2.12.0** executes inside that PHP 7.4 container. The current-version site on port 8089 is separate.

Debug logging is enabled, display is disabled, and theme development mode is active. Native settings use five posts and five comments per page, threaded comments to depth five, and pretty permalinks. [Environment](minimum-environment.json).

The pinned official Theme Unit Test import processed all **186** records without errors or warnings: 58 posts, 21 pages, 37 attachments and 70 menu items. All **37** attachment original files are present and nonempty. Official, polish and October reproduction fixture helpers then produced **102** published test routes. [Source](minimum-source.json), [import summary](minimum-import-summary.json), [inventory](minimum-inventory.json), [raw import](minimum-import.log).

Minimum WordPress's Content block supports padding but not margins. Its original Sample Page wrapper consequently emitted no spacing style, reproducing the report. [Before markup](minimum-spacing-before.json), [HTML capture](minimum-spacing-before.html).

## Minimum runtime gates

| Check | Result | Evidence |
| --- | --- | --- |
| Production PHP lint | 22 files pass in actual PHP 7.4 | [Backend](minimum-backend.json) |
| Native block structure | 32 template, part and registered-pattern server parse/serialize round-trips pass; referenced blocks/patterns registered | [Backend](minimum-backend.json) |
| Content spacing | Both templates use native editable `padding.top` with spacing preset 60 and no unsupported top margin | [Backend](minimum-backend.json) |
| Older-core reading time | Optional pattern remains empty when Time to Read is unavailable | [Backend](minimum-backend.json) |
| Pagination boundaries | 32 cases pass; unrelated queries unchanged and all temporary records removed | [Pagination](minimum-pagination.json) |
| Pagination input stress | 35 malformed, scalar, array and boundary inputs pass against the actual callback | [Input cases](pagination-inputs-minimum.json) |
| Official Theme Check 20260901 | Passed: zero required issues, warnings or recommendations; two informational messages. All 46 tested clean production-copy files match final source | [Theme Check](minimum-theme-check.json) |
| PHP debug log after tests | Zero bytes | [Final log status](minimum-debug-final.json) |

The clean Theme Check copy is isolated under the container's `/tmp`; it does not replace the active theme or use the shared build directory. Native server round-trips do not claim editor JavaScript validation.

## Firefox and WebKit

Tests used matching **Playwright 1.64.1**, **Firefox 157.0** and **WebKit 27.2**. Existing cached engines did not match a live runtime, so exact engines were installed under `/tmp/wildlemon-oct28/browsers`. Other projects' browser caches were preserved.

- The existing official-fixture suite passed **90 layouts** on the current site at 320, 390, 768, 1024 and 1440 pixels, plus **four** native menu/Search interaction scenarios. Maximum horizontal page overflow was zero. [Results](browser-current/browsers.json), [runner](browser-current/runner.log).
- Focused reported cases passed **36 layouts** across current and minimum sites at 320, 768 and 1440 pixels, in both engines. First content gaps were **24 / 38.39 / 56px** respectively, matching native content padding. No page overflow or page JavaScript errors were observed. [Results](browser-focused.json).
- **Twelve** focused keyboard scenarios passed: current links remain visibly marked, nested current links have a background, Enter navigates Test 2 to Test 3 and updates the selected link, and Escape restores menu/submenu focus. [Results](browser-focused.json).

The focused browser run already included all three final header end-alignment declarations. A later explanatory comment is its only difference from the final stylesheet. Removing that comment reproduces the tested stylesheet hash byte for byte; the proof is retained in `finalStylesheetReview` in the focused report. A comment-only browser rerun was unnecessary.

These are automated engine checks, not physical-device or screen-reader testing. WebKit engine results do not claim a separate Safari application session.

## Tool-specific observations

- Core checksum verification passed. WP-CLI additionally reports the official Docker image's retained `wp-config-docker.php` as an extra non-core file.
- An initial temporary Theme Check wrapper omitted explicit global result bindings in WP-CLI's eval-file scope. Its three wrapper-only notices were [retained](minimum-harness-initial-debug.log); the wrapper was corrected, only those validated entries were reset, and corrected/final checks left the debug log empty. No production code change was needed for this tool issue.

The [compact summary](minimum-and-browsers-summary.json) retains final source fingerprints. Reproduction helpers are [minimum backend](check-minimum-backend.php), [minimum Theme Check](check-minimum-theme-check.php), and [focused browser checks](check-review-browsers.py).
