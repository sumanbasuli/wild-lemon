# Author biography QA — 2 October 2026

The QA author's description was empty, so the existing native WordPress biography blocks had no content to display. A sample biography is now saved for `wildlemonqa` on the isolated QA installation. The [fixture helper](../setup-polish-fixtures.php) fills an empty biography and preserves later profile edits. The two imported official-data authors remain unchanged.

Live examples: [post with author card](http://localhost:8089/wl-polish-brief-title/) and [author archive](http://localhost:8089/author/wildlemonqa/). [Restored profile and fixture IDs](profile.json).

## Fixes found by adding the content

- A normal biography forced the archive text below the 120px avatar even at desktop width. The existing native flex group now lets the text use the remaining row width, wrapping below the avatar on phones.
- An unbroken author name caused horizontal overflow in post bylines/cards and author archive headings. Author names and query titles now wrap safely; author-name blocks stay within their available width.

The existing author card, palette, fonts and core blocks are preserved. No custom biography renderer or profile feature was added.

## Verification

[56 recorded layouts](layout.json) cover seven cases on both a post and its author archive at **320, 390, 768 and 1440px**:

- Normal sample biography, short biography, and a 1,440-character biography.
- Multiline text with a local link and emphasis.
- A long unbroken biography and a long unbroken author name.
- Empty biography: core omits the biography block while retaining the author identity and working layout.

All final cases have one main landmark and **zero horizontal page, biography or name overflow**. Normal author cards and archive headers stack on phones and share a row with the avatar at 768/1440px. The post card's name/avatar links point to the matching author archive. Actual keyboard activation reached both the biography link destination and the author archive. [Navigation evidence](navigation.json).

The [first long-name pass](long-name-first-pass.json) records the overflow before the wrapping correction; it is retained separately from the passing final matrix. WordPress preserves allowed biography links and emphasis, but plain blank lines do not create paragraph elements in the native biography block. This core formatting behavior is unchanged.

The normal sample biography and display name were restored after testing. [Mobile post](mobile-post.jpg) and [desktop archive](desktop-archive.jpg) screenshots show the final profile.

## Backend and package

[Focused backend checks](backend-summary.json) on WordPress **7.1.2 / PHP 8.3.35** pass:

- All **22** production PHP files lint; theme JSON validates.
- The registered author-header pattern retains native blocks, passes server parse/serialize round trip and is referenced by the author template.
- Clean-package Theme Check: **0 errors, 0 warnings, 2 informational messages**.
- The rebuilt **1.3** ZIP passes integrity and exclusion checks; all **46** packaged files match the current source byte for byte. [Package manifest](package.json).

Current ZIP SHA-256:

```text
110c72e69e04bddb5310cc4b98fe3fcb0640b7139289a8bd4e631d44f743e61e
```

This is a focused follow-up to the [earlier full polish pass](../polish-2026-10-02/README.md). It does not rerun that pass's 500-route-width matrix, minimum-version suite, editor round trip, or complete accessibility/browser coverage. Native server parsing is not a claim of fresh editor JavaScript validation. The earlier snapshots retain their original source/package hashes; the rebuilt package above includes the author layout corrections. This snapshot precedes the [1.4 release checks](../release-1.4/README.md).
