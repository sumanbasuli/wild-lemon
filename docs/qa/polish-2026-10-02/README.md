# Theme polish QA — 2 October 2026

This run refines Wild Lemon 1.3 while preserving its published structure, typography, colors, and editorial patterns. It uses the retained official WordPress Theme Unit Test import on the isolated QA installation, supplemented by **12 persistent fixtures** owned by `polish-2026-10-02`. The normal demo remains separate from the QA data.

The [official import baseline](../official-2026-09-30/README.md) records the pinned input, attachments, earlier fixes, and broader browser/accessibility checks. This report records the additional checks performed against the current source; it does not treat those earlier checks as a fresh run.

The subsequent [author biography follow-up](../author-bio-2026-10-02/README.md) adds the missing profile content, fixes two author layout cases, and records the newer package hash. The evidence and package hash below remain the snapshot of this initial polish pass.

## Fixtures and environments

The [fixture manifest](fixtures.json) and [setup instructions](fixtures.md) cover one, four and seven categories, long category labels, brief/long/unbroken titles, missing featured images, native mixed content, all six editorial patterns in normal and 320px/600px containers, and pages with comments open or closed. These records supplement the official import and remain available for repeat checks. The helper uses ownership metadata and refuses to overwrite records it does not own. The fixture dates avoid displacing the recent homepage stories.

- Current QA: WordPress **7.1.2**, frontend and CLI PHP **8.3.35**, `http://localhost:8089`. [Runtime](backend-runtime.json), [backend summary](backend-summary.json).
- Minimum-version checks: WordPress **6.7.2** and PHP **7.4.33**, `http://localhost:8090`. [Environment](minimum-environment.json), [backend checks](minimum-backend.json).
- Theme Check **20260901**; theme version **1.3**. These QA tools are not theme dependencies.

## Refinements verified

- The header navigation now establishes a stacking level above the search field, allowing child dropdowns and the mobile menu to remain clickable.
- Single-post category pills wrap into centered rows with consistent gaps. Pill labels are centered, with balanced padding for the trailing space introduced by uppercase letter spacing.
- Native select controls respect their content column when a long option would otherwise determine a larger intrinsic width. The first layout pass found **239px of overflow** on the 320px Widgets fixture; the final matrix passes after this correction.
- The 404 recent-posts Query Loop now uses a linked native Post Date, retaining a permalink for untitled posts without featured images. A native Query No Results block displays the existing empty-post message when there are no posts.

Screenshots: [desktop dropdown](dropdown-desktop.jpg), [mobile category pills](category-pills-mobile.jpg).

## Recorded verification

| Check | Result | Evidence |
| --- | --- | --- |
| 100 routes × 320, 390, 768, 1024 and 1440px | **500 layouts** pass the recorded assertions: one main landmark, no horizontal page/pill overflow, no compressed-title findings, no missing alt attributes or broken loaded local images. Category row center error is at most 0.008px. | [Final matrix](layout.json), [first pass](layout-first-pass.json), [content inventory](inventory.json) |
| Desktop nested navigation at 901, 939, 1024 and 1440px | Six child links are in bounds and topmost at each width; a child link reaches its expected page. | [Navigation](navigation.json) |
| Mobile menu at 320, 390, 768 and 900px | Forward/reverse keyboard traversal stays in the menu; Escape closes it and restores the Open menu control. The menu remains above search. | [Navigation](navigation.json) |
| Header search at 320, 390, 901 and 1440px | Opening search preserves header height, focuses the input, hides navigation and causes no overflow. Closing restores the expand control; submission reaches the expected results. | [Navigation](navigation.json) |
| Logged-in mobile control | With the WordPress admin toolbar present, the menu close button is topmost and retains a 44px square control. | [Navigation](navigation.json) |
| Chrome block editor | The iframe canvas works; all six public patterns appear and hidden patterns remain absent. Mixed content has no block recovery warnings. A quotation pattern was actually inserted, saved and reloaded, retaining both quotation instances without recovery warnings. Editor and published body typography match at 19px Newsreader and 32.3px line height. | [Editor](editor.json) |
| Minimum-version layout | **18 layouts**: six routes at 320, 901 and 1440px pass the recorded layout assertions. Desktop dropdowns, mobile Escape/focus return and search also pass. | [Final minimum checks](minimum-layout.json), [initial observation](minimum-layout-first-pass.json) |
| PHP 7.4 backend | All 22 production PHP files lint; theme JSON validates, the 63-category header retains every link, and reading time is absent when its native block is unsupported. The empty/untitled 404 cases pass without changing stored content. Recorded source hashes match. | [Minimum backend](minimum-backend.json) |
| Pagination | **32 cases**: eight post counts × pages 1, 2, 3 and 99 pass. Unrelated queries remain unchanged and temporary pagination fixtures are removed. | [Pagination](pagination.json) |
| 404 Query Loop | Empty and untitled cases pass with the expected native message/date permalink and no pagination. Stored content is unchanged. | [404 checks](404-blocks.json) |
| Current backend and production package | Host/runtime PHP lint and JSON validation pass. Theme Check reports **0 errors, 0 warnings, 2 informational messages**. All **46 package files** match source; required files are present and development files are excluded. ZIP integrity passes. No theme production log entries were recorded. | [Backend summary](backend-summary.json), [Theme Check](theme-check.json), [package](package.json) |

The first minimum-version hit test observed submenu links during their animation before accessibility-tree/render settlement. It recorded three non-topmost links; the settled final check records all six links as topmost and in bounds. The editor's in-app blob frame was blank, so the actual editor insertion/save/reload flow was verified in Chrome. Neither observation is reported as a passing initial test.

The tested archive is `build/wild-lemon-1.3.zip`, SHA-256:

```text
02e7d7f6cbff6b4f76c5d0898f383970eed64705b4b916c5391ab967cf4cc23e
```

## Scope and remaining release review

These results describe the fixtures, routes, assertions and interactions recorded above. This run did **not** repeat axe, Firefox, WebKit, screen-reader, physical device, or complete translated-site testing. The [prior official-fixture audit](../official-2026-09-30/README.md) records its own coverage and content/core findings. Its results remain historical evidence, not verification of every subsequent change.

Theme Check and the official fixture data do not provide WordPress certification or guarantee directory approval. The separate accessibility-ready review and review of existing sites with customized templates remain applicable. Rebuild and recheck the package manifest if production theme files change after this snapshot.
