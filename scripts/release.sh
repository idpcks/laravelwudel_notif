#!/bin/bash

# Script untuk auto-release package Laravel
# Usage: ./scripts/release.sh [patch|minor|major]

set -e

# Check if version type is provided
if [ -z "$1" ]; then
    echo "Usage: $0 [patch|minor|major]"
    echo "  patch: 1.0.0 -> 1.0.1"
    echo "  minor: 1.0.0 -> 1.1.0"
    echo "  major: 1.0.0 -> 2.0.0"
    exit 1
fi

VERSION_TYPE=$1

# Get current version from composer.json
CURRENT_VERSION=$(grep '"version"' composer.json | sed 's/.*"version": "\([^"]*\)".*/\1/')

if [ -z "$CURRENT_VERSION" ]; then
    echo "Version not found in composer.json, setting to 1.0.0"
    CURRENT_VERSION="1.0.0"
fi

echo "Current version: $CURRENT_VERSION"

# Split version into parts
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR=${VERSION_PARTS[0]}
MINOR=${VERSION_PARTS[1]}
PATCH=${VERSION_PARTS[2]}

# Calculate new version
case $VERSION_TYPE in
    "patch")
        PATCH=$((PATCH + 1))
        ;;
    "minor")
        MINOR=$((MINOR + 1))
        PATCH=0
        ;;
    "major")
        MAJOR=$((MAJOR + 1))
        MINOR=0
        PATCH=0
        ;;
    *)
        echo "Invalid version type: $VERSION_TYPE"
        exit 1
        ;;
esac

NEW_VERSION="$MAJOR.$MINOR.$PATCH"
echo "New version: $NEW_VERSION"

# Update composer.json
sed -i "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEW_VERSION\"/" composer.json

# Update CHANGELOG.md
TODAY=$(date +"%Y-%m-%d")
echo "## [$NEW_VERSION] - $TODAY" | cat - CHANGELOG.md > temp && mv temp CHANGELOG.md

# Commit changes
git add composer.json CHANGELOG.md
git commit -m "Bump version to $NEW_VERSION"

# Create and push tag
git tag -a "v$NEW_VERSION" -m "Release version $NEW_VERSION"
git push origin main
git push origin "v$NEW_VERSION"

echo "Release $NEW_VERSION created and pushed successfully!"
echo "Next steps:"
echo "1. Check GitHub Actions for build status"
echo "2. Verify release on GitHub: https://github.com/idpcks/laravelwudel_notif/releases"
echo "3. Package will be automatically updated on Packagist: https://packagist.org/packages/laravelwudel/laravelwudel-notif"
