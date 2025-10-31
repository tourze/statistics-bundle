# statistics-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![License](https://img.shields.io/packagist/l/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg?style=flat-square)]()

A comprehensive Symfony bundle for statistics data collection, providing daily report generation, metrics collection, and automatic statistics table creation functionality.

This bundle offers a complete solution for statistics management in Symfony applications, featuring a flexible plugin-based architecture that supports custom metrics providers and automatic table schema management.

## Table of Contents

- [Features](#features)
- [Dependencies](#dependencies)
- [Installation](#installation)
- [Configuration](#configuration)
- [Quick Start](#quick-start)
- [Advanced Usage](#advanced-usage)
  - [Creating Custom Metric Providers](#creating-custom-metric-providers)
  - [Stats Table Management](#stats-table-management)
- [API Reference](#api-reference)
- [Contributing](#contributing)
- [License](#license)
- [Changelog](#changelog)

## Features

- **Daily Report Generation**: Automatically generate and manage daily statistics reports
- **Flexible Metrics System**: Plugin-based architecture with custom metric providers
- **Multi-dimensional Analysis**: Support for categorized metrics with various data types
- **CLI Tools**: Console commands for report generation and stats table management
- **Doctrine Integration**: Full ORM support with proper entity relationships
- **Async Processing**: Support for background processing via Symfony Messenger
- **Extensible Architecture**: Easy to extend with custom providers and processors

## Dependencies

- PHP >= 8.1
- Symfony >= 7.3
- Doctrine ORM >= 3.0
- nesbot/carbon >= 2.72
- tourze ecosystem packages (doctrine-helper, doctrine-timestamp-bundle, etc.)

## Installation

Install via Composer:

```bash
composer require tourze/statistics-bundle
```

## Configuration

Register the bundle in your Symfony application:

```php
// config/bundles.php
return [
    // ... other bundles
    StatisticsBundle\StatisticsBundle::class => ['all' => true],
];
```

The bundle works out of the box with minimal configuration. Services are automatically registered and tagged for dependency injection.

## Quick Start

1. **Register the bundle** in Symfony (if not auto-registered):
   ```php
   // config/bundles.php
   return [
       // ... other bundles
       StatisticsBundle\StatisticsBundle::class => ['all' => true],
   ];
   ```

2. **Run database migrations** to create required tables:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

3. **Generate daily reports** using the CLI command:
   ```bash
   # Generate report for yesterday (default)
   php bin/console app:statistics:generate-daily-report
   
   # Generate report for a specific date
   php bin/console app:statistics:generate-daily-report --date=2024-04-27
   
   # Force regenerate existing report
   php bin/console app:statistics:generate-daily-report --date=2024-04-27 --force
   ```

4. **Manage stats tables** with auto-generation:
   ```bash
   php bin/console app:stats-table
   ```

## Advanced Usage

### Creating Custom Metric Providers

Implement the `MetricProviderInterface` to create custom metrics:

```php
use Carbon\CarbonImmutable;
use StatisticsBundle\Metric\MetricProviderInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(MetricProviderInterface::SERVICE_TAG)]
class CustomMetricProvider implements MetricProviderInterface
{
    public function getMetricId(): string
    {
        return 'user_registrations';
    }
    
    public function getMetricName(): string
    {
        return 'Daily User Registrations';
    }
    
    public function getMetricDescription(): string
    {
        return 'Number of users registered on this day';
    }
    
    public function getMetricUnit(): string
    {
        return 'users';
    }
    
    public function getCategory(): string
    {
        return 'user_activity';
    }
    
    public function getCategoryOrder(): int
    {
        return 10;
    }
    
    public function getMetricValue(CarbonImmutable $date): mixed
    {
        // Your custom logic here - query database, call APIs, etc.
        return $this->userRepository->countRegistrationsForDate($date);
    }
}
```

The metrics provider will be automatically registered when you tag it with `MetricProviderInterface::SERVICE_TAG`.

### Stats Table Management

The bundle provides automatic statistics table creation and management through the `AsStatsColumn` attribute:

```php
use StatisticsBundle\Attribute\AsStatsColumn;
use StatisticsBundle\Enum\StatTimeDimension;
use StatisticsBundle\Enum\StatType;

class MyEntity
{
    #[AsStatsColumn(timeDimension: StatTimeDimension::DAILY_NEW, statsType: StatType::COUNT)]
    private int $dailyCount;
    
    #[AsStatsColumn(timeDimension: StatTimeDimension::MONTHLY_TOTAL, statsType: StatType::SUM, name: 'monthly_revenue')]
    private float $revenue;
}
```

Then run the stats table command to create/update tables:

```bash
php bin/console app:stats-table
```

### Working with Daily Reports

The bundle provides a service for programmatic access to daily reports:

```php
use StatisticsBundle\Service\DailyReportService;

class MyController
{
    public function __construct(
        private DailyReportService $dailyReportService
    ) {}
    
    public function getReports()
    {
        // Get a specific date report
        $report = $this->dailyReportService->getDailyReport('2024-04-27');
        
        // Get reports for a date range
        $reports = $this->dailyReportService->getDailyReportsByDateRange(
            '2024-04-01', 
            '2024-04-30'
        );
        
        // Get recent reports (last 7 days by default)
        $recentReports = $this->dailyReportService->getRecentDailyReports(30);
        
        // Create or update a report programmatically
        $metricsData = [
            'user_count' => ['name' => 'User Count', 'value' => 150, 'unit' => 'users'],
            'revenue' => ['name' => 'Daily Revenue', 'value' => 1250.50, 'unit' => 'USD']
        ];
        $report = $this->dailyReportService->createOrUpdateDailyReport(
            '2024-04-27',
            $metricsData,
            ['extra' => 'metadata']
        );
    }
}
```

### Entities and Data Structure

The bundle provides the following main entities:

- **DailyReport**: Represents a daily statistics report with date and metrics
- **DailyMetric**: Individual metrics within a daily report with name, value, unit, and category
- **StatTimeDimension**: Enum for time dimensions (DAILY_NEW, DAILY_TOTAL, WEEKLY_NEW, etc.)
- **StatType**: Enum for statistic types (COUNT, SUM, AVG, etc.)
- **AsStatsColumn**: Attribute for marking entity properties for automatic stats table generation

## API Reference

### Console Commands

- `app:statistics:generate-daily-report`: Generate daily statistics reports with options for date and force refresh
- `app:stats-table`: Automatically create and maintain statistics tables based on entity annotations

### Services

- `DailyReportService`: Main service for daily report CRUD operations and metric provider management
- `CreateTableStatsHandler`: Async message handler for statistics table data population

### Interfaces and Attributes

- `MetricProviderInterface`: Interface for implementing custom metric providers
- `DailyReportRepositoryInterface`: Repository interface for daily reports
- `AsStatsColumn`: Attribute for marking entity properties for automatic statistics tracking

## Contributing

We welcome contributions! Please follow these guidelines:

- Follow PSR-12 coding standards
- Write comprehensive tests for new features
- Update documentation for API changes
- Use semantic versioning for releases

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Changelog

For a detailed list of changes, please refer to the [CHANGELOG.md](CHANGELOG.md) file or the Git commit history.
