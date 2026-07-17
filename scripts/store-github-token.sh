#!/usr/bin/env bash
set -euo pipefail

USERNAME="redimatrading-trading"
REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"

echo "Store a GitHub Personal Access Token for user: ${USERNAME}"
echo "Create one here: https://github.com/settings/tokens (enable 'repo' scope)"
echo
read -r -s -p "Paste GitHub token: " TOKEN
echo

if [[ -z "${TOKEN}" ]]; then
  echo "No token provided."
  exit 1
fi

printf "protocol=https\nhost=github.com\nusername=${USERNAME}\npassword=${TOKEN}\n" | git credential-osxkeychain store

cd "$REPO_DIR"
git remote set-url origin "https://${USERNAME}@github.com/${USERNAME}/bicol-procurement.git"
git push -u origin resty-branch

echo "GitHub credentials saved and branch pushed."
