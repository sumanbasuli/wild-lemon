# Release & Submission

## Versioning

The single source of truth is the `VERSION` file. `build.sh` stamps it into the build copy's `style.css` (`Version:`) and `readme.txt` (`Stable tag:`) — the source files keep whatever version they had, so bump `VERSION` and add a `readme.txt` changelog entry together.

Format: wordpress.org accepts `1.2` or `1.2.1`. Every resubmission after a review must increase the version.

## Build

```bash
./build.sh
```

Validates PHP + theme.json, copies the theme into `build/wild-lemon/` excluding dev files (`docs/`, `build.sh`, `VERSION`, `.git*`, `build/`), stamps the version, and zips `build/wild-lemon.zip`.

## wordpress.org submission checklist

Before uploading `build/wild-lemon.zip` at <https://wordpress.org/themes/upload/>:

- [ ] `VERSION` bumped, changelog entry added in `readme.txt`
- [ ] Theme Check plugin: PASS, no required issues
- [ ] `screenshot.png` is a genuine front-end capture, 1200×900, no browser chrome or admin bar
- [ ] style.css tags are truthful (theme actually supports each tag)
- [ ] `Tested up to` uses a major version only (e.g. `7.0`, never `7.0.2`)
- [ ] No plugin-territory code: no analytics, SEO, custom post types, shortcodes, syntax highlighters, custom lazy loading, or content parsing — core handles images/lazy-loading natively
- [ ] All bundled assets licensed and credited in `readme.txt` (fonts: OFL with source links; images: GPL note)
- [ ] No remote assets: fonts, scripts, and images all ship in the theme
- [ ] Full test-data walk with `WP_DEBUG` on — zero notices

### accessibility-ready

The theme carries the `accessibility-ready` tag, which triggers a **separate, slower review**. Keep these invariants or drop the tag:

- [ ] All text/background pairs ≥ 4.5:1 (see contrast table in design-guidelines.md)
- [ ] Skip link present (core provides it for block themes — verify it renders)
- [ ] Keyboard: everything operable, focus always visible, hover states paired with focus
- [ ] Forms labeled (search blocks keep their screen-reader-text labels)
- [ ] One `h1` per page, no heading skips, correct landmarks
- [ ] Content links underlined; color never the only signal
- [ ] Decorative elements `aria-hidden`

## Review round-trips

Rejections don't hard-fail: fix, bump `VERSION`, rebuild, and resubmit — the ticket re-enters at the end of the queue. Keep reviewer notes and the fixes for them in the changelog.
