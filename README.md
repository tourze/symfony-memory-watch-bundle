# Symfony Memory Watch Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/symfony-memory-watch-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-memory-watch-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/symfony-memory-watch-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/symfony-memory-watch-bundle)

A Symfony bundle that monitors and logs memory usage in your application during the request lifecycle.

## Features

- Monitors memory usage between request and response
- Logs warnings when memory usage exceeds defined thresholds
- Zero configuration required to get started
- Works with Symfony's PSR-compatible logging
- Lightweight with minimal performance impact

## Installation

```bash
composer require tourze/symfony-memory-watch-bundle
```

### Register the bundle

For Symfony applications using Flex, the bundle should be automatically registered. Otherwise, add it manually to your `config/bundles.php`:

```php
<?php

return [
    // ...
    Tourze\MemoryWatchBundle\MemoryWatchBundle::class => ['all' => true],
];
```

## Quick Start

After installation, the bundle is ready to use with zero configuration. It will automatically:

1. **Monitor memory usage** - Track memory consumption for each request
2. **Log warnings** - Alert when memory usage exceeds 50MB threshold
3. **Clean up automatically** - Use WeakMap to prevent memory leaks

The bundle logs to your application's default logger when memory usage exceeds the threshold. Check your logs to see output like:

```
[2024-01-01 12:00:00] app.WARNING: 内存使用超过阈值 {"使用内存":75.5,"内存阈值":"50MB"}
```

No additional configuration is required - the bundle works out of the box.

## How It Works

The bundle uses an event subscriber to:

1. Record memory usage at the beginning of the request
2. Measure memory usage at the end of the request
3. Log a warning when the difference exceeds the configured threshold

Memory usage data is collected using PHP's `memory_get_usage()` function. The bundle uses a `WeakMap` to associate memory measurements with request objects, ensuring no memory leaks occur.

## Requirements

- PHP 8.1+
- Symfony 6.4+
- PSR-compatible logger

## Contributing

Contributions are welcome! Feel free to submit a pull request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
