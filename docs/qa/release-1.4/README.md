# Wild Lemon 1.4 — release checks

Completed **2 October 2026** using the retained official WordPress Theme Unit Test content and persistent polish fixtures on the isolated QA site, WordPress **7.1.2 / PHP 8.3.35**. The normal demo remains separate.

## Release changes

- Keep nested navigation and the mobile menu above search and page content.
- Center wrapped category pills and balance their label padding.
- Keep author archive text alongside its avatar on larger screens, stack on phones, and wrap long author names/headings.
- Contain native category selectors with long options.
- Retain native empty-query messaging and untitled-post date links on the 404 page.
- Strengthen the native gallery caption gradient while retaining its typography, padding, placement and box geometry.

Version headers, `VERSION`, changelog and accessibility statement are updated for **1.4**. The existing theme composition and core blocks are preserved.

## Fresh verification

| Check | Result | Evidence |
| --- | --- | --- |
| Official Theme Check 20260901 on the clean package | **0 errors, 0 warnings, 2 informational messages** | [Theme Check](theme-check.json) |
| Axe 4.12.1 | **48 scans**: 22 routes at 320/1440px, mobile menu, nested desktop submenu and open search at both widths. No theme-owned or unexpected violations. | [Final results](accessibility.json) |
| Fixture content findings | Six findings across both widths: empty table headers in two official fixtures and a missing H1 in the deliberately untitled post. Exact findings are retained; unexpected findings fail the release axe helper. | [Content findings](accessibility-content-findings.json) |
| Keyboard, focus and contrast | Two skip-link checks, two native Details checks, and 29 arrow/form-boundary/footer-focus measurements pass. | [Focused review](accessibility-review.json) |
| Reflow and text spacing | 24 cases at 320/640 CSS pixels pass with no page overflow or detected clipped text. These widths represent the available space at desktop zoom; browser-toolbar zoom was not automated. | [Focused review](accessibility-review.json) |
| Author accessibility | 12 post/archive cases at 320/640/1440px, normal and increased text spacing; minimum measured text contrast **4.71:1**, no page/text overflow. | [Author review](author-accessibility.json) |
| Gallery captions | Eight captions across two posts and two widths. Corrected black gradient stops 0.85/0.65 guarantee white-text contrast of at least **6.98:1**, even over a white image. Text is visible and page overflow is zero. | [Caption review](gallery-caption-review.json) |
| Native interactions | Mobile menu targets/focus/Escape at 320/390/768px; actual next/previous query navigation; out-of-range empty loop; untitled permalink; page/threaded comments and reply/cancel; password form and multipage post navigation pass. | [Interactions](interactions.json) |
| Query boundaries | **32 cases** pass; unrelated queries unchanged; temporary fixtures removed. | [Pagination](pagination.json) |
| Backend | **22** production PHP files lint on host and actual QA runtime; theme JSON valid; **32** native pattern/template/part server parse/serialize checks and registration checks pass; author/single/404 structure and empty/untitled 404 rendering pass. No PHP debug log or temporary pagination records remain. | [Backend summary](backend-summary.json), [runtime detail](backend-runtime.json) |
| Build | All **46** package files match source byte for byte. ZIP integrity, required files, exclusions and all version headers pass. Installable and versioned ZIPs are identical. | [Package manifest](package.json) |

The first caption review found insufficient contrast over actual light photo areas. The [failing baseline](gallery-caption-first-pass.json) and [initial axe results](accessibility-before-caption-fix.json) are preserved. The complete axe matrix was rerun after the correction. Axe still marks gradient backgrounds and arrow glyphs for manual contrast checking; the focused measurements above resolve those cases. The native mobile caption's small scroll-height excess is bottom padding, with every text rectangle visible.

Screenshots: [caption before](gallery-caption-before-320.png), [caption after](gallery-caption-after-320.png), [desktop after](gallery-caption-after-1440.png).

Rendered accessibility checks ran after the final CSS corrections. Only release metadata, changelog and the statement then changed; the clean **1.4** package was rebuilt and its backend/Theme Check gates rerun.

## Earlier coverage and limits

The [polish pass](../polish-2026-10-02/README.md) records 500 layouts and minimum-version verification. The [author biography pass](../author-bio-2026-10-02/README.md) records 56 biography/name layouts. These are earlier snapshots, not reruns of every assertion against this release. Native server parsing does not claim fresh editor JavaScript validation.

These developer checks follow the [WordPress theme testing guide](https://developer.wordpress.org/themes/releasing-your-theme/testing/) and [accessibility-ready requirements](https://make.wordpress.org/themes/handbook/review/accessibility/required/). They are not directory approval or a full accessibility conformance audit. Fresh screen-reader sessions, physical-device testing, complete translations and Firefox/WebKit testing are outside this release pass; earlier browser evidence remains separately documented. The accessibility statement keeps `Accessibility audit completed: No`.

## Package and GitHub publication

Local upload-ready files: `build/wild-lemon.zip` and `build/wild-lemon-1.4.zip`. Local package SHA-256:

```text
b0148fa9958e46052762c401586d3d5afc6f5d5c843b02e5e836eca1434b39d6
```

The release commit and annotated `v1.4` tag use the existing tag-triggered workflow to publish [Wild Lemon 1.4](https://github.com/sumanbasuli/wild-lemon/releases/tag/v1.4). Its ZIP/checksum are built from the tagged commit; ZIP timestamps can differ from this local artifact. Development files and QA data are excluded from the installable theme. WordPress.org upload remains a manual step.

The pending workflow and development-document drafts from the earlier task are excluded from this release commit and retained locally.
