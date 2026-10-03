# Wild Lemon 1.4.1 — release checks

Completed **3 October 2026**. This patch release aligns the native next-post arrow beside its label and preserves the title's full width and link behavior.

## Checks

- [Post navigation regression checks](../post-navigation-2026-10-03/README.md): twenty layout cases at mobile/desktop widths, long and unbroken titles, missing adjacent posts, increased text spacing and RTL direction emulation. Two browser-only optional block variants retain native inline links. Keyboard focus and Enter navigation pass.
- Two axe-core 4.12.1 scans scoped to post navigation show zero violations. Manual arrow contrast measures **5.02:1**. These are focused checks; the broader [1.4 release checks](../release-1.4/README.md) remain an earlier baseline.
- Official Theme Check **20260901**, on the clean package under WordPress **7.1.2 / PHP 8.3.35**: passed with zero required issues, warnings or recommendations, and two informational messages. See [results](theme-check.json).
- [Build](build.txt): production PHP lint and theme JSON validation pass.
- [Package manifest](package.json): all **46** package files match source byte for byte. ZIP integrity, development-file exclusions, required files and 1.4.1 version headers pass. Upload-ready and versioned local ZIPs are identical.
- `git diff --check` passes. The unrelated workflow and development-document drafts are excluded from the release commit.

Local upload-ready package: `build/wild-lemon.zip`. Versioned package: `build/wild-lemon-1.4.1.zip`.

Local ZIP SHA-256:

```text
a6f8ea257d631b9c4a085e728dff0480b3b6f7c853c42e2e24994897fc2b5b6d
```

The annotated `v1.4.1` tag triggers the existing workflow to publish [Wild Lemon 1.4.1](https://github.com/sumanbasuli/wild-lemon/releases/tag/v1.4.1) with ZIP and checksum assets. CI ZIP timestamps can differ from the local archive; packaged file bytes must match the tested source. WordPress.org upload is handled manually.
