# Official fixture stress test — 30 September 2026

The reported archive problem reproduced with the official **Edge Case: Many Categories** post: 63 categories squeezed its title to **21px** at a 960px viewport. After the fix, the title has **726px**, with categories beneath it. All category links remain available. [Before](archive-before.png), [after](archive-after.png), [four-category reproduction](archive-720.png), [mobile](archive-390.png).

The test also found and fixed:

- Narrow archive patterns used the viewport breakpoint instead of their actual container width. The layout now reflows inside columns too, and metadata retains the interface font inside article content.
- Classic image captions with inline widths overflowed mobile pages by up to 384px. Their width now respects both the reading column and the available space.
- Core Audio's 300px minimum width exceeded the 272px content area on a 320px phone. The player now fits its container.
- Long, unbroken previous/next titles overflowed their containers. They now wrap.
- Untitled posts had no dependable permalink. Every theme Query Loop now links its date, using the native Post Date block.
- The Page template omitted comments. It now uses the existing core Comments pattern; closed pages without comments remain empty.
- Nested comment text shrank to 72px on a 320px screen. Correctly targeting core's nested ordered lists, allowing the text column to grow, and using smaller mobile avatars gives the deepest replies 180px.
- WordPress 6.7 displayed a Previous link on empty/out-of-range queries. A filter scoped to the theme's `wl-pagination` blocks suppresses unusable pagination while keeping core's queries and links.
- Classic `<!--nextpage-->` posts were split but had no page links. WordPress's `wp_link_pages()` now supplies the missing links. Core Page Break blocks retain their own output without duplicates.
- Nested mobile navigation links could be only 20px tall. All levels now have at least 44px link height and the long menu scrolls.

The published homepage structure, palette, typography, and six editorial patterns are preserved. The theme still uses core blocks and has no plugin dependency. The existing demo on port 8088 was not replaced with test data.

## Official inputs and environments

