# Wild Lemon 1.5 — release checks

Completed **3 October 2026**. Version 1.5 packages the tested post navigation correction and uses changelog-only GitHub release notes.

- Rendered styles are unchanged from the tested 1.4.1 source except for the version header. The [navigation checks](../post-navigation-2026-10-03/README.md) cover twenty layout cases, optional core block settings, keyboard activation, two scoped axe scans and 5.02:1 arrow contrast.
- Official Theme Check 20260901 passes on the clean 1.5 package with zero required issues, warnings or recommendations and two informational messages. See [results](theme-check.json).
- Production PHP lint and theme JSON validation pass. See [build](build.txt).
- All 46 packaged files match source byte for byte. ZIP integrity, exclusions and version headers pass. See [manifest](package.json).
- [Release notes](release-notes.md) contain only the user-facing changelog. The committed workflow generates this exact content without an additional footer.
- The existing branch-trigger workflow and development-document drafts remain local; the workflow commit contains only the notes-generator correction.

Local package: `build/wild-lemon-1.5.zip` (identical to `build/wild-lemon.zip`). Local SHA-256:

```text
aa123294eac94b42e2b321c45f8d3f6f55093c3551d92a370d0210d5e4e0196d
```

Publication uses the annotated `v1.5` tag and the [GitHub release](https://github.com/sumanbasuli/wild-lemon/releases/tag/v1.5). CI ZIP timestamps can differ; packaged file bytes must match the tested build.
