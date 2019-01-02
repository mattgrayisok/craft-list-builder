# List Builder for Craft CMS 3

The ultimate [Craft CMS](https://craftcms.com/) mailing list builder.

* Easily create and integrate subscriptions forms
* Little-to-no code required
* Sync collected emails with popular marketing platforms
* Create popups which display after a delay or using an exit intent
* GDPR consent tracking

## Contents

- [License](#license)
- [Requirements](#installation)
- [Usage](#usage)
- [Settings](#settings)
- [How It Works](#how-it-works)
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

Once the plugin is installed, you'll need to set up a Source (a method of collecting emails) and optionally a Destination (somewhere to send emails that have been collected). You can view and export any emails that have been collected via the control panel or just allow them to be synced up to your marketing platform on a regular basis.

## Settings

> Give a summary of the plugin settings or explain what each of the settings does. Embed a screenshot beneath the text if appropriate.

#### Enable Reporting

Enables the collection of data about the site for generation reports.

#### Detail Level

The level of detail to collect about the site.

- Low : collects site data only.
- Medium : collects site and user data.
- High : collects site, user and interaction data.

> If the plugin comes with a `config.php` file then describe how to use it and explain any settings that are not already explained above.

### Configuration Settings

The plugin comes with a config file for a multi-environment way to set the plugin settings. The config file also provides more advanced plugin configuration settings. To use it, copy the `config.php` to your project’s main `config` directory as `gamma.php` and uncomment any settings you wish to change.

#### Ignore Sites

An array of site IDs to ignore when collecting data.

#### Ignore Users

An array of user IDs to ignore when collecting data.

## How It Works

> Use this section to go in-depth into how the plugin works, using subsections and screenshots where appropriate.

The plugin monitors various things, collecting data about your sites, users and interactions.

### Data Collection

Data is collected automatically as the site is used.

### Reporting

Reports are created based on collected data.

## Roadmap

> Add planned features so that users can see what is in the pipeline.

### Version 2.x

- Better multi-site support.
- Visual reports and graphs.
- More advanced configuration settings.

## Credits

> Credit anyone who deserves it.

Inspired in part by [pluginfactory.io](https://pluginfactory.io).

Created by [PutYourLightsOn](https://putyourlightson.com/).
