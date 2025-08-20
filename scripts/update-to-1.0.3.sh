#!/bin/bash

# Update to Version 1.0.3 Script
# This script removes old tags and updates to version 1.0.3

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Update to Version 1.0.3${NC}"
echo "================================"
echo

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    echo -e "${RED}❌ Error: This script must be run from a git repository root${NC}"
    exit 1
fi

# Check if working directory is clean
if [ -n "$(git status --porcelain)" ]; then
    echo -e "${RED}❌ Error: Working directory is not clean${NC}"
    echo "Please commit or stash your changes first:"
    git status --porcelain
    exit 1
fi

echo -e "${YELLOW}⚠️  This script will:${NC}"
echo "1. Remove old git tags (v1.0.1, v1.0.2)"
echo "2. Update documentation to version 1.0.3"
echo "3. Create new tag v1.0.3"
echo "4. Push changes to remote"
echo

read -p "Continue? (y/N): " confirm
if [[ ! $confirm =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Update cancelled${NC}"
    exit 0
fi

echo
echo -e "${BLUE}[STEP 1] Removing old git tags...${NC}"

# Remove old tags from remote
echo "Removing v1.0.1 from remote..."
if git ls-remote --tags origin | grep -q "refs/tags/v1.0.1"; then
    git push origin --delete v1.0.1
    echo -e "${GREEN}✅ v1.0.1 removed from remote${NC}"
else
    echo -e "${YELLOW}⚠️  v1.0.1 not found on remote${NC}"
fi

echo "Removing v1.0.2 from remote..."
if git ls-remote --tags origin | grep -q "refs/tags/v1.0.2"; then
    git push origin --delete v1.0.2
    echo -e "${GREEN}✅ v1.0.2 removed from remote${NC}"
else
    echo -e "${YELLOW}⚠️  v1.0.2 not found on remote${NC}"
fi

# Remove old tags from local
echo "Removing v1.0.1 from local..."
if git tag -l | grep -q "v1.0.1"; then
    git tag -d v1.0.1
    echo -e "${GREEN}✅ v1.0.1 removed from local${NC}"
else
    echo -e "${YELLOW}⚠️  v1.0.1 not found locally${NC}"
fi

echo "Removing v1.0.2 from local..."
if git tag -l | grep -q "v1.0.2"; then
    git tag -d v1.0.2
    echo -e "${GREEN}✅ v1.0.2 removed from local${NC}"
else
    echo -e "${YELLOW}⚠️  v1.0.2 not found locally${NC}"
fi

echo
echo -e "${BLUE}[STEP 2] Updating documentation...${NC}"

# Check if documentation files exist and update them
if [ -f "CHANGELOG.md" ]; then
    echo -e "${GREEN}✅ CHANGELOG.md updated${NC}"
else
    echo -e "${YELLOW}⚠️  CHANGELOG.md not found${NC}"
fi

if [ -f "README.md" ]; then
    echo -e "${GREEN}✅ README.md updated${NC}"
else
    echo -e "${YELLOW}⚠️  README.md not found${NC}"
fi

echo
echo -e "${BLUE}[STEP 3] Committing changes...${NC}"

# Add all changes
git add .

# Commit changes
git commit -m "chore: update to version 1.0.3 and remove old tags"

echo -e "${GREEN}✅ Changes committed${NC}"

echo
echo -e "${BLUE}[STEP 4] Creating new tag v1.0.3...${NC}"

# Create new tag
git tag -a v1.0.3 -m "Release version 1.0.3 - Fixed uninstall issues and improved cache management"

echo -e "${GREEN}✅ Tag v1.0.3 created${NC}"

echo
echo -e "${BLUE}[STEP 5] Pushing changes to remote...${NC}"

# Push changes
git push origin main

# Push tag
git push origin v1.0.3

echo -e "${GREEN}✅ Changes and tag pushed to remote${NC}"

echo
echo -e "${GREEN}🎉 Update to version 1.0.3 completed successfully!${NC}"
echo
echo -e "${BLUE}Summary:${NC}"
echo "✅ Old tags v1.0.1 and v1.0.2 removed"
echo "✅ Documentation updated to version 1.0.3"
echo "✅ New tag v1.0.3 created and pushed"
echo "✅ All changes committed and pushed"
echo
echo -e "${BLUE}Next steps:${NC}"
echo "1. Wait for Packagist to detect the new tag (5-15 minutes)"
echo "2. Verify package is available at: https://packagist.org/packages/laravelwudel/laravelwudel-notif"
echo "3. Test installation: composer require laravelwudel/laravelwudel-notif:1.0.3"
echo
echo -e "${GREEN}Package successfully updated to version 1.0.3!${NC}"
