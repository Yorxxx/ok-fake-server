OK-Fake-Server
===================
<p align="center">
<a href="https://travis-ci.org/Yorxxx/ok-fake-server.svg?branch=master"><img src="https://travis-ci.org/Yorxxx/ok-fake-server.svg?branch=master" alt="Continous Integration"></a>
<a href="https://codecov.io/gh/Yorxxx/ok-fake-server">
  <img src="https://codecov.io/gh/Yorxxx/ok-fake-server/branch/master/graph/badge.svg" alt="Codecov" />
</a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

Dingo/Laravel server to provide data to ok-p2p clients. 
The main purpose of this server is to provide fake data and not be in need to use client server.

----------


## About Laravel

[Laravel](https://laravel.com/) is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Laravel attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, yet powerful, providing tools needed for large, robust applications. A superb combination of simplicity, elegance, and innovation give you tools you need to build any application with which you are tasked.

## About Dingo

The [Dingo API](https://github.com/dingo/api) package is meant to provide you, the developer, with a set of tools to help you easily and quickly build your own API. While the goal of this package is to remain as flexible as possible it still won't cover all situations and solve all problems.

This package provides tools for the following, and more:

- Content Negotiation
- Multiple Authentication Adapters
- API Versioning
- Rate Limiting
- Response Transformers and Formatters
- Error and Exception Handling
- Internal Requests
- API Blueprint Documentation

## Requirements

- PHP 5.6+
- Composer

## Installation

First install dependencies via composer.
```bash
composer install
php artisan vendor:publish --provider="Dingo\Api\Provider\LaravelServiceProvider"
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\JWTAuthServiceProvider"
php artisan jwt:generate
```
Define API_PREFIX environment variable, via path or .env file
```bash
API_PREFIX='api'
```

## Execution
```bash
php artisan serve
```

You can define environment variables (environment, debug mode...) before launching. See [Dingo Configuration](https://github.com/dingo/api/wiki/Configuration) for more info.
