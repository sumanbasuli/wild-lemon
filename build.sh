#!/usr/bin/env bash
#
# Production build for the Wild Lemon theme.
#
# Reads the version from the VERSION file, stamps it into style.css and
# readme.txt in the build copy, validates the sources, and produces:
#
#   build/wild-lemon/          clean theme folder (what WordPress installs)
#   build/wild-lemon.zip       upload-ready archive for wordpress.org
#
# Usage: ./build.sh

set -euo pipefail

THEME_SLUG="wild-lemon"
SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BUILD_DIR="$SRC_DIR/build"
DEST_DIR="$BUILD_DIR/$THEME_SLUG"

VERSION="$(tr -d '[:space:]' < "$SRC_DIR/VERSION")"
if [[ -z "$VERSION" ]]; then
	echo "error: VERSION file is empty" >&2
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
	--exclude ".gitignore" \
	--exclude ".DS_Store" \
	--exclude "*.zip"

# ---- stamp version ----------------------------------------------------------

sed -i '' -E "s/^(Version: ).*/\1$VERSION/" "$DEST_DIR/style.css"
sed -i '' -E "s/^(Stable tag: ).*/\1$VERSION/" "$DEST_DIR/readme.txt"
echo "  version stamped: $VERSION"

# ---- package ----------------------------------------------------------------

( cd "$BUILD_DIR" && rm -f "$THEME_SLUG.zip" && zip -rq "$THEME_SLUG.zip" "$THEME_SLUG" -x "*.DS_Store" )

echo "  build/$THEME_SLUG/"
echo "  build/$THEME_SLUG.zip ($(du -h "$BUILD_DIR/$THEME_SLUG.zip" | cut -f1 | tr -d ' '))"
echo "Done."
