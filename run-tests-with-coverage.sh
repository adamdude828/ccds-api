#!/bin/bash

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Default values
COVERAGE_DIR="coverage-report"
GENERATE_HTML=true
MIN_COVERAGE=""

# Parse command line arguments
while [[ $# -gt 0 ]]; do
    case $1 in
        --no-html)
            GENERATE_HTML=false
            shift
            ;;
        --min=*)
            MIN_COVERAGE="${1#*=}"
            shift
            ;;
        --help)
            echo "Usage: $0 [options]"
            echo ""
            echo "Options:"
            echo "  --no-html        Don't generate HTML coverage report"
            echo "  --min=<percent>  Minimum coverage percentage required"
            echo "  --help           Show this help message"
            exit 0
            ;;
        *)
            echo "Unknown option: $1"
            exit 1
            ;;
    esac
done

echo -e "${GREEN}Running tests with code coverage...${NC}"
echo ""

# Build the test command
TEST_CMD="docker exec -e XDEBUG_MODE=coverage laravel_app_dev php artisan test --coverage"

# Add HTML coverage if requested
if [ "$GENERATE_HTML" = true ]; then
    TEST_CMD="$TEST_CMD --coverage-html=$COVERAGE_DIR"
fi

# Add minimum coverage if specified
if [ -n "$MIN_COVERAGE" ]; then
    TEST_CMD="$TEST_CMD --min=$MIN_COVERAGE"
fi

# Run the tests
$TEST_CMD

# If HTML coverage was generated, provide instructions to view it
if [ "$GENERATE_HTML" = true ] && [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}HTML coverage report generated!${NC}"
    echo ""
    echo "To view the coverage report:"
    echo "  1. Open the coverage report in your browser:"
    echo "     docker exec laravel_app_dev cat /var/www/html/$COVERAGE_DIR/index.html > coverage.html && open coverage.html"
    echo ""
    echo "  2. Or copy the entire report directory to your host:"
    echo "     docker cp laravel_app_dev:/var/www/html/$COVERAGE_DIR ./"
    echo ""
fi

echo -e "${YELLOW}Additional coverage options:${NC}"
echo "  --coverage-xml=<dir>     Generate XML coverage report"
echo "  --coverage-cobertura     Generate Cobertura XML report (for CI/CD)"
echo "  --coverage-text          Show detailed coverage in terminal"
echo ""
echo "Example commands:"
echo "  ./run-tests-with-coverage.sh --min=80"
echo "  ./run-tests-with-coverage.sh --no-html" 