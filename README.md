# Database Translations plugin for Craft CMS

Manage Craft i18n translations and store in database

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.7 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require digitalpulsebe/craft-database-translations

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Database Translations.

## Configuring Database Translations

Configure options in the Craft control panel or create a file in config/database-translations.php as a copy of [config.php](src/config.php).

![Screenshot](resources/img/screenshot_settings.png)

### Configure the translation categories

Only the categories defined in the settings will be translated.

### Add missing translations

When a new unknown translation is used while rendering templates, an event is triggered.
Handling this event is optional. A new empty translation row will be added for the missing message.

## Importing translations

![Screenshot](resources/img/screenshot_import.png)

Apart from the automatic missing translations' event, there are four ways of adding
message rows in the database:

1. **Create one manually**

   Just enter the new message key and category to add an empty row

2. **Parse twig templates**

   Twig files are processed to find usage of the |t filter.
   The found results are listed in a review-step, select the rows you want to add to the database.

3. **Import CSV files**

   Import CSV files. Map the columns.

   ![Screenshot](resources/img/screenshot_import_map.png)

   The found results are listed in a review-step, select the rows you want to add to the database.

4. **Parse php translation files**

   The native translation files in ./translations folder can be mapped to database rows.
   The found results are listed in a review-step, select the rows you want to add to the database.

## Exporting

1. Export all the rows using the export tab
2. Or select the rows in the table to export

## Manage translations

![Screenshot](resources/img/screenshot_table.png)
