# WP Update Provider

[![CircleCI](https://circleci.com/gh/Ed-ITSolutions/wp-update-provider.svg?style=svg)](https://circleci.com/gh/Ed-ITSolutions/wp-update-provider)

WUP provides an interface in the WordPress admin panel to create and manage theme/plugins updates. From WUP you can see the current version of your package and the `site_urls` of any sites using it along with the version they are running.

## Install

Head over to [releases](https://github.com/Ed-ITSolutions/wp-update-provider/releases) and download the provided ZIP file.

Upload it to your WordPress site and activate the plugin.

## Usage

### Creating a Package

At the bottom of the WordPress admin side bar you will see `WP Update Provider`. Open it and click `add new`.

To create a package you need to supply its name and slug. _Slug needs to match the slug clients will use_.

### Releasing a Version

#### Using the dashboard

When viewing a package there is a file uploader in which you can place a ZIP file. This file will be uploaded and parsed through the same system as the CI uploader.

#### Using CI

WUP-Client provides a function to send [builds from your CI](https://github.com/Ed-ITSolutions/wup-client#ci).