Following the [WordPress theme testing guide](https://developer.wordpress.org/themes/releasing-your-theme/testing/), this run used the current export from [WordPress/theme-test-data](https://github.com/WordPress/theme-test-data), pinned to commit `b47acf980696897936265182cb684dca648476c7`. [Source and SHA-256](source.json).

The **full import**, including attachments, succeeded: 58 posts, 21 pages, 37 attachments, 70 menu items. There are 77 published post/page fixtures; the draft and scheduled post remain unpublished. All 37 attachment files exist and are nonempty. [Media inventory](media.json), [content inventory](inventory.json).

- Main QA: **WordPress 7.1.2 / PHP 8.3.35**, `http://localhost:8089`, Docker project `wild-lemon-qa`.
- Minimum requirements: **WordPress 6.7.2 / PHP 7.4.33**, `http://localhost:8090`, project `wild-lemon-compat`. An earlier pass also exercised PHP 8.1.32.
- WordPress Importer 0.9.6 and Theme Check 20260901; neither is a theme dependency.
- Five posts per archive page, five top-level comments per comments page, threading depth five, pretty permalinks, `WP_DEBUG` and `WP_DEBUG_LOG`. No theme-origin PHP warnings or errors were found during the browser runs. A direct PHP invocation of the WP-CLI-only regression harness failed with `WP_CLI` unavailable; rerunning it through WP-CLI under PHP 7.4 passed all 32 cases. That harness invocation error remains in the compatibility debug log.
- Both installations use independent database/uploads volumes. Only their QA headers use a database override to select the official 18-link nested menu.

## Verification

| Check | Result | Evidence |
| --- | --- | --- |
| All 77 published fixtures plus 10 template/query/pattern routes; 320, 390, 768, 1024, 1440px | **435/435**: no horizontal page overflow, compressed query titles, or broken loaded local images | [Final layout](layout-after.json), [first pass](layout-first-pass.json) |
| Same 87 routes on WP 6.7.2 / PHP 7.4.33; 320, 768, 1440px | **261/261** | [Minimum-version layout](layout-wp67.json) |
| Archive: 1, 2, 3, 4, 63 categories; narrow containers down to 240px; RTL direction and increased text spacing | **84/84**: readable titles, no overlap, every category retained | [Regression](archive-regression.json) |
| Firefox and WebKit: 9 routes × 5 widths × 2 engines | **90/90** layouts; mobile menu/search keyboard checks also pass | [Browsers and versions](browsers.json) |
| Query pagination: 8 post counts × 4 page numbers, on both WordPress versions | **64/64**; exact post IDs, empty messages, native previous/next, unrelated queries unchanged | [Current](pagination.json), [minimum](pagination-wp67.json) |
| Actual UI flows, both WordPress versions | Menu focus containment/Escape, nested menu scrolling, centered controls, query next/previous, empty page, untitled date link, page comments, reply/cancel, older/newer comments, password unlock, content pagination | [Current](interactions.json), [minimum](interactions-wp67.json) |
| Search: 8 widths, long site title, RTL direction, Enter/button submission, reduced motion | No layout shift; native interactions pass with the full official menu | [Search](search.json) |
| Classic and core-block page breaks | Five page views; correct content and exactly one set of links per view | [Post page links](post-page-links.json) |
| Axe: 13 routes × 2 widths, open mobile menu, open search at 2 widths | 29 checks; fixture-only findings below after transitions settle | [Accessibility](accessibility.json) |
| Editor parser | 21 patterns, 8 templates, 3 parts; no invalid blocks | [Patterns](../pattern-validation.json), [templates](../template-validation.json) |
| Production ZIP | PHP/JSON validation, PHP 7.4 lint, Theme Check: **0 errors / 0 warnings**, 2 informational messages | [Theme Check](theme-check.json) |

Additional visual checks: [nested comments](comments-mobile.png), [mobile menu](menu-mobile.png), [classic images](classic-images-mobile.png).

The first layout pass ran while fixes were being developed; it records 18 failing combinations, not a frozen original baseline. The archive-before capture was taken before its fix. Archive screenshots with “Category Hierarchy” reproduce the supplied labels in the browser only; the live test page keeps the original official post.

## Findings that remain outside theme fixes

- The official formatting and comments fixtures contain an empty table header (`empty-table-header`, minor). Authored table content was preserved.
- The intentionally untitled fixture has no H1 (`page-has-heading-one`, moderate/best practice). Its native date permalink is now reachable. The theme does not invent a visible article title.
- An earlier audit reproduced a malformed automatic Page List menu in WordPress 7.1.2 with Twenty Twenty-Five as well as Wild Lemon. This run's official converted menu passes. The earlier core finding is still recorded in [the previous audit](../README.md); core files were not patched.
- Firefox and Playwright WebKit are automated desktop engine checks. Physical iOS/Safari, screen-reader use, complete translated-site testing, and existing-site template customizations still need release review. Direction-only RTL tests do not substitute for a translated site.

The fixtures and Theme Check are official WordPress tools. The browser assertions are project tests, not WordPress certification or a guarantee of directory approval. The [theme review requirements](https://make.wordpress.org/themes/handbook/review/required/) still apply.

## Repeat on an isolated installation

`docs/compose.yml` accepts `WILD_LEMON_PORT` and `WILD_LEMON_WORDPRESS_IMAGE`. Always pass the same port/image when running a project's CLI service so Compose does not recreate it using defaults.

```sh
WILD_LEMON_PORT=8089 docker compose -p wild-lemon-qa -f docs/compose.yml up -d
# On a fresh instance, install WordPress with wp core install and activate wild-lemon.
WILD_LEMON_PORT=8089 docker compose -p wild-lemon-qa -f docs/compose.yml run --rm cli wp plugin install wordpress-importer theme-check --activate
curl -fL https://raw.githubusercontent.com/WordPress/theme-test-data/b47acf980696897936265182cb684dca648476c7/themeunittestdata.wordpress.xml -o /tmp/wild-lemon-theme-unit.xml
WILD_LEMON_PORT=8089 docker compose -p wild-lemon-qa -f docs/compose.yml run --rm -v /tmp/wild-lemon-theme-unit.xml:/tmp/theme-unit.xml:ro cli wp import /tmp/theme-unit.xml --authors=create
WILD_LEMON_PORT=8089 docker compose -p wild-lemon-qa -f docs/compose.yml run --rm cli wp eval-file wp-content/themes/wild-lemon/docs/qa/setup-official-fixtures.php
```

Set the options listed above before running the interaction checks. The helper only accepts the two isolated localhost QA URLs; it creates test pages and selects the imported All Pages menu. The fixture import itself should run on a fresh database.

With `agent-browser` on PATH (or its executable in `AGENT_BROWSER`):

```sh
python3 docs/qa/check-official-layout.py docs/qa/official-2026-09-30/inventory.json /tmp/layout.json
python3 docs/qa/check-archive-layout.py
python3 docs/qa/check-official-interactions.py
python3 docs/qa/check-official-accessibility.py
QA_URL=http://localhost:8089 QA_SEARCH_TERM=markup QA_OUTPUT=/tmp/search.json python3 docs/qa/check-search.py
```

`QA_URL` changes the target for these scripts. `QA_WIDTHS` and `QA_SESSION` configure the layout matrix. The saved inventory supplies fixture paths, not IDs; the helper adds the regression routes. On a freshly re-imported site with different slugs, regenerate the inventory first.

For the extra browser engines, install Python Playwright in a temporary virtual environment, run `playwright install firefox webkit`, then run `docs/qa/check-official-browsers.py` using that environment's Python. The existing build, Theme Check, block-parser, and pagination commands are in [development.md](../../development.md) and [the QA guide](../README.md).

The PHP 7.4 compatibility container reuses the isolated WordPress 6.7.2 volume after switching its Apache image to `wordpress:php7.4-apache`; it does not replace the installed core files. The final version pair was verified by bootstrapping WordPress in that container. Its containers were stopped after verification; its database and uploads volumes are retained. The normal demo (8088) and current-version QA site (8089) remain running.
