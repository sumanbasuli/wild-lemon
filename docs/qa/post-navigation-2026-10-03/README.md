# Next-post arrow correction — 3 October 2026

CSS aligns WordPress's native trailing next arrow with its label using a shared grid cell and baseline. The title retains its full width and logical end alignment. The rule applies when the core block contains both a label and an arrow.

Verified on the local WordPress QA site at `http://localhost:8089` in Chromium:

- Twenty layout cases: short, long and unbroken next titles at 320, 640, 768 and 1440 pixels; missing previous/next posts at 320 and 1440; RTL direction emulation and increased text spacing at 320 and 1440. No horizontal overflow. Arrow and label centers differ by at most one pixel; desktop previous/next labels differ by at most 1.25 pixels.
- Two browser-only markup variants matching core's title-disabled and arrow-disabled options retain their native inline links and zero extra label padding.
- Tab reaches the next link with a visible outline and underline. Enter navigates to the expected adjacent post, “Tea”.
- Two axe-core 4.12.1 audits scoped to `.wl-post-nav`, at 320 and 1440 pixels: zero violations. The decorative arrows require manual color-contrast review; their computed color `rgb(114, 107, 92)` against `rgb(251, 249, 243)` measures **5.02:1**.
- Desktop and mobile screenshots were inspected. `git diff --check` passes.

Measurements are in [checks.json](checks.json). These are focused regression checks; RTL uses direction emulation rather than a translated WordPress installation.

The correction is included in version 1.4.1. Clean package and release checks are recorded in [the 1.4.1 report](../release-1.4.1/README.md).
