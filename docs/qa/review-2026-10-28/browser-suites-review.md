# Browser regression suites — 28 October 2026

Fresh official-content sites: WordPress **7.1.3 / PHP 8.3.36** at `localhost:8089`, and WordPress **6.7.10 / PHP 7.4.33** at `localhost:8090`. Browser: Chromium through agent-browser. This pass exercises existing project assertions on official WordPress fixtures; it is not an accessibility conformance certification.

## Final results

| Suite | Current WordPress | Minimum branch |
| --- | --- | --- |
| Native interactions | Pass | Independent flows pass; conditional native Escape focus limitation below |
| Header Search | 15 cases pass after the header end-alignment correction | 15 cases pass after the same correction |
| Axe | 48 scans; zero unexpected automated violations | 48 scans; zero unexpected automated violations; repeated classic-caption IDs require manual-review disclosure |
| Focused accessibility | 2 skip-link, 2 Details, 29 contrast and 24 reflow/text-spacing checks pass | Same checks pass |
| Gallery caption contrast | Four caption/width combinations: 6.98:1 rounded lower bound | Four caption/width combinations: 6.98:1 rounded lower bound |

The axe/focused-review reports were refreshed after the final desktop-header CSS correction. Final evidence is in `current/` and `minimum/`: `interactions.json`, `search.json`, `accessibility.json`, and `accessibility-review.json`. The final Search results are also retained in the header-fix pass's `search-after.json`; the original 901px clipping failure is preserved in the earlier run logs.

Native interactions cover 44px controls, mobile menu scrolling/focus containment, actual query next/previous navigation, out-of-range empty messaging, untitled-post date permalinks, existing page comments, threaded reply/cancel and comment paging, password unlock, and classic multipage content. No new comments were submitted.

Each axe matrix retains six expected authored-fixture findings: empty table headers on the HTML-formatting and paged-comments fixtures at two widths, and the intentionally untitled post's missing H1 at both widths. Exact records are in each `accessibility-content-findings.json`. Those content issues were preserved. Arrow-glyph contrast incompletes are resolved by the focused measurements; white gallery-caption text over the actual black gradient has a computed lower bound of **6.977:1** even over a white image (`gallery-caption-contrast.json`).

## Minimum-core limitations retained explicitly

### Escape after reverse tabbing in native Navigation

The original interaction helper fails its focus-restoration assertion on WordPress 6.7.10. Normal Escape from the initial menu link restores focus to **Open menu**, but reverse Tab to **Close menu** or the last nested **Page B** link followed by Escape closes the overlay and leaves focus on **BODY**. The failure occurs at 320, 390 and 768 pixels; the current WordPress version passes the same sequence.

This repeats with Wild Lemon's stylesheet disabled and with its mobile submenu styles reset. A further browser-only isolation retains the unchanged server-rendered Navigation subtree, removes the theme header wrapper and theme/global styles, waits until focus enters the native dialog, and confirms that normal Escape passes while reverse-Tab Escape still loses focus. No site theme or database setting was switched. See `minimum/menu-escape-isolation.json` and `minimum/menu-escape-native-settled.json`; the original failing log is `minimum/interactions-run.txt`.

The continuation run records `escapeRestoresFocus: false` and `coreMenuEscapeLimitation` explicitly in `minimum/interactions.json`, then checks all remaining native flows. It exits nonzero because that limitation remains. It is not an all-green native-interaction result, and no theme JavaScript or core patch was added for this independent legacy-core behavior.

### Repeated classic-caption IDs

The official image-alignment fixture contains two classic caption shortcodes using `attachment_907` with different alignment values. WordPress 6.7.10 emits two `figcaption` elements with ID `caption-attachment-907`; axe marks this as a critical `duplicate-id-aria` manual-review finding at both widths. Current WordPress generates no duplicate caption IDs for the same fixture. Actual DOM counts and captions are retained in `caption-id-review.json`; each site's `accessibility-additional-manual-findings.json` preserves its exact axe records. Authored fixture content was not rewritten.

All browsers owned by this suite pass were closed. `browser-suites-summary.json` records the final source CSS hash and aggregate results; `browser-suite-runs.json` retains the initial failures and `browser-suite-final-runs.json` records the final accessibility refresh.

The subsequent archive-header pattern correction changes only paragraph block metadata (`align: "wide"` to `className: "alignwide"`), retaining the same published paragraph HTML and CSS. Browser suites were not repeated for that invisible metadata correction; actual editor parsing/save/reload checks are recorded separately by the editor pass.
