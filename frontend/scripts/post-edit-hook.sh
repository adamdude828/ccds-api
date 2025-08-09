#!/bin/bash

# Post-edit hook for TypeScript and linting checks
# This script runs after file edits to ensure code quality

echo "🔍 Running post-edit checks..."

# Run TypeScript type checking
echo "📘 Checking TypeScript types..."
if npm run verify > /dev/null 2>&1; then
    echo "✅ TypeScript check passed"
else
    echo "❌ TypeScript errors found"
    npm run verify
    exit 1
fi

# Run ESLint
echo "🔧 Running ESLint..."
if npm run lint > /dev/null 2>&1; then
    echo "✅ Lint check passed"
else
    echo "❌ Lint errors found"
    npm run lint
    exit 1
fi

echo "✨ All checks passed!"