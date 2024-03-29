# Membership Plugin Changelog

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
