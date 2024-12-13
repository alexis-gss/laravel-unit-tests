---
layout:
  title:
    visible: true
  description:
    visible: false
  tableOfContents:
    visible: true
  outline:
    visible: true
  pagination:
    visible: true
---

# 📥 Installation

{% hint style="info" %}
[Laravel Unit Tests](https://github.com/alexis-gss/laravel-unit-tests/tree/develop) requires [PHP 8.3+](https://www.php.net/releases/).
{% endhint %}

Add this composer package to your Laravel project:

```
composer req alexis-gss/laravel-unit-tests
```

Then, copy few configuration files in your application from vendor package file:

```
php artisan vendor:publish --provider="LaravelUnitTests\UnitTestsServiceProvider"
```

{% hint style="info" %}
You can also use the following command:

```
php artisan vendor:publish
```

&#x20;and select the package provider&#x20;

```
LaravelUnitTests\UnitTestsServiceProvider
```
{% endhint %}

{% hint style="warning" %}
&#x20;To publish only configuration files required for tests execution, you can use the tag:

```
--tag="config"
```
{% endhint %}

{% hint style="warning" %}
To publish only the authentication tests file, you can use the tag:

```
--tag="auth"
```
{% endhint %}

{% hint style="danger" %}
To overload existing files (adding this tag is recommended for proper package operation), you can use the option:

```
--force
```
{% endhint %}

Update the configuration file `config/unit-test.php` according to your project.

Finally, run all the tests and see the result:

```
php artisan test
```
