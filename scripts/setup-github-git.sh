#!/usr/bin/env bash
set -euo pipefail

REPO_DIR="$(cd "$(dirname "$0")/.." && pwd)"
BRANCH="resty-branch"
REMOTE_SSH="git@github.com:redimatrading-trading/bicol-procurement.git"
REMOTE_HTTPS="https://github.com/redimatrading-trading/bicol-procurement.git"
SSH_KEY="$HOME/.ssh/id_ed25519"
SSH_PUB="${SSH_KEY}.pub"

echo "==> Bicol Procurement Git setup (${BRANCH})"
echo "Repository: ${REPO_DIR}"
echo

cd "$REPO_DIR"

if [[ "$(git branch --show-current)" != "$BRANCH" ]]; then
  if git show-ref --verify --quiet "refs/heads/${BRANCH}"; then
    git checkout "$BRANCH"
  else
    git checkout -b "$BRANCH"
  fi
fi

echo "==> Current branch: $(git branch --show-current)"

if [[ ! -f "$SSH_PUB" ]]; then
  echo "==> Creating SSH key for GitHub..."
  mkdir -p "$HOME/.ssh"
  chmod 700 "$HOME/.ssh"
  ssh-keygen -t ed25519 -C "$(git config --global user.email 2>/dev/null || echo 'github-key')" -f "$SSH_KEY" -N ""
  chmod 600 "$SSH_KEY"
  chmod 644 "$SSH_PUB"
else
  echo "==> SSH key already exists: ${SSH_PUB}"
fi

if ! grep -q "Host github.com" "$HOME/.ssh/config" 2>/dev/null; then
  echo "==> Adding GitHub host to ~/.ssh/config"
  cat >> "$HOME/.ssh/config" <<'EOF'

Host github.com
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519
  AddKeysToAgent yes
  UseKeychain yes
EOF
  chmod 600 "$HOME/.ssh/config"
fi

eval "$(ssh-agent -s)" >/dev/null
ssh-add --apple-use-keychain "$SSH_KEY" 2>/dev/null || ssh-add "$SSH_KEY"

echo
echo "==> Add this SSH public key to GitHub (user: redimatrading-trading):"
echo "    https://github.com/settings/ssh/new"
echo
cat "$SSH_PUB"
echo
read -r -p "Press Enter after the SSH key is added to GitHub..."

git remote set-url origin "$REMOTE_SSH"
echo "==> Remote set to SSH: ${REMOTE_SSH}"

echo "==> Testing GitHub SSH connection..."
ssh -T git@github.com || true

echo "==> Pushing ${BRANCH} to origin..."
git push -u origin "$BRANCH"

echo
echo "Done. In Cursor:"
echo "  1. Reload window (Cmd+Shift+P -> Developer: Reload Window)"
echo "  2. Use Source Control to Commit / Push / Pull on ${BRANCH}"
