# statistics-bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)
[![Build Status](https://img.shields.io/travis/tourze/statistics-bundle/master.svg?style=flat-square)](https://travis-ci.org/tourze/statistics-bundle)
[![Quality Score](https://img.shields.io/scrutinizer/g/tourze/statistics-bundle.svg?style=flat-square)](https://scrutinizer-ci.com/g/tourze/statistics-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/statistics-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/statistics-bundle)

一个用于平台统计的 Symfony Bundle，支持灵活的日报指标管理、自动生成统计表、指标扩展与自定义，适用于多维度统计分析场景。

## 功能特性

- 支持每日统计报表自动生成与管理
- 灵活的指标扩展机制，支持自定义指标提供者
- 基于 Doctrine ORM 的实体设计，支持多维度、多分类统计
- 命令行工具自动生成统计报表与数据表
- 与 tourze 相关基础组件深度集成

## 安装说明

- PHP >= 8.1
- Symfony >= 6.4
- Doctrine ORM >= 2.20
- 通过 Composer 安装：

```bash
composer require tourze/statistics-bundle
```

## 快速开始

1. 注册 Bundle 至 Symfony：
2. 配置数据库连接与相关依赖
3. 实现自定义指标提供者（实现 `MetricProviderInterface`）
4. 使用命令行工具生成每日统计报表：

```bash
php bin/console app:statistics:generate-daily-report --date=2024-04-27
```

## 详细文档

- API 说明请参考代码注释
- 可通过实现 `MetricProviderInterface` 扩展自定义指标
- 支持通过 `app:stats-table` 命令自动维护统计表结构

## 贡献指南

- 欢迎提交 Issue 与 PR
- 请遵循 PSR 规范与本项目代码风格
- 所有新功能需包含测试用例

## 版权和许可

- MIT License
- (c) tourze 团队

## 更新日志

- 详见 CHANGELOG.md 或 Git 提交历史
