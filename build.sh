#!/usr/bin/env bash
#
# Production build for the Wild Lemon theme.
#
# Reads the version from the VERSION file, stamps it into style.css and
# readme.txt in the build copy, validates the sources, and produces:
#
#   build/wild-lemon/          clean theme folder (what WordPress installs)
#   build/wild-lemon.zip       upload-ready archive for wordpress.org
#   build/wild-lemon-VERSION.zip  versioned copy for GitHub releases
#
# Usage: ./build.sh

set -euo pipefail

THEME_SLUG="wild-lemon"
SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_DIR="$SRC_DIR/build"
DEST_DIR="$BUILD_DIR/$THEME_SLUG"

VERSION="$(tr -d '[:space:]' < "$SRC_DIR/VERSION")"
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+(\.[0-9]+)?$ ]]; then
	echo "error: VERSION must be a numeric version such as 1.3 or 1.3.1" >&2
	exit 1
fi

echo "Building $THEME_SLUG $VERSION"

# ---- validate sources -------------------------------------------------------

if command -v php >/dev/null; then
	for f in "$SRC_DIR/functions.php" "$SRC_DIR"/patterns/*.php; do
		php -l "$f" >/dev/null || { echo "error: PHP lint failed: $f" >&2; exit 1; }
	done
	echo "  PHP lint: OK"
fi

if command -v python3 >/dev/null; then
	python3 -c "import json; json.load(open('$SRC_DIR/theme.json'))" \
		|| { echo "error: theme.json is not valid JSON" >&2; exit 1; }
	echo "  theme.json: OK"
fi

# ---- copy theme files -------------------------------------------------------

rm -rf "$BUILD_DIR"
mkdir -p "$DEST_DIR"

rsync -a "$SRC_DIR/" "$DEST_DIR/" \
	--exclude "build" \
	--exclude "build.sh" \
	--exclude "VERSION" \
	--exclude "docs" \
	--exclude ".git" \
	--exclude ".github" \
	--exclude ".gitignore" \
	--exclude ".DS_Store" \
	--exclude "*.zip"

# ---- stamp version ----------------------------------------------------------

# A backup suffix works with both BSD sed (macOS) and GNU sed (Linux).
sed -i.bak -E "s/^(Version: ).*/\1$VERSION/" "$DEST_DIR/style.css"
sed -i.bak -E "s/^(Stable tag: ).*/\1$VERSION/" "$DEST_DIR/readme.txt"
rm "$DEST_DIR/style.css.bak" "$DEST_DIR/readme.txt.bak"
echo "  version stamped: $VERSION"

# ---- package ----------------------------------------------------------------

( cd "$BUILD_DIR" && rm -f "$THEME_SLUG.zip" && zip -rq "$THEME_SLUG.zip" "$THEME_SLUG" -x "*.DS_Store" )
cp "$BUILD_DIR/$THEME_SLUG.zip" "$BUILD_DIR/$THEME_SLUG-$VERSION.zip"

echo "  build/$THEME_SLUG/"
echo "  build/$THEME_SLUG.zip ($(du -h "$BUILD_DIR/$THEME_SLUG.zip" | cut -f1 | tr -d ' '))"
echo "  build/$THEME_SLUG-$VERSION.zip"
echo "Done."
