# List Builder for Craft CMS 3

The ultimate [Craft CMS](https://craftcms.com/) mailing list builder.

Maximise your mailing list conversion rate with auto-generated inline forms and popups or go fully custom.


* Easily create and integrate subscriptions forms
* Little-to-no code required
* Sync collected emails with popular marketing platforms
* Create popups which display after a delay or using an exit intent
* GDPR consent tracking

## Contents

- [License](#license)
- [Requirements](#installation)
- [Usage](#usage)
- [Sources](#sources)
- [Destinations](#destinations)
- [Syncing](#syncing)
- [Support](#support)
- [Roadmap](#roadmap)
- [Credits](#credits)

## License

This plugin requires a commercial license which can be purchased through the Craft Plugin Store.  
The license fee is $49 plus \$19 per subsequent year for updates (optional).

## Requirements

This plugin requires Craft CMS 3.0.0 or later.

## Usage

Install the plugin from the Craft Plugin Store in your site’s control panel or manually using composer.

```
composer require mattgrayisok/craft-list-builder
```

Once the plugin is installed, you'll need to set up a **Source** (a method of collecting emails) and optionally a **Destination** (somewhere to send emails that have been collected). You can view and export any emails that have been collected via the control panel or just allow them to be synced up to your marketing platform on a regular basis.

## Sources

*TODO: Detailed instructions for sources*

## Destinations

*TODO: Detailed instructions for destinations*

## Syncing

Syncing subscribers up to marketing platforms can be a slow process. Because of this List Builder won't sync after every single email is collected. There are three triggers which can start a sync process:

### Manual

In the Destinations section of the plugin you'll find a `Sync All` button in the top right corner. Clicking this will immediately start a sync task running.

### Every 10 Subscriptions

By default the plugin will queue up a sync task for every 10 subscriptions that are received. This will only add the task to the queue though, you'll need to visit the control panel or be running an [async queue runner](https://github.com/ostark/craft-async-queue) for the task to actually process.

The plugin is set up to ensure that only one sync task can be scheduled at any time so if many subscriptions are received before the queue is processed it will still only contain a single sync task.

### Scheduled

A console command is available if you'd like to run sync tasks on a schedule. Simply set up a cron job to execute `./craft list-builder/subscribers/sync` as often as you would like. The plugin is set up to ensure that only one sync task can be scheduled at any time as there's no benefit in allowing these tasks to overlap.

## Support

If you encounter any issues during the use of this plugin please let me know by:

* Creating an issue on GitHub
* Dropping me an email: matt at mattgrayisok dot com
* Finding me in the Craft Discord: @mattgrayisok
* DMing me on Twitter: @mattgrayisok

I'll respond to critical issues as quickly as I can.

## Roadmap

### Version 1.x

- Custom fields
- Improved documentation
- Convert Kit integration
- AWeber integration
- Campaign Monitor integration
- Additional dashboard insights

### Version 2.x

- Page whitelisting/blacklisting for popups
- Additional themes
- Multi-site support


## Credits

Created by [mattgrayisok](https://mattgrayisok.com/).
