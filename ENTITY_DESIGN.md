# Entity Design

This module contains two main Doctrine entities:

## DailyReport

- Table: `ims_statistics_daily_report`
- Fields:
  - `id`: int, primary key
  - `reportDate`: string, report date (YYYY-MM-DD)
  - `metrics`: one-to-many, relation to DailyMetric
  - `extraData`: json, additional data
  - `createTime`: datetime, created at
  - `updateTime`: datetime, updated at

### Design Notes
- Each report represents a day's statistics.
- Metrics are stored as a collection of DailyMetric objects.
- Extra data can be stored as a JSON blob for extensibility.

## DailyMetric

- Table: `ims_statistics_daily_metric`
- Fields:
  - `id`: int, primary key
  - `report`: many-to-one, relation to DailyReport
  - `metricId`: string, metric identifier
  - `metricName`: string, metric name
  - `metricUnit`: string, metric unit (optional)
  - `category`: string, metric category (optional)
  - `value`: float, metric value
  - `createTime`: datetime, created at
  - `updateTime`: datetime, updated at

### Design Notes
- Each metric belongs to a report and is uniquely identified by `metricId` within the report.
- Supports flexible value types (float, bool, etc., normalized to float).
- Designed for extensibility and integration with custom metric providers.
