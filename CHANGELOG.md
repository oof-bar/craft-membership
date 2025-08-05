# Membership Plugin Changelog

## 3.0.0 - 2025-08-05

> [!IMPORTANT]  
> After the upgrade, be sure and add the **Membership Logs** UI element to your Subscriptions field layout to preserve visibility into the plugin’s activity.

### Added

- Craft 5 compatability! 🎉
- Non-admin users with the new **Manage grants** permission can now create and edit grants.
- Membership is now _free_! We are working to incorporate its features into the official [Stripe plugin](https://plugins.craftcms.com/stripe), but it will remain available as a Commerce enhancement until the next major release.

### Changed

- Logs are now exposed via a UI field layout element and can be placed anywhere you wish on the Subscriptions field layout.
- Spruced up some control panel views with additional help text and cross-links.

## 2.0.2 - 2023-05-01

### Changed

- Fixed URLs to readme and issues in `composer.json`.

## 2.0.1 - 2023-04-30

### Changed

- Fixed an error when saving a grant in an environment that has `allowAdminChanges` disabled.

## 2.0.0 - 2023-04-30

### Added

- Membership is now compatible with Craft 4 and Commerce 4!

### Changed

- The new grant view is only accessible if a plan and user group already exist.
- Plan names are included in log messages.
- `templates/grants/index.twig` moved to `templates/_grants/index.twig`
- `templates/grants/edit.twig` moved to `templates/_grants/edit.twig`

### Removed

- `oofbar\membership\controllers\BaseController`
- `oofbar\membership\controllers\BaseCpController`
- `oofbar\membership\controllers\BaseAdminController`
- `oofbar\membership\controllers\LogsController`
- `templates/_include/nav.twig`
- All custom permissions

## 1.0.0 - 2020-02-26

### Added
- Initial release! 🎉
