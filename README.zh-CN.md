# statistics-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![License](https://img.shields.io/packagist/l/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg?style=flat-square)]()

一个用于统计数据收集的 Symfony Bundle，提供日报生成、指标收集、
自动统计表创建等功能。

该 Bundle 为 Symfony 应用提供了完整的统计管理解决方案，采用灵活的插件式架构，
支持自定义指标提供者和自动表结构管理。

## 目录

- [功能特性](#功能特性)
- [依赖要求](#依赖要求)
- [安装说明](#安装说明)
- [配置说明](#配置说明)
- [快速开始](#快速开始)
- [高级用法](#高级用法)
  - [创建自定义指标提供者](#创建自定义指标提供者)
  - [统计表管理](#统计表管理)
- [API 参考](#api-参考)
- [贡献指南](#贡献指南)
- [版权和许可](#版权和许可)
- [更新日志](#更新日志)

## 功能特性

- **日报生成**: 自动生成和管理每日统计报表
- **灵活指标系统**: 基于插件的架构，支持自定义指标提供者
- **多维度分析**: 支持分类指标和多种数据类型
- **命令行工具**: 控制台命令用于报表生成和统计表管理
- **Doctrine 集成**: 完整的 ORM 支持和恰当的实体关系
- **异步处理**: 通过 Symfony Messenger 支持后台处理
- **可扩展架构**: 易于通过自定义提供者和处理器扩展

## 依赖要求

- PHP >= 8.1
- Symfony >= 7.3
- Doctrine ORM >= 3.0
- nesbot/carbon >= 2.72
- tourze 生态系统相关包 (doctrine-helper, doctrine-timestamp-bundle 等)

## 安装说明

通过 Composer 安装：

```bash
composer require tourze/statistics-bundle
```

## 配置说明

在 Symfony 应用中注册 Bundle：

```php
// config/bundles.php
return [
    // ... 其他 bundles
    StatisticsBundle\StatisticsBundle::class => ['all' => true],
];
```

该 Bundle 开箱即用，无需额外配置。服务会自动注册并通过依赖注入进行标记。

## 快速开始

1. **注册 Bundle** 至 Symfony（如果未自动注册）：
   ```php
   // config/bundles.php
   return [
       // ... 其他 bundles
       StatisticsBundle\StatisticsBundle::class => ['all' => true],
   ];
   ```

2. **运行数据库迁移** 创建必需的表：
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

3. **生成日报** 使用 CLI 命令：
   ```bash
   # 生成昨天的报表（默认）
   php bin/console app:statistics:generate-daily-report
   
   # 生成指定日期的报表
   php bin/console app:statistics:generate-daily-report --date=2024-04-27
   
   # 强制重新生成现有报表
   php bin/console app:statistics:generate-daily-report --date=2024-04-27 --force
   ```

4. **管理统计表** 通过自动生成：
   ```bash
   php bin/console app:stats-table
   ```

## 高级用法

### 创建自定义指标提供者

实现 `MetricProviderInterface` 来创建自定义指标：

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
        return '每日用户注册量';
    }
    
    public function getMetricDescription(): string
    {
        return '当日注册的用户数量';
    }
    
    public function getMetricUnit(): string
    {
        return '人';
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
        // 你的自定义逻辑 - 查询数据库、调用API等
        return $this->userRepository->countRegistrationsForDate($date);
    }
}
```

### 统计表管理

该 Bundle 通过 `AsStatsColumn` 属性提供自动统计表创建和管理功能：

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

然后运行统计表命令来创建/更新表：

```bash
php bin/console app:stats-table
```

### 操作日报数据

该 Bundle 提供服务以便程序化访问日报数据：

```php
use StatisticsBundle\Service\DailyReportService;

class MyController
{
    public function __construct(
        private DailyReportService $dailyReportService
    ) {}
    
    public function getReports()
    {
        // 获取指定日期的报表
        $report = $this->dailyReportService->getDailyReport('2024-04-27');
        
        // 获取日期范围内的报表
        $reports = $this->dailyReportService->getDailyReportsByDateRange(
            '2024-04-01', 
            '2024-04-30'
        );
        
        // 获取最近几天的报表（默认7天）
        $recentReports = $this->dailyReportService->getRecentDailyReports(30);
        
        // 程序化创建或更新报表
        $metricsData = [
            'user_count' => ['name' => '用户数量', 'value' => 150, 'unit' => '人'],
            'revenue' => ['name' => '日收入', 'value' => 1250.50, 'unit' => '元']
        ];
        $report = $this->dailyReportService->createOrUpdateDailyReport(
            '2024-04-27',
            $metricsData,
            ['extra' => '元数据']
        );
    }
}
```

### 实体和数据结构

该 Bundle 提供以下主要实体：

- **DailyReport**: 表示包含日期和指标的每日统计报告
- **DailyMetric**: 日报中的单个指标，包含名称、值、单位和分类
- **StatTimeDimension**: 时间维度枚举（DAILY_NEW、DAILY_TOTAL、WEEKLY_NEW等）
- **StatType**: 统计类型枚举（COUNT、SUM、AVG等）
- **AsStatsColumn**: 用于标记实体属性以自动生成统计表的属性

## API 参考

### 控制台命令

- `app:statistics:generate-daily-report`: 生成每日统计报告，支持日期和强制刷新选项
- `app:stats-table`: 基于实体注解自动创建和维护统计表

### 服务

- `DailyReportService`: 用于日报 CRUD 操作和指标提供者管理的主要服务
- `CreateTableStatsHandler`: 用于统计表数据填充的异步消息处理器

### 接口和属性

- `MetricProviderInterface`: 实现自定义指标提供者的接口
- `DailyReportRepositoryInterface`: 日报的仓库接口
- `AsStatsColumn`: 用于标记实体属性进行自动统计跟踪的属性

## 贡献指南

我们欢迎贡献！请遵循以下指导原则：

- 遵循 PSR-12 编码标准
- 为新功能编写全面的测试
- 为 API 变更更新文档
- 使用语义化版本进行发布

## 版权和许可

本包采用 MIT 许可证。详情请参见 [LICENSE](LICENSE) 文件。

## 更新日志

详细的变更列表请参考 [CHANGELOG.md](CHANGELOG.md) 文件或 Git 提交历史。
