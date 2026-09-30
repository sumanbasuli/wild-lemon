# Wild Lemon 1.3 — local QA, 30 September 2026

## Scope

Polish and repair the published theme while preserving its design. The homepage retains the published composition: featured story, latest-post grid, topics strip, and archive rows. The original heading sizes, fonts, palette, rounded imagery, search pill, and theme screenshot are retained. The replacement journal layout was removed following the user's correction. Core No Results blocks were added inside the existing loops.

Changes cover centered hamburger controls, responsive spacing, search behavior, long content, nested comments, wide-block support, and editable patterns styled to match the existing theme. Native pagination was added to the existing archive section so older posts are reachable without replacing the homepage composition.

## Environment and evidence

WordPress 7.1.2, PHP 8.3.35, Docker, agent-browser 0.38.1 with Chromium. `WP_DEBUG` and `WP_DEBUG_LOG` were enabled. No PHP debug log was generated during the checks. The production theme has no plugin dependency.

| Check | Result | Evidence |
| --- | --- | --- |
| Official Theme Unit Test content: 12 routes × 5 widths (320, 390, 768, 1024, 1440px), rerun after restoring the published layout | 60/60 without document overflow; 10 failures before fixes, up to 948px overflow | [Before](layout-before.json), [after](layout-after.json) |
| Controls at 320, 390, 768px | Menu target 44 × 44px; SVG vertical offset 0px; search pill 44px high | [Interaction checks](interaction-checks.json) |
| Full footer | Initial layout passed seven widths from 320–1440px, nested-label wrapping, keyboard focus, and mobile axe checks. Final spacing refinement uses 36px link rows and preserves the heading-to-first-link text gap, verified at 390 and 1440px without overflow. | [Initial checks](footer-checks.json), [spacing refinement](footer-spacing-checks.json), [mobile](footer-390.png), [desktop](footer-1440.png) |
| Long, three-level mobile menu | Scrolls within overlay; focus stays inside; Escape returns focus | [Interaction checks](interaction-checks.json) |
| Header search | Text label retained; smooth expansion with no header or content movement at eight widths from 320–1440px. Native autofocus, Escape, outside-click closing, Enter/button submission, reduced motion, long titles, and RTL checks pass. | [Search checks](search-checks.json) |
| Reading time | Native estimate visible in homepage featured-post and single-post bylines at 320, 390, and 1440px, with no overflow. Compact `min read` label, 14px text, 12px spacing, and a 3px decorative dot match the screenshot's treatment. In-memory content checks produced 1, 2, and 14 minutes for 10, 378, and 2,646 words; unmarked blocks, ranges, and word counts retain core output. Unavailable core block produces no unsupported markup. | [Layout checks](reading-time-checks.json), [format checks](reading-time-format-checks.json) |
| Long unbroken site title, RTL direction, increased text spacing | No document overflow | [Interaction checks](interaction-checks.json) |
| Archive page counts and empty loops | 32 boundary cases: 0, 1, 4, 5, 8, 9, 12, 13 posts × pages 1, 2, 3, 99. Correct row IDs, empty messages, previous/next controls, and no phantom pages. Unrelated queries unchanged. | [Pagination checks](pagination-checks.json) |
| Browser pagination | Clicked Next through pages 2 and 3, then Previous; removed temporary posts and verified no pagination with eight posts and an inline empty message on the obsolete page-two URL | [Browser results](pagination-browser.json) |
| WordPress block parser | 18 patterns, 8 templates, 3 parts: no invalid blocks | [Patterns](pattern-validation.json), [templates/parts](template-validation.json) |
| Theme Check on clean package | No errors or warnings; two informational messages | [Theme Check](theme-check.json) |
| PHP lint / theme.json / Git whitespace | Pass | `./build.sh`, `git diff --check` |
| Axe: mobile pattern page / open mobile menu with configured links | Zero violations | [Patterns](accessibility-demo.json), [menu](accessibility-menu.json) |
| Axe: open header search at 390 and 1440px | Zero violations | [Search accessibility](accessibility-search.json) |
| Axe: six desktop routes with imported stress data and automatic page-list menu | Core/data findings below | [Stress audit](accessibility-checks.json) |

