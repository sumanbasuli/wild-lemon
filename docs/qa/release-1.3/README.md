# Wild Lemon 1.3 — final release checks

Completed 30 September 2026. The isolated WordPress site on port 8089 served the
**clean build directory, mounted read-only**, during these checks. Its official
Theme Unit Test content was retained. The development source mount was restored
afterward; the demo on port 8088 was unchanged.

## Final changes

- Search and comment field boundaries now use the existing `muted-2` color. The
  comment boundary improved from approximately 1.39:1 to 5.02:1 against the theme
  background. The layout, palette tokens, and published screenshot are preserved.
- Added the required root `accessibility.txt`, including testing scope, known
  limitations, the screen-reader text class, and help/reporting contacts.
- The release also includes the three new patterns and the archive, media,
  comments, and pagination fixes documented in the [official fixture audit](../official-2026-09-30/README.md).

## Results on the clean package

| Check | Result | Evidence |
| --- | --- | --- |
| WordPress Theme Check 20260901 | 0 errors, 0 warnings, 2 informational messages | [Theme Check](theme-check.json) |
| Axe on 13 routes at 320/1440px, open mobile menu, and open search | 29 scans; no theme-owned violations in these cases | [Complete results, including incomplete checks](accessibility.json) |
| Keyboard skip link and Details | Visible skip link; subsequent Tab continues in main; Details toggles with Space and Enter, at both widths | [Focused review](accessibility-review.json) |
| Form boundaries, arrow glyphs, footer keyboard focus | 29 measurements pass; form boundaries 4.54–5.29:1, arrows at least 5.02:1, footer focus 15.74:1 | [Focused review](accessibility-review.json) |
| Reflow and text spacing | 24 checks across six routes at 320/640 CSS pixels, with normal and increased text spacing; no page overflow or detected clipped text | [Focused review](accessibility-review.json) |
| Firefox and WebKit | 90 layouts and 4 menu/search keyboard flows pass | [Browser versions and results](browsers.json) |
| Native UI flows | Menu scrolling/focus/Escape; query next/previous; empty queries; untitled permalinks; page/threaded comments; reply/cancel; password form; multipage posts pass | [Interactions](interactions.json) |
| Search | Eight widths, long title, direction-only RTL, Enter/button submission, and reduced motion; no layout shift | [Search](search.json) |
| Query boundaries | 32 cases pass; temporary fixtures removed; unrelated queries unchanged | [Pagination](pagination.json) |
| Build | PHP lint, valid theme.json, valid ZIP; all 46 packaged files byte-identical to source; development files excluded | [SHA-256 and file manifest](package.json) |
| Runtime | WordPress 7.1.2 / PHP 8.3.35 / theme 1.3; no entries in the QA PHP debug log | Verified from the running container |

The earlier full fixture run additionally covers 435 current-version layouts,
261 layouts on **WordPress 6.7.2 / PHP 7.4.33**, 84 archive regressions, and editor
validation of all 21 patterns, 8 templates, and 3 parts. No PHP or block markup
changed after that compatibility/parser run.

Visual checks: [comment form on mobile](comment-form-mobile.png), [open search on mobile](search-mobile.png).

## Interpretation and remaining manual review

The fixture-only axe findings are empty table headings in the formatting/comments
test content and a missing H1 in the intentionally untitled post. The test content
was preserved. Axe's incomplete results concern arrow glyphs; their actual computed
foreground/background contrast was checked separately and passes.

The reflow checks use the CSS-pixel space of a 1280px desktop at 200%/400% zoom;
they do not automate browser-toolbar zoom. Screen-reader sessions, physical iOS
devices, Firefox text-only zoom, a fully translated site, and a comprehensive
WCAG conformance audit remain outside this developer run. The existing core
automatic Page List issue and its comparison against Twenty Twenty-Five are
recorded in the earlier audit; the configured official nested menu passes here.

Theme Check is the official automated checker. These browser checks follow the
[WordPress theme testing guide](https://developer.wordpress.org/themes/releasing-your-theme/testing/)
and [required accessibility review criteria](https://make.wordpress.org/themes/handbook/review/accessibility/required/).
The new statement follows the [accessibility.txt requirement](https://wpaccessibility.org/docs/topics/documenting-accessibility/accessibility-txt/),
effective June 30, 2026. They do not constitute WordPress directory approval or an
official accessibility-ready review. `Accessibility audit completed: No` in the
statement makes the scope explicit.

## Artifact

`build/wild-lemon-1.3.zip` and `build/wild-lemon.zip` contain the same upload-ready
theme. SHA-256:

`3de337a1cf03255755a36d67b18901e75220a6fda4baf07b795a87d0982da779`

Build artifacts remain local and ignored by Git. The package manifest and all
source files are versioned. No remote push or WordPress submission was made.
