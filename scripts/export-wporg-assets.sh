#!/usr/bin/env bash
# Export WordPress.org listing assets for 4wp-weather.
# Sources: assets/images/ (preferred) or info/ (legacy). Output: .wordpress-org/assets/
# Upload output to SVN assets/ — not the plugin ZIP.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="${ROOT}/.wordpress-org/assets"

if [[ -f "${ROOT}/assets/images/icon.png" && -f "${ROOT}/assets/images/banner.png" ]]; then
	SRC="${ROOT}/assets/images"
elif [[ -f "${ROOT}/info/icon.png" && -f "${ROOT}/info/banner.png" ]]; then
	SRC="${ROOT}/info"
else
	echo "No source PNGs found." >&2
	echo "  Expected: assets/images/icon.png + banner.png  OR  info/icon.png + banner.png" >&2
	echo "" >&2
	if [[ -f "${OUT}/icon-256x256.png" && -f "${OUT}/banner-772x250.png" ]]; then
		echo "Ready-to-upload exports already exist:" >&2
		echo "  ${OUT}/" >&2
		echo "Sync to SVN: rsync -av ${OUT}/ ~/wordpress.org/4wp-weather/assets/" >&2
		exit 0
	fi
	echo "Restore sources from git if needed:" >&2
	echo "  git show 941d808^:info/icon.png > info/icon.png" >&2
	echo "  git show 941d808^:info/banner.png > info/banner.png" >&2
	exit 1
fi

mkdir -p "${OUT}"

cp "${SRC}/icon.png" "${ROOT}/icon.png"

sips -z 128 128 "${SRC}/icon.png" --out "${OUT}/icon-128x128.png" >/dev/null
sips -z 256 256 "${SRC}/icon.png" --out "${OUT}/icon-256x256.png" >/dev/null
sips -z 250 772 "${SRC}/banner.png" --out "${OUT}/banner-772x250.png" >/dev/null
sips -z 500 1544 "${SRC}/banner.png" --out "${OUT}/banner-1544x500.png" >/dev/null

echo "Wrote assets to ${OUT}"
echo "Next: rsync to SVN assets/ (see docs/wporg/SVN-DEPLOY.md)"
