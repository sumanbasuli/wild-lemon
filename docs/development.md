# Development

## Repo layout

```
wild-lemon/
├── style.css            Theme header + layout and pattern CSS (tokens live in theme.json)
├── theme.json           v3 — palette, typography, spacing, block styles, template parts
├── functions.php        Editor style, stylesheet enqueue, block styles, pattern category
├── templates/           Block templates (home, single, page, archive, author, search, index, 404)
├── parts/               header, footer, footer-compact — thin wrappers that include patterns
├── patterns/            PHP patterns; all translatable strings live here
├── assets/fonts/        Bundled variable woff2 fonts (latin subset, OFL)
├── assets/images/       Bundled editorial pattern imagery
├── screenshot.png       1200×900 front-end capture
├── readme.txt           wordpress.org readme (license, credits, changelog)
├── docs/                These docs (excluded from builds)
├── build.sh + VERSION   Production build tooling (excluded from builds)
└── build/               Build output (gitignored)
```

## Architecture rules

- **Templates are HTML, so they contain no user-facing strings.** Anything translatable sits in a `patterns/*.php` file wrapped in `esc_html_e()` / `esc_attr_e()` with the `wild-lemon` text domain — matching how core themes (Twenty Twenty-Five) do it.
- **Parts include patterns** (`<!-- wp:pattern {"slug":"wild-lemon/header"} /-->`) so header/footer strings are translatable too.
- `patterns/query-list.php` is the shared post-list (rows + pagination + no-results) used by archive, author, search, and index.
- Template-only patterns (`Inserter: no`): 404, header/footer, comments, author bio, and query sections. Six editorial patterns are available in the inserter: A life well noticed, Notes on the everyday, A thought to keep, A table for the season, A day in pictures, and A slower weekend.
- **theme.json owns every token.** style.css holds layout, responsive behavior, and custom pattern styles that block attributes cannot express.
- PHP: prefix functions `wild_lemon_`, pattern variables `$wild_lemon_`, escape all output.
- Entities in translatable strings: use real UTF-8 characters (`…` `’` `—`), never `&hellip;`-style entities — `esc_html_e()` double-escapes them.

## Block markup conventions

Hand-written block markup must match the editor's serializer or the Site Editor shows "attempt recovery":

- Dynamic blocks (`post-title`, `post-date`, `query-title`, `avatar`, …) are self-closing comments — prefer them; there is no HTML to mismatch.
- Static wrappers (`group`, `columns`, `paragraph`, `heading`) need exact HTML. Inline style order: border → color → spacing (top/right/bottom/left) → typography (size, style, weight, line-height, letter-spacing, text-transform).
- Preset attrs serialize as classes (`has-muted-color has-text-color has-x-small-font-size`); raw values serialize inline.
- Copy conventions from Twenty Twenty-Five patterns when unsure.

## Graceful degradation

- Posts without featured images: `.wl-media-row` and the home hero collapse the image column via `:has()` — never leave a dead 200px gutter.
- Preserve the published homepage composition: featured story, latest grid, topics strip, and archive rows. The existing archive query supplies pagination for older posts.
- Core Query Loop offsets skip posts without subtracting them from `found_posts`. The two `wild_lemon_query_loop_*` filters carry the initial offset into `WP_Query` and correct its total. They do not change row offsets or unrelated queries. The frontend remains core Query Loop, Pagination, and No Results blocks.
- Each homepage loop includes the compact `hidden-no-posts` pattern inside a core No Results block. Do not hide blank loops with CSS or remove their empty-state message.
- The featured homepage post and single-post bylines include `hidden-reading-time`, using the core Time to Read block. WordPress calculates the estimate; a render filter formats only the marked byline block as a translatable `%s min read` label. Other instances, ranges, and word counts retain core output. The byline uses 14px muted text and a decorative dot, matching the published screenshot. This optional metadata requires WordPress 6.9+; the pattern outputs nothing when the block is unavailable, preserving WordPress 6.7/6.8 compatibility without an unsupported editor block.
- The hero/latest/archive queries exclude sticky posts consistently to preserve their offsets.
- Archive rows use the Query Loop's container width. Short category lists retain the published three-column composition; four or more categories move below the title, and narrow containers reflow to two columns or a stack. Every category remains available.
- `wild_lemon_query_pagination_visibility()` only handles blocks marked `wl-pagination`. It preserves core's rendered links and query filters while suppressing empty/out-of-range pagination, including WordPress 6.7's unconditional Previous link.
- Query Loop dates are native Post Date permalinks, keeping untitled posts reachable. The Page template includes the existing Comments pattern; core controls whether comments appear.
- Core Post Content adds page links for Page Break blocks but omits classic `<!--nextpage-->` links. `wild_lemon_legacy_page_links()` supplies those with `wp_link_pages()` for singular legacy content; block-based pagination is left to core.
- Empty tag cloud/categories render nothing; `query-no-results` shows the `hidden-no-results` pattern.

