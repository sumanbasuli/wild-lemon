# Wild Lemon 1.6 release review

Prepared on 28 October 2026. [Release notes](release-notes.md) describe the user-facing changes. The [full theme and accessibility review](../review-2026-10-28/README.md) contains the official Theme Unit Test data provenance, layout and interaction coverage, real editor round trips, coding standards, and recorded legacy core/content findings.

## Final release gates

- `VERSION`, the `style.css` version, and the `readme.txt` stable tag all match **1.6**.
- The isolated production build passes PHP lint, theme JSON parsing, ZIP integrity, required-file checks and development-file exclusions. All **46 packaged files** match the source. [Package manifest](package.json), [build output](build.txt).
- The final package changes only version metadata and the new changelog compared with the final reviewed working tree. All behavior and layout changes are identical to the tested source. [Source equivalence](qa-source-equivalence.json).
- Official Theme Check **20260901** passes **7,839 checks** on each runtime, with **0 required issues, warnings or recommendations** and two informational messages. Both manifests match the 1.6 package: [WordPress 7.1.3 / PHP 8.3.36](theme-check-current.json), [WordPress 6.7.10 / PHP 7.4.33](theme-check-minimum.json).
- The committed tag-triggered release workflow builds from `v1.6` and generates the public release notes from the matching readme changelog. The separate existing workflow/development-document drafts are excluded from this release commit.

The pre-release reports retain their original 1.5 working-tree labels and fingerprints; they have not been rewritten as new test runs. No additional broad browser suites were needed for the version/changelog update.

The local review site remains available at [localhost:8089](http://localhost:8089/), with its database and uploads preserved and the web/database services configured to restart unless stopped. Local credentials remain in the chat.
