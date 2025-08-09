#!/bin/bash
set -e

echo "Starting Offline Agent Setup..."

# Create directories
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
CACHE_DIR="$PROJECT_ROOT/offline-cache"
OFFLINE_ASSETS_DIR="$PROJECT_ROOT/offline-assets"

mkdir -p "$CACHE_DIR"
mkdir -p "$OFFLINE_ASSETS_DIR"
mkdir -p "$OFFLINE_ASSETS_DIR/browsers"

cd "$PROJECT_ROOT"

# Function to check if a command exists
command_exists() {
  command -v "$1" >/dev/null 2>&1
}

# Check if Node.js is installed
if ! command_exists node; then
  echo "Error: Node.js is not installed. Please install Node.js before running this script."
  exit 1
fi

# Check if npm is installed
if ! command_exists npm; then
  echo "Error: npm is not installed. Please install npm before running this script."
  exit 1
fi

# Check if Docker is installed (optional but recommended)
if command_exists docker; then
  echo "Docker is installed. Will set up Docker image for offline usage."
  USE_DOCKER=true
else
  echo "Docker is not installed. Will set up npm packages for offline usage."
  USE_DOCKER=false
fi

# Install project dependencies
echo "Installing project dependencies..."
npm ci

# Install Playwright globally to ensure it's available
echo "Installing Playwright..."
npm install -g playwright
npx playwright install

# Install Playwright MCP
echo "Installing Playwright MCP..."
npm install -g @playwright/mcp

# Cache Playwright MCP for offline use
echo "Caching Playwright MCP for offline use..."
npx @playwright/mcp@latest --help

# If Docker is available, build and save the Docker image
if [ "$USE_DOCKER" = true ]; then
  echo "Building and saving Playwright MCP Docker image..."
  
  # Create a simple Dockerfile
  cat > "$CACHE_DIR/Dockerfile" << EOF
FROM mcr.microsoft.com/playwright:v1.41.0-jammy
RUN npm install -g @playwright/mcp
ENTRYPOINT ["npx", "@playwright/mcp"]
EOF

  # Build the Docker image
  docker build -t playwright-mcp "$CACHE_DIR"
  
  # Save the Docker image to a tar file
  docker save playwright-mcp > "$OFFLINE_ASSETS_DIR/playwright-mcp.tar"
  
  echo "Docker image saved to $OFFLINE_ASSETS_DIR/playwright-mcp.tar"
fi

# Save the browser binaries
echo "Saving browser binaries for offline use..."
PLAYWRIGHT_BROWSERS_PATH="$HOME/.cache/ms-playwright"
if [ -d "$PLAYWRIGHT_BROWSERS_PATH" ]; then
  cp -r "$PLAYWRIGHT_BROWSERS_PATH"/* "$OFFLINE_ASSETS_DIR/browsers/"
fi

# Create a script to run tests offline
cat > "$PROJECT_ROOT/scripts/run-offline-tests.sh" << EOF
#!/bin/bash
set -e

SCRIPT_DIR="\$(cd "\$(dirname "\${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="\$(dirname "\$SCRIPT_DIR")"
OFFLINE_ASSETS_DIR="\$PROJECT_ROOT/offline-assets"

# Set Playwright browsers path to use our offline browsers
export PLAYWRIGHT_BROWSERS_PATH="\$OFFLINE_ASSETS_DIR/browsers"

# If Docker image is available, load it
if [ -f "\$OFFLINE_ASSETS_DIR/playwright-mcp.tar" ]; then
  echo "Loading Playwright MCP Docker image..."
  docker load < "\$OFFLINE_ASSETS_DIR/playwright-mcp.tar"
  
  # Start MCP server in Docker
  echo "Starting MCP server in Docker..."
  docker run -d --name playwright-mcp-server -p 8000:8000 playwright-mcp --port 8000
  
  # Wait for the server to start
  echo "Waiting for MCP server to start..."
  sleep 5
else
  # Start MCP server using cached npm package
  echo "Starting MCP server using npm..."
  npx @playwright/mcp --port 8000 &
  MCP_PID=\$!
  
  # Register cleanup on script exit
  trap 'kill \$MCP_PID' EXIT
  
  # Wait for the server to start
  echo "Waiting for MCP server to start..."
  sleep 5
fi

# Run the tests
cd "\$PROJECT_ROOT/tests"
npx playwright test

# If Docker container is running, stop it
if docker ps | grep -q "playwright-mcp-server"; then
  echo "Stopping Docker container..."
  docker stop playwright-mcp-server
  docker rm playwright-mcp-server
fi

echo "Tests completed!"
EOF

chmod +x "$PROJECT_ROOT/scripts/run-offline-tests.sh"

# Create the agents.md file with instructions
echo "Creating agent setup instructions..."

echo "Offline setup complete! The offline-assets directory contains:"
ls -la "$OFFLINE_ASSETS_DIR"

echo "Use the './scripts/run-offline-tests.sh' script to run tests in an offline environment."