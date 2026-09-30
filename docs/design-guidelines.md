# Design Guidelines

Wild Lemon is a warm, editorial blogging theme: bookish serif reading, quiet grotesk UI, one lemon accent. The published theme and original design handoff establish the visual identity. Refinements must preserve the homepage composition, heading scale, palette, typefaces, rounded imagery, and pill controls. Every color token below maps to `theme.json`.

## Color tokens (`settings.color.palette`)

| Slug | Hex | Name | Role |
| --- | --- | --- | --- |
| `base` | `#FBF9F3` | Base | Page background |
| `contrast` | `#221E18` | Contrast | Body text, dark footer background |
| `contrast-2` | `#57503F` | Deep Stone | Ledes, secondary prose |
| `accent` | `#EDC93B` | Lemon | Logo dot, highlights, decorative marks only — never text |
| `accent-2` | `#F6ECC2` | Lemon Tint | Pills, topic strip, author box backgrounds |
| `accent-3` | `#695F01` | Olive | Links, category eyebrows (from `oklch(0.48 0.1 102)`) |
| `muted` | `#6F6759` | Stone | Excerpts, secondary UI text |
| `muted-2` | `#726B5C` | Stone Light | Meta text: dates, labels, captions |
| `line` | `#E6E0D2` | Line | Hairline borders |

Fixed non-token colors: footer secondary text `#B5AE9E`, footer link text `#DDD7C9`, footer divider `#3A352C`, pill borders `#DCD5C4`, highlight wash `#F2DE8A`, row hover `#F7F4EA`.

The full footer retains its dark background and two link columns. Keep the columns equal in width, preserve the breathing room below the small uppercase headings, and use compact 36px link rows (including RSS). Hover and keyboard focus use the lemon accent and an underline; keyboard focus also has a visible outline. On small screens the brand sits above the two columns. Long and nested page names must wrap within their column.

### Contrast rules (accessibility-ready)

Every text/background pair must hit **WCAG AA 4.5:1**. Current audit:

- `muted-2` is `#726B5C` (not the mock's `#8B8474`, which fails at 3.53:1). Do not lighten it.
- On `accent-2` tint backgrounds, the lightest allowed text is `muted` (4.71:1).
- On `contrast` dark backgrounds, the darkest allowed text is `#B5AE9E` (7.51:1).
- `accent` lemon is decorative only; it never carries text.

Re-verify with the ratio script in [development.md](development.md#contrast-check) whenever a color changes.

## Typography (`settings.typography`)

| Family | Slug | Role |
| --- | --- | --- |
| Newsreader (variable, opsz 6–72, wght 400–700, + italic) | `newsreader` | Headings, prose, pullquotes |
| Schibsted Grotesk (variable, wght 400–700, + italic) | `schibsted-grotesk` | UI, meta, navigation, buttons |

Both are bundled locally in `assets/fonts/` (latin subset, woff2, SIL OFL). Never load fonts from a CDN.

Scale: `x-small` 12 · `small` 14 · `medium` 17 (root) · `large` 30 · `x-large` 44 · `xx-large` 64, with `large`+ fluid. Body prose (`core/post-content`) runs Newsreader 19px/1.7. Hero/post titles retain the published per-template fluid sizes (58px home, 54px single at desktop).

Meta labels: grotesk, uppercase, `0.1–0.14em` tracking, 600–700 weight, `x-small`.

## Spacing (`settings.spacing.spacingSizes`)

`20` 12px · `30` 16px · `40` 24px · `50` clamp(24–36px) · `60` clamp(24–56px, the global gutter) · `70` clamp(40–72px) · `80` clamp(48–96px).

Layout: `contentSize` 680px (prose), `wideSize` 1120px (heroes, grids). Root padding uses spacing `60`; sections are `alignfull` groups carrying their own matching gutter.

## Motifs

- **Logo dot**: lemon circle after the site title (`.wl-logo a::after`). Never text-colored.
- **Pills**: rounded-99px chips — tinted (`.wl-pill`, categories), outlined (`.wl-pill-outline`, tags), topic cloud (`.wl-topic-pills`).
- **Hairlines**: 1px `line` borders separate sections; pullquotes get a 3px `contrast` top rule.
- **Circled pagination**: 40px circles, filled `contrast` for the current page.
- **404 numeral**: CSS-art "4○4" with lemon seed (`.wl-404-numeral`), always `aria-hidden="true"`.

## Accessibility guardrails

- Never remove focus outlines without a `:focus-visible` replacement.
- Every `:hover` style on interactive elements pairs with `:focus`.
- Content links stay underlined (`elements.link` in theme.json); color alone never signals a link.
- One `h1` per page; no heading level skips.
- Decorative art gets `aria-hidden="true"`.

## Editorial layouts

- Give each section a clear reading order. Use whitespace and typography before adding a box or decoration.
- Keep prose at the 680px content width; let editorial compositions use the 1120px wide width.
- Intro and notes patterns respond to their container width, including when placed inside a narrow column.
- Preserve the existing 12–14px image corners. Demo imagery may vary without changing the theme composition.
- Center the 24px menu icon inside a 44px touch area. Retain the labeled search pill and core navigation keyboard/focus handling.
