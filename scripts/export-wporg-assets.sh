#!/usr/bin/env bash
# Export WordPress.org listing assets from info/ sources.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
INFO="${ROOT}/info"
OUT="${ROOT}/.wordpress-org/assets"

mkdir -p "${OUT}"

cp "${INFO}/icon.png" "${ROOT}/icon.png"

sips -z 128 128 "${INFO}/icon.png" --out "${OUT}/icon-128x128.png" >/dev/null
sips -z 256 256 "${INFO}/icon.png" --out "${OUT}/icon-256x256.png" >/dev/null
sips -z 250 772 "${INFO}/banner.png" --out "${OUT}/banner-772x250.png" >/dev/null
sips -z 500 1544 "${INFO}/banner.png" --out "${OUT}/banner-1544x500.png" >/dev/null

echo "Wrote assets to ${OUT}"
