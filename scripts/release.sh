#!/bin/bash

# LaravelWudel Notif Package Release Script
# This script ensures proper versioning and git tag management

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in a git repository
if [ ! -d ".git" ]; then
    print_error "This script must be run from a git repository root"
    exit 1
fi

# Check if working directory is clean
if [ -n "$(git status --porcelain)" ]; then
    print_error "Working directory is not clean. Please commit or stash changes first."
    git status --porcelain
    exit 1
fi

# Get current version from git tags
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "0.0.0")
print_status "Current version: $CURRENT_VERSION"

# Parse version components
IFS='.' read -ra VERSION_PARTS <<< "$CURRENT_VERSION"
MAJOR=${VERSION_PARTS[0]}
MINOR=${VERSION_PARTS[1]}
PATCH=${VERSION_PARTS[2]}

# Ask for new version
echo
echo "Current version: $CURRENT_VERSION"
echo "Choose version bump type:"
echo "1) Patch (1.0.3 -> 1.0.4)"
echo "2) Minor (1.0.3 -> 1.1.0)"
echo "3) Major (1.0.3 -> 2.0.0)"
echo "4) Custom version"
echo
read -p "Enter choice (1-4): " choice

case $choice in
    1)
        NEW_PATCH=$((PATCH + 1))
        NEW_VERSION="$MAJOR.$MINOR.$NEW_PATCH"
        print_status "Bumping patch version to: $NEW_VERSION"
        ;;
    2)
        NEW_MINOR=$((MINOR + 1))
        NEW_VERSION="$MAJOR.$NEW_MINOR.0"
        print_status "Bumping minor version to: $NEW_VERSION"
        ;;
    3)
        NEW_MAJOR=$((MAJOR + 1))
        NEW_VERSION="$NEW_MAJOR.0.0"
        print_status "Bumping major version to: $NEW_VERSION"
        ;;
    4)
        read -p "Enter custom version (e.g., 1.0.4): " NEW_VERSION
        if [[ ! $NEW_VERSION =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            print_error "Invalid version format. Use format: X.Y.Z"
            exit 1
        fi
        print_status "Using custom version: $NEW_VERSION"
        ;;
    *)
        print_error "Invalid choice"
        exit 1
        ;;
esac

# Confirm release
echo
print_warning "About to release version: $NEW_VERSION"
print_warning "This will:"
print_warning "1. Update CHANGELOG.md"
print_warning "2. Create git tag: v$NEW_VERSION"
print_warning "3. Push tag to remote"
echo
read -p "Continue? (y/N): " confirm

if [[ ! $confirm =~ ^[Yy]$ ]]; then
    print_status "Release cancelled"
    exit 0
fi

# Update CHANGELOG.md
print_status "Updating CHANGELOG.md..."
if [ -f "CHANGELOG.md" ]; then
    # Create backup
    cp CHANGELOG.md CHANGELOG.md.backup
    
    # Add new version entry
    sed -i "1i ## [$NEW_VERSION] - $(date +%Y-%m-%d)\n\n### Added\n- New release with improvements\n\n" CHANGELOG.md
    
    print_success "CHANGELOG.md updated"
else
    print_warning "CHANGELOG.md not found, skipping update"
fi

# Update README.md version
print_status "Updating README.md version..."
if [ -f "README.md" ]; then
    # Update version in README
    sed -i "s/Version: [0-9]\+\.[0-9]\+\.[0-9]\+/Version: $NEW_VERSION/g" README.md
    print_success "README.md version updated"
else
    print_warning "README.md not found, skipping update"
fi

# Commit changes
print_status "Committing changes..."
git add .
git commit -m "chore: prepare release v$NEW_VERSION"

# Create and push tag
print_status "Creating git tag: v$NEW_VERSION"
git tag -a "v$NEW_VERSION" -m "Release version $NEW_VERSION"

print_status "Pushing changes and tag..."
git push origin main
git push origin "v$NEW_VERSION"

print_success "Release v$NEW_VERSION completed successfully!"
echo
print_status "Next steps:"
print_status "1. Wait for Packagist to detect the new tag"
print_status "2. Verify package is available at: https://packagist.org/packages/laravelwudel/laravelwudel-notif"
print_status "3. Test installation: composer require laravelwudel/laravelwudel-notif:$NEW_VERSION"
echo
print_status "Package released: v$NEW_VERSION"
