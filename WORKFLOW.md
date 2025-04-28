# Workflow (Statistics Bundle)

```mermaid
flowchart TD
    A[Start: Scheduled Task or CLI] --> B{Generate Daily Report?}
    B -- Yes --> C[Invoke GenerateDailyReportCommand]
    C --> D[DailyReportService: Fetch/Create DailyReport]
    D --> E[Get All Metric Providers]
    E --> F[For Each Provider: Calculate Metric Value]
    F --> G[Update/Add DailyMetric to DailyReport]
    G --> H[Persist to Database]
    H --> I[Show Report Summary]
    B -- No --> Z[End]
```

- Daily statistics can be generated automatically via scheduled tasks or manually via CLI.
- The service fetches all registered metric providers, calculates each metric, and persists the results.
- New metrics can be added by implementing MetricProviderInterface.
