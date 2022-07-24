[![GitHub issues](https://img.shields.io/github/release/vite-manifest-parser)](https://github.com/vite-manifest-parser/releases)
![](https://github.com/vite-manifest-parser/workflows/GitHub_CI/badge.svg " ")
[![codecov](https://codecov.io/gh/vite-manifest-parser/branch/master/graph/badge.svg?token=4yEceIaSFP)](https://codecov.io/gh/vite-manifest-parser)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/vite-manifest-parser/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/vite-manifest-parser/?branch=master)
[![GitHub issues](https://img.shields.io/github/issues/vite-manifest-parser)](https://github.com/vite-manifest-parser/issues)
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)]()

An expressive, query builder for WordPRess it can also be referred as a Database Abstraction Layer. Pixie WPDB supports WPDB ONLY and it takes care of query sanitization, table prefixing and many other things with a unified API.

> **Pixie WPDB** is an adaption of `pixie` originally written by [usmanhalalit](https://github.com/usmanhalalit). [Pixie](https://github.com/usmanhalalit/pixie) is no longer under active development.

# Features
* [Fluent API](https://github.com/gin0115/pixie-wpdb/wiki/Query%20Methods)
* [Nested Queries](https://github.com/gin0115/pixie-wpdb/wiki/Sub%20&%20Nested%20Queries)
* [Multiple Connections](https://github.com/gin0115/pixie-wpdb/wiki/Home#setup-connection)
* [Sub Queries](https://github.com/gin0115/pixie-wpdb/wiki/Sub%20&%20Nested%20Queries)
* [JSON Support](https://github.com/gin0115/pixie-wpdb/wiki/Json%20Methods)
* [Model Hydration](https://github.com/gin0115/pixie-wpdb/wiki/Result%20Hydration)
* [Custom Alias Facade](https://github.com/gin0115/pixie-wpdb/wiki/Home#connection-alias)
* [Raw SQL Expressions](https://github.com/gin0115/pixie-wpdb/wiki/Bindings%20&%20Raw%20Expressions)
* [Value Type Binding](https://github.com/gin0115/pixie-wpdb/wiki/Bindings%20&%20Raw%20Expressions)
* [Transaction Support](https://github.com/gin0115/pixie-wpdb/wiki/Transactions)
* [Query Events](https://github.com/gin0115/pixie-wpdb/wiki/Query%20Events)

```php
$thing = QB::table('someTable')->where('something','=', 'something else')->first();
```

# Install

## Perquisites

* WordPress 5.7+ (tested upto 5.9)
* PHP 7.1+ (includes support for PHP8)
* MySql 5.7+ or MariaDB 10.2+
* Composer (optional)

## Using Composer

The easiest way to include Pixie in your project is to use [composer](http://getcomposer.org/doc/00-intro.md#installation-nix). 

```bash
composer require gin0115/pixie-wpdb
```