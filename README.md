# statistics-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![Build Status](https://img.shields.io/travis/tourze/statistics-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/statistics-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/statistics-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/statistics-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)

A flexible Symfony bundle for platform statistics, supporting daily report metrics management, automatic stats table generation, and extensible/customizable metrics for multidimensional analysis.

## Features

- Auto-generate and manage daily statistics reports
- Flexible metric extension mechanism, support for custom metric providers
- Doctrine ORM entity design for multi-dimensional, multi-category statistics
- CLI tools for generating daily reports and maintaining stats tables
- Deep integration with tourze base components

## Installation

- PHP >= 8.1
- Symfony >= 6.4
- Doctrine ORM >= 2.20
- Install via Composer:

```bash
composer require tourze/statistics-bundle
```

## Quick Start

1. Register the bundle in Symfony
2. Configure database and dependencies
3. Implement custom metric providers (implement `MetricProviderInterface`)
4. Generate daily statistics reports via CLI:

```bash
php bin/console app:statistics:generate-daily-report --date=2024-04-27
```

## Documentation

- See code comments for API details
- Extend custom metrics by implementing `MetricProviderInterface`
- Use `app:stats-table` command to auto-maintain statistics table schema

## Contributing

- Issues and PRs are welcome
- Follow PSR standards and project code style
- All new features must include tests

## License

- MIT License
- (c) tourze team

## Changelog

- See CHANGELOG.md or git history
