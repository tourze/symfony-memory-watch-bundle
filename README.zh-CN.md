# Symfony 内存监控扩展包

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-memory-watch-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-memory-watch-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-memory-watch-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-memory-watch-bundle)

一个用于监控和记录Symfony应用程序请求生命周期内内存使用情况的扩展包。

## 功能特性

- 监控请求开始到结束之间的内存使用情况
- 当内存使用超过定义的阈值时记录警告日志
- 无需配置即可立即使用
- 与Symfony的PSR兼容日志系统无缝集成
- 轻量级设计，对性能影响极小

## 安装方法

```bash
composer require tourze/symfony-memory-watch-bundle
```

### 注册扩展包

对于使用Flex的Symfony应用程序，扩展包应该会被自动注册。否则，需要手动将其添加到`config/bundles.php`文件中：

```php
<?php

return [
    // ...
    Tourze\MemoryWatchBundle\MemoryWatchBundle::class => ['all' => true],
];
```

## 工作原理

该扩展包使用事件订阅器来：

1. 在请求开始时记录内存使用情况
2. 在请求结束时测量内存使用情况
3. 当内存使用差值超过配置的阈值时，记录警告日志

内存使用数据通过PHP的`memory_get_usage()`函数收集。扩展包使用`WeakMap`将内存测量值与请求对象相关联，确保不会发生内存泄漏。

## 系统要求

- PHP 8.1+
- Symfony 6.4+
- PSR兼容的日志记录器

## 贡献指南

欢迎贡献！请随时提交拉取请求。

## 许可证

MIT许可证。更多信息请查看[许可证文件](LICENSE)。
