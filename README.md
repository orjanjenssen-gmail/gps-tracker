# GPS Tracker – WordPress Plugin

A lightweight WordPress plugin that receives GPS data via REST API and displays positions on interactive maps.

Designed for OwnTracks and custom API clients.

---

## ✨ Features

- REST endpoint for GPS ingestion
- Hybrid authentication:
  - API key (X-GPS-API-Key header)
  - HTTP Basic Auth (OwnTracks compatible)
- Rate limiting per authentication type
- GPS data filtering (accuracy + jitter protection)
- Speed and altitude storage
- Interactive Leaflet map display
- Multiple map providers:
  - OpenStreetMap
  - MapTiler
  - Esri Satellite
  - OpenTopoMap
- Debug logging
- Statistics foundation (distance, max speed, altitude)

---

## 📦 Installation

### Option 1 – Git (Recommended)

```bash
cd wp-content/plugins
git clone https://github.com/orjanjenssen-gmail/gps-tracker.git
