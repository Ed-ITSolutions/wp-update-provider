# WUP Client

Client side code for [WP Update Provider](https://github.com/Ed-ITSolutions/wp-update-provider).

## Install

Using Composer:

```
composer require ed-itsolutions/wup-client
```

## Usage

Require composer as normal.

Add `wup_client` to your `functions.php` or plugin class.

```php
// For a plugin
wup_client('plugin', 'plugin-slug', 'http://your.site.com/wup/plugin-slug');

// For a theme
wup_client('theme', 'theme-slug', 'http://your.site.com/wup/theme-slug');
//                                 ^^ URL of your WUP install.
```

That's it!

wup-client will now talk to WUP and offer updates when needed.

## CI

wup-client also provides the `wup_build` function which can be used to have your CI build and release a new version of your plugin automatically.

```php
wup_build(
  'theme-slug', // The themes (or plugins) slug.
  '/some/path', // The root path of the theme/plugin. Use dirname(__FILE__) to make this generic.
  'deployKey', // The deploy key for the server. DON'T ACTUALLY PUT THIS IN VCS. ProTip. getenv('WUP_DEPLOY_KEY')
  'http://yoursite.com/wp-admin/admin-post.php' // The admin-post.php url for your site.
);
```

Exit Codes:

 - 1 - Something went wrong deploying to domain.
 - 2 - No deploy key set.

 ## Client Conflict Resolution

 WUP-Client may end up in multiple plugins on the same site. It maybe that it is being used by 2 wp-update-provider servers and you have no control over updating the other installations.

 To prevent issues where pluginA uses wup-client 0.0.X and pluginB uses 0.1.X may cause pluginB to use 0.0.X's `WUPClient` class a system is in place to ensure that the lastest version of WUPClient is used by all plugins and themes on the site.

 If breaking changes are introduced a system will be devised to ensure that WUPClient is used within the correct major version.