# Wild Lemon — 28 October 2026 review

## Retained site

The new site is running at **http://localhost:8089/**, with admin at **http://localhost:8089/wp-admin/**. The local login was shared in the chat. WordPress **7.1.3 / PHP 8.3.36** runs in Docker project `wild-lemon-oct28`; database and uploads use dedicated named volumes. Both the web and database containers have `unless-stopped` restart policy. Theme source is mounted from this repository and theme development mode is enabled, so local changes appear after reload.

Useful review routes:

- [Reported post/byline spacing and selected Test 2 menu](http://localhost:8089/wl-review-post-columns/).
- [Reported page title/content spacing](http://localhost:8089/wl-review-page-heading/).
- [Pattern Gallery](http://localhost:8089/pattern-gallery/).
- [Native Site Editor](http://localhost:8089/wp-admin/site-editor.php).

A separate compatibility installation remains on port 8090: WordPress **6.7.10 / PHP 7.4.33**, project `wild-lemon-oct28-min`. The two sites have independent databases and uploads. Each received the full official Theme Unit Test import and reproducible theme fixtures; all 37 imported attachment originals exist and are nonempty. The official source is pinned in [source.json](source.json).

To start the retained main site from this repository if needed:

```sh
WILD_LEMON_PORT=8089 WILD_LEMON_WORDPRESS_IMAGE=wordpress:7.1.3-php8.3-apache docker compose -f docs/compose.yml -p wild-lemon-oct28 up -d db wordpress
```

## Changes

1. **Post byline → content and page title → content:** core Post Content did not support margin attributes in WordPress 6.7. The templates' intended top margin was omitted, leaving a measured zero gap. Both templates now use the supported padding control with the existing Large preset. Current WordPress retains its previous geometry; the minimum version now has the intended **24–56px** responsive separation. Posts/pages, featured images, first headings/paragraphs/Columns, and empty content are covered.
2. **Current navigation item:** exact native current links retain a 2px underline. The selected submenu row also uses the existing Lemon Tint background and dark text. Sibling links stay unselected. Native menu markup, URLs, keyboard handling, hover and focus remain in place.
3. **Search clipping found by stress testing:** a wrapping header could strand Search at the start of a line, causing its expanded input to extend to x −159px at a 901px viewport. Three logical alignment declarations keep wrapped controls at the inline end. The corrected field is fully visible; normal and RTL flows pass.
4. **Archive editor stability:** an archive-label paragraph used `align: "wide"`, which WordPress 6.7 interpreted as text alignment when reserializing, producing a nested paragraph. Its existing `alignwide` class is now specified through `className`. The rendered appearance is unchanged; [equivalence evidence](archive-render-equivalence.json) accounts only for core's sequential generated container IDs.
5. **PHP standards:** six pattern-header blank lines and explicit scalar/unslash/numeric handling for public pagination input resolve current WPCS findings. Signed integer/lower-bound behavior is retained; no nonce is required for a read-only archive link. Thirty-five malformed-input cases pass on each actual PHP runtime.

The existing theme composition, palette, typography and public patterns are preserved. These fixes are included in version 1.6. The evidence below records the pre-release working tree based on version 1.5; final version and package checks are recorded in [the 1.6 release review](../release-1.6/README.md). The existing workflow/development-document drafts are separate from this work.

## Verification

| Check | Result and evidence |
| --- | --- |
| Official Theme Check 20260901 | **0 required issues, warnings or recommendations; 2 INFO**, on both clean production copies. [Current](current-theme-check.json), [minimum](minimum-theme-check.json). |
| All imported routes | **550 current + 330 minimum** layout cases, plus **84 archive cases per site**: no page overflow, compressed query titles, detected geometric offenders, broken loaded local images, missing image alt attributes or main-landmark errors. [Layout report](layout-review.md). |
| Reported cases | **48 spacing cases + 4 current-menu keyboard flows** across both versions. Includes empty/featured content and 320/768/1440px. [Measurements](spacing-menu.json). |
| Search | **46 final cases** across both versions: eight widths, long titles, full/doubled menus, RTL, Enter/button submission, stable expansion, Escape/outside-click and reduced motion. [Search report](search-wrap-review.md). |
| Firefox / WebKit | **90 current layouts + 4 native interaction scenarios**, then **36 reported-case layouts + 12 keyboard scenarios** across both versions. Actual engines Firefox 157.0 / WebKit 27.2. [Report](minimum-and-browsers.md). |
| Axe-core | **96 final scans**, no unexpected automated violations. Exact authored-fixture findings and the minimum-version manual caption finding are retained below. [Browser report](browser-suites-review.md). |
| Focus, spacing, contrast | **114 focused accessibility checks** pass; eight gallery-caption bounds are at least **6.9776:1**. Selected dropdown text has **13.97:1** palette contrast. [Focused report](browser-suites-review.md). |
| Native interactions | Current menu/query/search/comments/password/multipage flows pass. Minimum flows pass except the specifically isolated legacy menu-focus path below. [Current](current/interactions.json), [minimum](minimum/interactions.json). |
| Query boundaries | **32 cases per version**, exact rows/links/empty states, unrelated query unchanged and temporary fixtures removed. [Current](current-pagination.json), [minimum](minimum-pagination.json). |
| Malformed pagination inputs | **35 cases per version**, preserving signed numeric coercion and lower-bound behavior while safely handling nonscalar input. [Current](pagination-inputs.json), [minimum](pagination-inputs-minimum.json). |
| Actual block editors | **32 sources per version**—8 templates, 3 parts, 21 registered patterns—parse and reserialize without invalid blocks. The six public patterns save and reload as native draft content on both versions with identical serialized hashes. [Current final sources](editor-current-sources-final.json), [current draft](editor-current.json), [minimum](editor-minimum.json). |
| Site Editor | Actual Single Posts and Pages canvases open with native `core/post-content` padding60 and no invalid blocks; 44 and 25 expanded native blocks respectively. [Evidence](site-editor-current.json). |
| Runtime/backend | **22 PHP lints + 32 native server block round-trips per runtime**, registered pattern references and optional reading-time fallback pass. [Current](current-backend.json), [minimum](minimum-backend.json). |
| Coding/compatibility standards | **WPCS 3.4.1: 0 errors / 0 warnings**; **PHPCompatibilityWP: 0 / 0**, PHP floor 7.4, all 22 production PHP files. Legacy WPThemeReview findings are documented below. [Standards report](phpcs-review.md). |
| Isolated production build | Build script, PHP/JSON, ZIP integrity and development-file exclusions pass; all **46 files match final source**. Existing release ZIPs were preserved. [Manifest](current-package.json), [build](current-build.txt). |

### Verification sequence

The full 1,048-case route/archive matrix ran after the requested spacing/menu corrections and PHP cleanup. Stress testing then found the wrapped Search issue; the final Search suites, focused cross-engine checks, axe/focus checks and package/backend gates cover its correction. The archive editor correction changes block metadata while preserving rendered HTML, so the affected editor/standards/backend/package gates were refreshed rather than repeating unrelated layout scans. A final explanatory CSS comment is the only byte difference from the focused cross-engine stylesheet; the exact proof is retained in the browser report.

## Recorded core, content and tool findings

- **WordPress 6.7 Navigation:** reverse Tab into particular menu controls followed by Escape closes the modal but can leave focus on `BODY`. This reproduces with the unchanged native Navigation subtree isolated outside the theme header, with theme/global styles removed. Normal Escape works; current WordPress passes the exact failing path. [Isolated proof](minimum/menu-escape-native-settled.json). No core or theme-script workaround was introduced.
- **WordPress 6.7 classic captions:** the official image-alignment fixture repeats two caption shortcodes with attachment907; old core emits the same figcaption ID twice. Current WordPress emits no duplicate caption IDs. [DOM comparison](caption-id-review.json). Authored fixture content is retained.
- **Authored test content:** empty table headers and an intentionally untitled post account for six automated fixture findings per site across scan widths. Some official posts also contain a content H1 in addition to the template title. These are retained and reported rather than silently rewriting the official data.
- **WPThemeReview 0.2.1:** five unfiltered legacy findings flag legitimate core block APIs and mistake `patterns/author-*` files for classic templates. Current Theme Check and WPCS pass; primary-source justification and raw reports are in [the standards review](phpcs-review.md).

Browser checks use automated desktop Chromium, Firefox and WebKit. This run did not include a fresh physical-device or screen-reader session.

Temporary editor round-trip drafts were removed; all usable review posts/pages and media remain. Initial QA-wrapper/staging-permission errors were retained separately, corrected and rerun; they did not originate in production theme files. Final runtime checks and site health are in [final-state.json](final-state.json). The site remains running for use and review.
