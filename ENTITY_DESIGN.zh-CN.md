# 实体设计说明

本模块包含两个主要的 Doctrine 实体：

## DailyReport（统计日报）

- 表名：`ims_statistics_daily_report`
- 字段：
  - `id`：整型，主键
  - `reportDate`：字符串，报表日期（YYYY-MM-DD）
  - `metrics`：一对多，关联 DailyMetric
  - `extraData`：json，额外数据
  - `createTime`：datetime，创建时间
  - `updateTime`：datetime，更新时间

### 设计说明
- 每条报表数据对应一天的统计信息。
- 指标以 DailyMetric 集合的形式存储，便于扩展。
- 额外数据以 JSON 格式存储，提升灵活性。

## DailyMetric（日报指标）

- 表名：`ims_statistics_daily_metric`
- 字段：
  - `id`：整型，主键
  - `report`：多对一，关联 DailyReport
  - `metricId`：字符串，指标唯一标识
  - `metricName`：字符串，指标名称
  - `metricUnit`：字符串，指标单位（可选）
  - `category`：字符串，指标分类（可选）
  - `value`：浮点型，指标值
  - `createTime`：datetime，创建时间
  - `updateTime`：datetime，更新时间

### 设计说明
- 每个指标归属于一份日报，且在同一日报内通过 `metricId` 唯一标识。
- 支持多种类型的值（最终归一为 float 存储）。
- 便于与自定义指标提供者集成，扩展性强。
