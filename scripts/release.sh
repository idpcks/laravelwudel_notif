#!/bin/bash

# LaravelWudel Notif Package Release Script
# This script helps with releasing new versions of the package

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 LaravelWudel Notif Package Release Script${NC}"
echo "=================================================="

# Check if we're on main branch
CURRENT_BRANCH=$(git branch --show-current)
if [ "$CURRENT_BRANCH" != "main" ]; then
    echo -e "${RED}❌ Error: You must be on the main branch to release${NC}"
    echo "Current branch: $CURRENT_BRANCH"
    exit 1
fi

# Check if working directory is clean
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}❌ Error: Working directory is not clean${NC}"
    echo "Please commit or stash your changes first"
    git status --short
    exit 1
fi

# Get current version
CURRENT_VERSION=$(grep '"version"' composer.json | sed 's/.*"version": "\([^"]*\)".*/\1/')
echo -e "${YELLOW}Current version: $CURRENT_VERSION${NC}"

# Ask for new version
read -p "Enter new version (e.g., 1.0.1): " NEW_VERSION

if [ -z "$NEW_VERSION" ]; then
    echo -e "${RED}❌ Error: Version cannot be empty${NC}"
    exit 1
fi

# Validate version format (semantic versioning)
if ! [[ $NEW_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo -e "${RED}❌ Error: Invalid version format. Use semantic versioning (e.g., 1.0.1)${NC}"
    exit 1
fi

echo -e "${YELLOW}Releasing version: $NEW_VERSION${NC}"

# Update composer.json version
sed -i "s/\"version\": \"$CURRENT_VERSION\"/\"version\": \"$NEW_VERSION\"/" composer.json
echo -e "${GREEN}✅ Updated composer.json version to $NEW_VERSION${NC}"

# Update CHANGELOG.md
TODAY=$(date +%Y-%m-%d)
sed -i "s/## \[Unreleased\]/## \[Unreleased\]\n\n## \[$NEW_VERSION\] - $TODAY\n\n### Added\n- Release $NEW_VERSION\n\n### Changed\n- Package updated for Packagist distribution\n\n## \[$CURRENT_VERSION\]/" CHANGELOG.md
echo -e "${GREEN}✅ Updated CHANGELOG.md${NC}"

# Update README.md version
sed -i "s/**Version:** $CURRENT_VERSION/**Version:** $NEW_VERSION/" README.md
echo -e "${GREEN}✅ Updated README.md version${NC}"

# Update INSTALLATION.md version
sed -i "s/**Version:** $CURRENT_VERSION/**Version:** $NEW_VERSION/" INSTALLATION.md
echo -e "${GREEN}✅ Updated INSTALLATION.md version${NC}"

# Commit changes
git add .
git commit -m "Release version $NEW_VERSION"
echo -e "${GREEN}✅ Committed version $NEW_VERSION${NC}"

# Create tag
git tag -a "v$NEW_VERSION" -m "Release version $NEW_VERSION"
echo -e "${GREEN}✅ Created tag v$NEW_VERSION${NC}"

# Push changes and tag
git push origin main
git push origin "v$NEW_VERSION"
echo -e "${GREEN}✅ Pushed changes and tag to remote${NC}"

echo ""
echo -e "${GREEN}🎉 Release $NEW_VERSION completed successfully!${NC}"
echo ""
echo "Next steps:"
echo "1. Wait for Packagist to auto-update (usually takes a few minutes)"
echo "2. Verify the new version appears on Packagist"
echo "3. Test installation with: composer require laravelwudel/laravelwudel-notif:$NEW_VERSION"
echo ""
echo -e "${YELLOW}Note: Packagist will automatically detect the new tag and update the package${NC}"
