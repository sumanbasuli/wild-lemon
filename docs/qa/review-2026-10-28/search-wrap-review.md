# Wrapped-header Search correction — 28 October 2026

Stress QA reproduced an additional header defect on both WordPress installations: above the mobile breakpoint, the native wrapping controls Group could place Search at the start of a new line. Its absolutely positioned field expanded toward the inline start and became partly off-screen.

At 901px on the current installation, the expanded field occupied **x −159.27 to 129.05** although document overflow measured zero. The screenshot and the existing Search suite confirm that document overflow alone was insufficient to catch the clipped interface.

## Correction

The header controls Group now uses `justify-content: flex-end`, `max-inline-size: 100%`, and `margin-inline-start: auto`. Wrapped rows remain anchored at the logical inline end, with direction handled naturally in RTL. Existing breakpoints, the core Search block, transitions, keyboard behavior and focus styles are retained.

At 901px after the correction, the field occupies **x 567.64 to 855.95**, within the viewport. Header height stays **240.41px** across the correction, and opening Search produces zero measured header/button/content movement in the regression suite.

## Final verification

| Suite | Current WP 7.1.3 / PHP 8.3.36 | Minimum WP 6.7.10 / PHP 7.4.33 |
| --- | --- | --- |
| Existing full Search helper | 15 passed | 15 passed |
| Combined long-brand, direction and menu-wrap stress | 8 passed | 8 passed |

The unchanged existing helper checks 320, 390, 600, 768, 900, 901, 1024 and 1440px; long titles; RTL; animated expansion without layout movement; focus on opening; Escape focus restoration; outside-click closing; native Enter and button submissions; and reduced motion.

Extended browser-only cases combine long brand names with LTR/RTL direction at 901/1440px, using the official menu and a doubled menu that forces additional wrapping. All fields stay inside their header and viewport, focus enters the input, and header/button/content geometry stays stable when opening. No site content was changed by these extra fixtures.

**All 46 final Search cases passed.** The final stylesheet SHA-256 is:

```text
c3a7d00a5aaeb3b0c7c2358f3c883176fb264b4989789feea4b5202e7497a367
```

The broader [1,048-case layout matrix](layout-review.md) ran before these three header-alignment declarations. This report records the focused final Search verification; it does not claim the entire earlier matrix was rerun afterward.

## Evidence

- Current baseline: [failing helper output](current/search-before.txt), [901px screenshot](current/search-wrap-before-901.png), [geometry](current/search-wrap-before-901.json).
- Minimum baseline: [failing helper output](minimum/search-run.txt).
- Current correction: [901px screenshot](current/search-wrap-after-901.png), [geometry](current/search-wrap-after-901.json), [full helper results](current/search-after.json), [run output](current/search-after-run.txt), [extended cases](current/search-wrap-extended.json).
- Minimum correction: [901px screenshot](minimum/search-wrap-after-901.png), [geometry](minimum/search-wrap-after-901.json), [full helper results](minimum/search-after.json), [run output](minimum/search-after-run.txt), [extended cases](minimum/search-wrap-extended.json).

All dedicated Search browsers were closed. Source edits were limited to `style.css`; the existing Search helper was unchanged.
