#!/usr/bin/env bash
# =============================================================
# bin/setup-assets.sh
#
# Downloads Leaflet 1.9.4 and leaflet.markercluster 1.5.3 into
# the ext/leaflet/ vendor directory.
#
# Run once after cloning / installing the extension:
#   bash bin/setup-assets.sh
# =============================================================

set -euo pipefail

EXT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEST="$EXT_DIR/ext/leaflet"
LEAFLET_VERSION="1.9.4"
CLUSTER_VERSION="1.5.3"

echo "==> Downloading Leaflet $LEAFLET_VERSION ..."
curl -fsSL "https://unpkg.com/leaflet@$LEAFLET_VERSION/dist/leaflet.js"  -o "$DEST/leaflet.js"
curl -fsSL "https://unpkg.com/leaflet@$LEAFLET_VERSION/dist/leaflet.css" -o "$DEST/leaflet.css"

# Leaflet marker images (used by the default icon)
mkdir -p "$DEST/images"
for IMG in marker-icon.png marker-icon-2x.png marker-shadow.png layers.png layers-2x.png; do
  curl -fsSL "https://unpkg.com/leaflet@$LEAFLET_VERSION/dist/images/$IMG" \
    -o "$DEST/images/$IMG"
done

echo "==> Downloading leaflet.markercluster $CLUSTER_VERSION ..."
curl -fsSL "https://unpkg.com/leaflet.markercluster@$CLUSTER_VERSION/dist/leaflet.markercluster.js" \
  -o "$DEST/leaflet.markercluster.js"
curl -fsSL "https://unpkg.com/leaflet.markercluster@$CLUSTER_VERSION/dist/MarkerCluster.css" \
  -o "$DEST/MarkerCluster.css"
curl -fsSL "https://unpkg.com/leaflet.markercluster@$CLUSTER_VERSION/dist/MarkerCluster.Default.css" \
  -o "$DEST/MarkerCluster.Default.css"

echo ""
echo "==> Done. Assets written to $DEST"
echo ""
echo "    leaflet.js                  $(wc -c < "$DEST/leaflet.js") bytes"
echo "    leaflet.css                 $(wc -c < "$DEST/leaflet.css") bytes"
echo "    leaflet.markercluster.js    $(wc -c < "$DEST/leaflet.markercluster.js") bytes"
