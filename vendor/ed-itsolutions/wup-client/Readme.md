# WUP Client

Client side code for [WP Update Provider](https://github.com/Ed-ITSolutions/wp-update-provider).

## Install

Using Composer:

```
composer require ed-itsolutions/wup-client
```

## Usage

Require composer as normal.

```php
// For a plugin
wup_client('plugin', 'plugin-slug', 'http://your.site.com/wup/plugin-slug');

// For a theme
wup_client('theme', 'theme-slug', 'http://your.site.com/wup/theme-slug');
//                                 ^^ URL of your WUP install.
``