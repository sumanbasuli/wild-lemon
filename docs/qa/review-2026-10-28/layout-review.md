# Chromium layout stress review — 28 October 2026

Fresh isolated sites run WordPress **7.1.3 / PHP 8.3.36** on port 8089 and the theme's minimum WordPress branch, **6.7.10 / PHP 7.4.33**, on port 8090. Production changes tested are the native Post Content top-padding correction and current-menu link styles.

| Suite | Current | Minimum |
| --- | --- | --- |
| Published post/page and template routes | 550 cases at 320, 390, 768, 1024 and 1440px | 330 cases at 320, 768 and 1440px |
| Category/title and narrow-container stress | 84 cases | 84 cases |
| Total | 634 cases | 414 cases |

**All 1,048 cases passed.** Layout routes include 102 published fixture posts/pages plus template extras, deduplicated to 110 paths. Both installations have identical published path sets.

The full route matrix reports zero horizontal overflow, compressed Query Loop titles, broken loaded local images, missing image-alt attributes, main-landmark count errors, or geometric overflow offenders. Image results apply to images loaded at measurement time; attachment validation and full interaction/accessibility audits are separate checks.

Archive tests cover 1, 2, 3, 4 and 63 categories; viewport widths from 320 to 1440px; containers from 240 to 780px; RTL direction emulation; and increased text spacing. Every archive title retains usable width, category/title overlap is absent, and horizontal overflow stays within the one-pixel tolerance.

## Content observations

The deliberately untitled official post `/edge-case-no-title/` has no H1. Six official fixtures include an H1 within their content in addition to the template title: `/text-category-blocks/`, `/theme-block-category/`, `/about/page-markup-and-formatting/`, `/markup-html-tags-and-formatting/`, `/block-category-common/`, and `/greek/`. These observations are recorded, unchanged content; this result does not claim every fixture has one H1.

## Evidence and scope

- [Current layout](current-layout.json), [minimum layout](minimum-layout.json), and [summary](layout-summary.json).
- [Current archive stress](current-archive/archive-regression.json) and [minimum archive stress](minimum-archive/archive-regression.json).
- Helpers used unchanged: `docs/qa/check-official-layout.py` and `docs/qa/check-archive-layout.py`. Dedicated browser sessions were used and closed by the helpers.

These are Chromium geometry and markup checks. Keyboard flows, selected-menu interaction, Site Editor serialization, axe, other browser engines, and manual visual review are recorded separately.