## Testing checklist

1. `php -l functions.php patterns/*.php` and JSON-validate `theme.json` (build.sh does both).
2. Activate on a WP 6.7+ site with the [theme unit test data](https://github.com/WordPress/theme-test-data); walk home, single (comments, prev/next), category, tag, author, search, empty search, page, 404. Watch for PHP notices with `WP_DEBUG`.
3. Run [Theme Check](https://wordpress.org/plugins/theme-check/) — must PASS.
4. Open the Site Editor; confirm no template/pattern shows block recovery warnings.
5. Keep `accessibility.txt` current with the actual testing scope and support contacts. Use `docs/qa/check-accessibility-review.py` alongside axe to verify keyboard paths, control contrast, reflow, and text spacing; automated results do not replace an assistive-technology audit.

## Local WordPress preview

From the repository root:

```bash
docker compose -f docs/compose.yml up -d db wordpress
```

Open <http://localhost:8088/> and complete WordPress installation on first use. The current theme directory is mounted into the container, so changes appear after a browser reload. Activate **Wild Lemon** in Appearance → Themes. Database and uploads are stored in Docker volumes; the theme source stays in this repository. The database credentials in `compose.yml` are for this localhost-only preview.

To run Theme Check on the clean production package:

```bash
./build.sh
docker compose -f docs/compose.yml run --rm cli wp plugin install theme-check --activate
docker compose -f docs/compose.yml run --rm \
  -v "$(pwd)/build/wild-lemon:/var/www/html/wp-content/themes/wild-lemon:ro" \
  cli wp theme-check run wild-lemon
```

Stop the preview with `docker compose -f docs/compose.yml down`. The database and uploads remain available for the next run.

During theme development, set `WP_DEVELOPMENT_MODE` to `theme` in the local WordPress configuration to version the stylesheet by its modification time. Production uses the theme version.

The recorded stress-test results and current limitations are in [qa/README.md](qa/README.md).

The [full official-fixture audit](qa/official-2026-09-30/README.md) includes all published fixtures and attachments, archive title-width assertions, WordPress 6.7.2/PHP 7.4.33 compatibility, and Firefox/WebKit checks. Use `WILD_LEMON_PORT=8089` with Compose project `wild-lemon-qa` for an isolated database and uploads; the default demo stays on 8088. `WILD_LEMON_WORDPRESS_IMAGE` can select a compatibility image. Keep both environment values consistent for all Compose commands targeting that project.

The local Pattern Gallery's six-pattern composition is saved in [qa/gallery-page.html](qa/gallery-page.html). It is demo page content, not an automatically created page or a required theme template. The three new patterns use core Image, Columns, Heading, Paragraph, and Details blocks. All pattern photos are bundled in `assets/images/`; none depend on the demo site's uploads or a remote URL.

### Contrast check

```python
def lum(h):
    h = h.lstrip('#'); r, g, b = (int(h[i:i+2], 16) / 255 for i in (0, 2, 4))
    f = lambda c: c / 12.92 if c <= 0.04045 else ((c + 0.055) / 1.055) ** 2.4
    return 0.2126 * f(r) + 0.7152 * f(g) + 0.0722 * f(b)

def ratio(a, b):
    la, lb = sorted((lum(a), lum(b)), reverse=True)
    return (la + 0.05) / (lb + 0.05)   # text needs >= 4.5
```

Run it over every new text/background pair before shipping (see the pair table in design-guidelines.md).

## Accessibility regression checks

On each template: exactly one `h1`, no heading level skips, `header/main/footer/nav` landmarks present, core skip link renders, no `img` without an `alt` attribute, no empty links/buttons, no unlabeled form controls, no removed focus outline without a `:focus-visible` replacement.
