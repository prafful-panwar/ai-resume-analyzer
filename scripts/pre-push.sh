#!/bin/bash

# 1. Enforce Branch Naming Rules
BRANCH=$(git rev-parse --abbrev-ref HEAD)
VALID_BRANCH_REGEX="^(feature|fix|update|hotfix|chore|docs|refactor|perf|test|build)\/.*"

if [[ ! $BRANCH =~ $VALID_BRANCH_REGEX ]] && [ "$BRANCH" != "main" ]; then
    echo "❌ [ERROR] Invalid branch name: '$BRANCH'"
    echo "Branch names must start with: feature/, fix/, update/, hotfix/, chore/, docs/, refactor/, perf/, test/, or build/ (or be 'main')."
    exit 1
fi

# 2. Run Code Quality Checks (code-shield)
echo "🚀 [PUSH CHECK] Running code-shield..."
composer code-shield

if [ $? -ne 0 ]; then
    echo "❌ [ERROR] code-shield failed. Please fix the issues before pushing."
    exit 1
fi

echo "✅ [SUCCESS] All checks passed. Pushing..."
exit 0
