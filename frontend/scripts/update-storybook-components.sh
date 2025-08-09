#!/bin/bash

# Function to set version in package.json
set_version() {
    local version=$1
    echo "Setting version to: $version"
    node -e "
        const fs = require('fs');
        const pkg = JSON.parse(fs.readFileSync('./package.json'));
        pkg.version = '$version';
        fs.writeFileSync('./package.json', JSON.stringify(pkg, null, 2));
    "
}

# Start verdaccio in the background and save its PID
echo "Starting verdaccio..."
verdaccio &
VERDACCIO_PID=$!

# Wait a few seconds for verdaccio to start
sleep 5

# Save current directory
CURRENT_DIR=$(pwd)

# Navigate up and to the storybook components directory
cd ../do-git-mis-components-storybook || exit 1

# Get latest version from repo and increment patch
echo "Getting latest version from Verdaccio..."
LATEST_VERSION=$(npm view @challenger/components --registry http://localhost:4873 version)
echo "Latest version from Verdaccio: $LATEST_VERSION"

# Increment patch version
NEW_VERSION=$(node -e "
    const [major, minor, patch] = '$LATEST_VERSION'.split('.');
    console.log(\`\${major}.\${minor}.\${parseInt(patch) + 1}\`);
")
echo "New version will be: $NEW_VERSION"

# Save current version from package.json
ORIGINAL_VERSION=$(node -p "require('./package.json').version")
echo "Saving original version: $ORIGINAL_VERSION"

# Set new version in package.json
set_version "$NEW_VERSION"

# Build the project
npm run build || { 
    echo "Build failed"
    set_version "$ORIGINAL_VERSION"
    kill $VERDACCIO_PID
    exit 1
}

# Ensure we're logged in to Verdaccio
echo "Checking Verdaccio login status..."
npm whoami --registry http://localhost:4873 > /dev/null 2>&1 || {
    echo "Not logged in to Verdaccio. Please login:"
    npm login --registry http://localhost:4873
}

# Temporarily update package.json for publishing
echo "Updating package.json for publishing..."
node -e "
    const fs = require('fs');
    const pkg = require('./package.json');
    pkg.publishConfig = { ...pkg.publishConfig, registry: 'http://localhost:4873' };
    fs.writeFileSync('./package.json', JSON.stringify(pkg, null, 2));
"

# Publish to verdaccio with new version
echo "Publishing version $NEW_VERSION to Verdaccio..."
npm publish --registry http://localhost:4873 || { 
    echo "Publish failed. Restoring original version..."; 
    set_version "$ORIGINAL_VERSION"
    kill $VERDACCIO_PID
    exit 1; 
}

# Restore original version after successful publish
echo "Restoring original version..."
set_version "$ORIGINAL_VERSION"

# Return to original directory
cd "$CURRENT_DIR" || exit 1

echo "Installing package version $NEW_VERSION..."
# Install the specific version of the package
npm install @challenger/components@$NEW_VERSION --registry http://localhost:4873 --force

# Clean up: Kill verdaccio process
echo "Cleaning up: stopping verdaccio..."
kill $VERDACCIO_PID

echo "Update complete!" 