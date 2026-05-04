# RailFlow API Endpoints for JMeter

Use these endpoints when creating a JMeter test plan for performance testing. Replace `{{BASE_URL}}` with the target host, for example `http://localhost:8000/api`.

## Authentication
- `GET /api/user`
  - Requires `auth:sanctum` middleware.
  - Returns authenticated user details.

## IoT Data
- `POST /api/iot/history`
  - Ingest IoT sensor history data.
- `GET /api/iot/latest`
  - Retrieve the latest IoT sensor reading.
- `POST /api/settings/update`
  - Update IoT-related settings.
- `GET /api/iot/nearest-station`
  - Get the nearest station based on current sensor data.

## Notifications
- `POST /api/notifications/enroll`
  - Register a device or user for notifications.
- `GET /api/notifications`
  - List notifications for the authenticated user.
- `POST /api/notifications/{id}/read`
  - Mark a notification as read.

## Disaster History
- `GET /api/disaster-history`
  - Retrieve disaster history records.
- `POST /api/disaster-history`
  - Create a new disaster history record.

## Object Detection
- `POST /api/object-detection`
  - Submit object detection alert data.

## CAPE (Context-Aware Prompt Engine)
- `GET /api/cape/assess`
  - Run a safety assessment using the CAPE engine.
- `POST /api/cape/chat`
  - Send a chat request to the CAPE system.

## JMeter Test Plan Notes
- Configure a Thread Group with the desired number of users and ramp-up period.
- Use `HTTP Request` samplers for each API endpoint.
- Add `Header Manager` entries for `Accept: application/json` and `Content-Type: application/json` as needed.
- For authenticated endpoints, add an `Authorization` header with the bearer token or session cookie.
- Use `JSON Extractor` to capture dynamic values for chained requests.
- Add `View Results Tree`, `Aggregate Report`, and `Summary Report` listeners for performance analysis.
