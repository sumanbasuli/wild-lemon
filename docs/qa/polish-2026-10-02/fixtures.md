# Persistent polish fixtures

These fixtures supplement the official Theme Unit Test data on the isolated QA site. They preserve the official posts, menu, templates and site settings. The fixture posts are dated January 2000 so they do not displace the recent homepage stories.

From the repository root:

```bash
WILD_LEMON_PORT=8089 docker compose -p wild-lemon-qa -f docs/compose.yml run --rm cli wp eval-file wp-content/themes/wild-lemon/docs/qa/setup-polish-fixtures.php
```

The command prints JSON containing the author ID, biography and archive URL, seven category IDs and all 12 fixtures' IDs and URLs. It adds a sample biography only when that QA author's biography is empty, preserving later profile edits. Run it again to refresh these records. The helper accepts only `http://localhost:8089` and `http://localhost:8090`, uses an existing WordPress author, and refuses to overwrite a record it does not own. Compatibility runs must use their existing project and image configuration.

All slugs begin with `wl-polish-`. Records carry `_wl_polish_fixture=polish-2026-10-02` ownership meta. They remain available for repeat browser checks; no cleanup runs automatically.

Coverage includes one, four and seven category pills; long category labels; brief, long and unbroken post titles; a post without a featured image; native columns, tables, lists, quote, gallery, Details and buttons; all six editorial patterns at normal width and inside 320px/600px columns; a page with comments enabled; and a short page with comments closed. Photos come from bundled theme assets and an existing local attachment. No remote media is imported.
