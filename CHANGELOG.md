# Database Translations Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.3.2 - 2024-08-14

### Updated

- update axios to 1.7.4 in response to CVE-2024-39338, new build

### Fixed

- js syntax when registering action trigger

## 3.3.1 - 2024-06-28

### Fixed
- catch no primary site error

## 3.3.0 - 2024-04-19

### Added
- permission for migration export format
- Settings to clear caches after saving translations

## 3.2.4 - 2024-04-12

### Added
- Shift click to select range of rows
- minor UI improvements

## 3.2.3 - 2024-04-10

### Fixed
- fix queries for installs with db prefix

## 3.2.2 - 2024-03-25

### Updated
- docs

## 3.2.1 - 2024-03-20

### Fixed
- migration update statement
- slashes in translation values in migration files

## 3.2.0 - 2024-03-20

### Added
- migration file export type

## 3.1.2 - 2024-02-28

### Added
- support for drafts in the bulk copy action

## 3.1.1 - 2024-02-21

### Fixed
- fix check correct import file type with getMimeType()
- fix missing i18n by avoiding setComponents

## 3.1.0 - 2024-01-11

### Added
- Bulk translate action (requires [Multi Translator Plugin](https://plugins.craftcms.com/multi-translator))

### Fixed
- import from csv with languages mapped from other languages

## 3.0.1 - 2023-12-28

### Fixed
- copy matrix elements in Craft 5

## 3.0.0 - 2023-12-28

### Added
- Craft 5 support

## 2.7.2 - 2024-08-14

### Updated

- update axios to 1.7.4 in response to CVE-2024-39338, new build

### Fixed

- js syntax when registering action trigger

## 2.7.1 - 2024-06-28

### Fixed
- catch no primary site error

## 2.7.0 - 2024-04-19

### Added
- permission for migration export format
- Settings to clear caches after saving translations

## 2.6.5 - 2024-04-12

### Added
- Shift click to select range of rows
- minor UI improvements

## 2.6.4 - 2024-04-10

### Fixed
- fix queries for installs with db prefix

## 2.6.3 - 2024-03-25

### Updated
- docs

## 2.6.2 - 2024-03-20

### Fixed
- slashes in translation values in migration files

## 2.6.1 - 2024-03-20

### Fixed
- migration update statement

## 2.6.0 - 2024-03-20

### Added
- migration file export type

## 2.5.3 - 2024-02-28

### Added
- support for drafts in the bulk copy action

## 2.5.2 - 2024-02-21

### Fixed
- fix check correct import file type with getMimeType()

## 2.5.1 - 2024-02-21

### Fixed
- fix missing i18n by avoiding setComponents

## 2.5.0 - 2024-01-11

### Added
- Bulk translate action (requires [Multi Translator Plugin](https://plugins.craftcms.com/multi-translator))
### Fixed
- import from csv with languages mapped from other languages

## 2.4.0 - 2023-12-18

### Added
- Bulk copy entry action

## 2.3.2 - 2023-12-15

### Fixed
- respect trim setting on import as well
- update npm packages for babel vulnerability

## 2.3.1 - 2023-09-22

### Updated
- Update docs and default conf file

## 2.3.0 - 2023-09-08

### Fixed
- fix select import vendor files after category has been enabled

### Added
- github release automation

### Updated
- license

## 2.2.3 - 2023-08-31

### Fixed
- revert fix select import vendor files after category has been enabled

## 2.2.2 - 2023-08-31

### Fixed
- fix select import vendor files after category has been enabled

## 2.2.1 - 2023-06-30

### Updated
- new logo
- npm package updates and build

## 2.2.0 - 2023-06-27

### Added
- option to trim values on save
- list vendor php translation files to import

## 2.1.2 - 2023-03-29

### Added
- Setting to handle missing translations only in devMode

## 1.1.2 - 2023-03-29

### Added
- Setting to handle missing translations only in devMode

## 2.1.1 - 2023-03-29

### Fixed
- api endpoints for custom CP_TRIGGER
- 
### Updated
- UI improvement: hide messages and site columns
- UI improvement: minimum width of value columns
- UI improvement: auto resize textarea

## 1.1.1 - 2023-03-29

### Fixed
- api endpoints for custom CP_TRIGGER

### Updated
- UI improvement: hide messages and site columns
- UI improvement: minimum width of value columns
- UI improvement: auto resize textarea

## 2.1.0 - 2023-03-06

### Added
- Map locale sources to other locales
- Event cmd+s to save
- Handy messages in views for empty data

## 1.1.0 - 2023-03-06

### Added
- Map locale sources to other locales
- Event cmd+s to save
- Handy messages in views for empty data

## 2.0.2 - 2023-02-17

### Added
- handle ctrl+s keydown

### Fixed
- remove deleted locales from selected locales in frontend

## 1.0.2 - 2023-02-17

### Added
- handle ctrl+s keydown

### Fixed
- remove deleted locales from selected locales in frontend

## 2.0.1 - 2023-02-13

### Updated
- Remove database charset options for new installs

## 2.0.0 - 2023-01-27

### Added
- Craft 4 support

## 1.0.1 - 2023-02-13

### Updated
- Remove database charset options for new installs

## 1.0.0 - 2023-01-27

### Added
- Initial release
