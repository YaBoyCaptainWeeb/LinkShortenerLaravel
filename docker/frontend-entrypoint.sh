#!/bin/sh
set -eu

cd /var/www/html

ready_file=/tmp/frontend-ready
lock_marker=node_modules/.package-lock.sha256

rm -f "$ready_file"

if [ ! -f package-lock.json ]; then
    echo "[assets] package-lock.json is missing; refusing to install non-reproducible dependencies." >&2
    exit 1
fi

lock_hash="$(sha256sum package-lock.json | awk '{print $1}')"
installed_lock_hash=""

if [ -f "$lock_marker" ]; then
    installed_lock_hash="$(cat "$lock_marker")"
fi

if [ ! -x node_modules/.bin/vite ] || [ "$installed_lock_hash" != "$lock_hash" ]; then
    echo "[assets] Installing frontend dependencies..."
    npm ci
    printf '%s\n' "$lock_hash" > "$lock_marker"
else
    echo "[assets] Frontend dependencies are up to date."
fi

echo "[assets] Removing the previous frontend build..."
rm -rf public/build

echo "[assets] Running the initial frontend build..."
npm run build

touch "$ready_file"
echo "[assets] Initial build completed; watching frontend sources..."

exec npm run build -- --watch
