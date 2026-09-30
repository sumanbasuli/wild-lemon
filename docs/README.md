# Wild Lemon — Documentation

Wild Lemon is a warm editorial FSE (block) theme for WordPress, built entirely on core blocks with no plugin dependencies. These docs are for developing and releasing the theme; they are excluded from the production build.

| Document | What it covers |
| --- | --- |
| [design-guidelines.md](design-guidelines.md) | Color tokens, typography, spacing, motifs, accessibility rules |
| [development.md](development.md) | Repo layout, templates/parts/patterns architecture, conventions, testing |
| [compose.yml](compose.yml) | Local WordPress and WP-CLI preview stack |
| [qa/README.md](qa/README.md) | Recorded visual, responsive, editor, and accessibility checks |
| [release.md](release.md) | Versioning, building, wordpress.org submission checklist |

## Quick start

```bash
# Drop the theme into a WordPress install
wp-content/themes/wild-lemon/

# Production build (zip for wordpress.org)
./build.sh          # → build/wild-lemon.zip
```

Requires WordPress 6.7+, PHP 7.4+.
