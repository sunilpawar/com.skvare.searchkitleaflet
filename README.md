# SearchKit Leaflet Map

**CiviCRM Extension** — `com.skvare.searchkitleaflet`

Adds an interactive **Leaflet map** display type to CiviCRM's SearchKit.
Plot any contact, contribution, event, or custom-entity search on a zoomable
map with clustered markers, token-based popups, and optional viewport
filtering.

---

## Features

| Feature | Detail |
|---|---|
| Leaflet map display | Works as a native SearchKit display type |
| Colour-coded markers | Colour by any field value (contact_type, membership_status, etc.) |
| Marker clustering | Groups nearby markers via `leaflet.markercluster` |
| Token popups | Shows selected columns inside the popup, plus a "View contact" link |
| Viewport filtering | Re-queries the API when the user pans or zooms |
| Admin settings panel | Full column-mapping & options UI inside SearchKit |
| Tile provider choice | OpenStreetMap, Carto Light/Dark, Stadia Toner |

---

## Requirements

| Requirement | Version |
|---|---|
| CiviCRM | 5.47 or later |
| Extension | `org.civicrm.search_kit` (bundled with CiviCRM) |
| PHP | 7.4+ |
| Geocoded addresses | Contacts must have `geo_code_1` / `geo_code_2` populated |

---

## Installation

### 1. Download or clone

```bash
cd <civicrm-extensions-dir>
git clone https://github.com/skvare/com.skvare.searchkitleaflet
```

### 2. Download vendor JS/CSS assets

```bash
cd com.skvare.searchkitleaflet
bash bin/setup-assets.sh
```

This downloads **Leaflet 1.9.4** and **leaflet.markercluster 1.5.3** into
`ext/leaflet/`. No npm or build step is required.

### 3. Enable the extension in CiviCRM

**Administer → System Settings → Manage Extensions**
Find *SearchKit Leaflet Map* and click **Install**.

---

## Usage

### Creating a Leaflet Map display

1. Go to **Search → SearchKit**.
2. Open an existing saved search or create a new one.
3. Click **Add display** → choose **Leaflet Map**.
4. In the **Display settings** sidebar:

   | Setting | What to set |
   |---|---|
   | **Latitude column** | `address_primary.geo_code_1` (default) |
   | **Longitude column** | `address_primary.geo_code_2` (default) |
   | **Popup label** | `display_name` or any text column |
   | **Popup subtitle** | `address_primary.city` or similar |
   | **Colour by field** | `contact_type`, `membership_status`, custom field… |

5. Add colour rules for each field value (e.g. `Individual → #2a6496`).
6. Save the display.

### Geocoding contacts

Addresses are geocoded automatically when:

- CiviCRM is configured with a geocoding provider  
  (**Administer → Administration Console → Mapping and Geocoding**).
- An address is saved or bulk-geocoded via  
  **Administer → Administration Console → Geocode and Parse Addresses**.

---

## Column mapping cheat sheet

| Purpose | SearchKit column name |
|---|---|
| Latitude | `address_primary.geo_code_1` |
| Longitude | `address_primary.geo_code_2` |
| Contact name | `display_name` |
| City | `address_primary.city` |
| State | `address_primary.state_province_id:label` |
| Email | `email_primary.email` |
| Phone | `phone_primary.phone` |
| Membership status | `Memberships_Contact_contact_id_01.status_id:label` |

---

## Tile providers

| Key | Provider | Notes |
|---|---|---|
| `osm` | OpenStreetMap | Free, no API key needed |
| `carto_light` | Carto Light | Free tier available |
| `carto_dark` | Carto Dark | Free tier available |
| `stadia_toner` | Stadia Toner | Free tier available |

---

## Running tests

```bash
cd <civicrm-root>
phpunit \
  --bootstrap CRM/Core/ClassLoader.php \
  --filter CRM_SearchKitLeaflet_DisplayTypeTest
```

---

## File structure

```
com.skvare.searchkitleaflet/
├── info.xml                          Extension metadata
├── searchkitleaflet.php              CiviCRM hooks
├── searchkitleaflet.civix.php        Civix helpers
├── Civi/SearchKitLeaflet/
│   └── DisplayType.php               Settings normaliser / tile helper
├── ang/crmSearchDisplayLeaflet/
│   ├── crmSearchDisplayLeaflet.module.js          Angular module
│   ├── crmSearchDisplayLeaflet.component.js       Map display component
│   ├── crmSearchDisplayLeaflet.html               Map template
│   ├── crmSearchDisplayLeafletSettings.component.js  Admin settings
│   ├── crmSearchDisplayLeafletSettings.html          Admin template
│   └── crmSearchDisplayLeaflet.css               Styles
├── ext/leaflet/                      Vendored JS/CSS (run setup-assets.sh)
├── bin/
│   └── setup-assets.sh               Asset downloader
└── tests/phpunit/CRM/SearchKitLeaflet/
    └── DisplayTypeTest.php           PHPUnit tests
```

---

## License

AGPL-3.0 — © [Skvare](https://skvare.com)
