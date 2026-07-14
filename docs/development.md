# Development

## Repo layout

```
wild-lemon/
├── style.css            Theme header + structural CSS only (tokens live in theme.json)
├── theme.json           v3 — palette, typography, spacing, block styles, template parts
├── functions.php        Editor style, stylesheet enqueue, block styles, pattern category
├── templates/           Block templates (home, single, page, archive, author, search, index, 404)
├── parts/               header, footer, footer-compact — thin wrappers that include patterns
├── patterns/            PHP patterns; all translatable strings live here
├── assets/fonts/        Bundled variable woff2 fonts (latin subset, OFL)
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
- Hidden patterns (`Inserter: no`): `hidden-404`, `hidden-no-results`, `query-list`, `archive-header`, `author-header`.
- **theme.json owns every token.** style.css only holds what block attributes cannot express: the logo dot, pill styling, circled pagination, grid rows, 404 numeral, focus states.
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
- The hero query uses `"sticky":"exclude"` (sticky posts otherwise prepend past `perPage:1`).
- Empty tag cloud/categories render nothing; `query-no-results` shows the `hidden-no-results` pattern.

## Testing checklist

1. `php -l functions.php patterns/*.php` and JSON-validate `theme.json` (build.sh does both).
2. Activate on a WP 6.7+ site with the [theme unit test data](https://github.com/WPTRT/theme-unit-test); walk home, single (comments, prev/next), category, tag, author, search, empty search, page, 404. Watch for PHP notices with `WP_DEBUG`.
3. Run [Theme Check](https://wordpress.org/plugins/theme-check/) — must PASS.
4. Open the Site Editor; confirm no template/pattern shows block recovery warnings.

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