Screenshots: [restored desktop homepage](home-desktop.png), [mobile home](home-mobile.png), [mobile menu](menu-mobile.png), [desktop patterns](patterns-desktop.png), [mobile patterns](patterns-mobile.png), [desktop search](search-open-desktop.png), [mobile search](search-open-mobile.png).

Header search uses the core Search block's button-only mode. CSS reserves the button's position and animates the field within the existing row. Navigation gives way while search is active; on mobile the title also gives way, keeping a usable field width. Core handles input focus, Escape, focus-out closing, and submission. No custom search JavaScript is added.

The published screenshot displays reading time although the original source templates omit it. The featured homepage post and single-post bylines now use the core Time to Read block, available in WordPress 6.9+. Its estimate follows actual article content. The frontend byline formats that estimate as `author · 14 min read`, using the theme's accessible muted color. The editor retains core's native minutes label. The hidden pattern omits this optional metadata when the native block is unavailable. Screenshots: [mobile home](reading-time-home-390.png), [desktop home](reading-time-home-1440.png), [mobile post](reading-time-single-390.png), [desktop post](reading-time-single-1440.png).

## Remaining release checks

- **WordPress 7.1.2 fallback page-list menu:** core renders a `ul` directly inside another `ul`, causing axe's serious `list` finding. This also reproduces with Twenty Twenty-Five 1.5 ([comparison](accessibility-core-theme.json)). Core files were not changed. The clean demo uses configured menu links; the automatic fallback needs rechecking on the release's WordPress version.
- **Imported content:** HTML-formatting and comments fixtures contain empty table headers. Theme code does not rewrite authored content. Some complex fixture contrast checks require manual assessment.
- **Browser/version coverage:** physical Safari/iOS, Firefox, assistive technology, and a fresh WordPress 6.7/PHP 7.4 installation remain release checks. RTL testing here changes document direction, not the full site language.
- **Test media:** official content was imported with `--skip=attachment`; representative local featured images and the bundled pattern image were checked.
- **Existing sites:** Site Editor customizations stored in the database can override theme template changes. These are preserved.

Theme Check and automated browser results do not replace the separate WordPress accessibility-ready review or guarantee directory approval. Nothing has been uploaded or pushed.

## Repeat

The localhost demo is restored after testing. Import the [official Theme Unit Test content](https://github.com/WPTT/theme-unit-test) into a backed-up local database before rerunning the stress routes. See WordPress's [testing guide](https://developer.wordpress.org/themes/advanced-topics/testing/).

From the repository root, with `agent-browser` installed (or `AGENT_BROWSER` set to its executable):

```bash
python3 docs/qa/check-layout.py docs/qa/layout-after.json
python3 docs/qa/check-interactions.py
python3 docs/qa/check-accessibility.py
```

`check-templates.py` requires an authenticated local block editor in the `wildlemon-editor` browser session. Build/Theme Check commands are in [development.md](../development.md).

Demo photos and prompts are in [demo/README.md](demo/README.md); these development fixtures are excluded from the ZIP. The original published screenshot remains in the theme. Docker keeps the local database/uploads in volumes.

### Pagination regression test

```bash
docker compose -f docs/compose.yml run --rm cli wp eval-file \
  wp-content/themes/wild-lemon/docs/qa/check-pagination.php
```

This creates isolated draft fixtures, renders the real homepage blocks for 32 boundary cases, checks exact post IDs and pagination output, and removes its fixtures in `finally`. Existing posts and options are not changed. The browser verification also removed its five temporary published fixtures; the local demo still has its original eight posts.

### Search regression test

```bash
python3 docs/qa/check-search.py
```

This runs against the clean local demo, including its `garden` search results, without changing WordPress content. It measures the expansion throughout the animation and verifies keyboard and pointer behavior. Long-title and RTL checks change only the test browser's document.
