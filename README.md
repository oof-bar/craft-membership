# Membership Plugin for Craft Commerce

> Give your users special access based on their [Commerce subscriptions](https://docs.craftcms.com/commerce/v4/subscriptions.html).

The Membership plugin works by listening to key [Subscription Events](https://docs.craftcms.com/commerce/v4/events.html#subscription-events) in Craft Commerce, and moving the subscriber into (or out of) groups based on rules or “grants” configured in the control panel.

Much of it could be implemented in a module specific to your application—this plugin is primarily intended for those who need a simple system for granting access based on active subscriptions, and do not wish to maintain that logic themselves.

The plugin handles creation, cancellation, and expiry of subscriptions, as well as switching of plans. At the moment, it does _not_ support special access or restrictions based on “trial” periods.

## Requirements

This plugin requires Craft CMS 4 and Commerce 4. [Version 1.x](https://github.com/oof-bar/craft-membership/tree/v1) is compatible with Craft 3 and Commerce 3.

> **Warning**
> In order for subscriptions to work at all, **you must have Stripe webhooks configured**! Stripe has an excellent CLI to help [forward webhooks in development environments](https://stripe.com/docs/webhooks/test).

### Upgrading from Membership 1.x

The 2.x upgrade should require no manual intervention, but it's always important to test your application for consistency.

There are a few important changes to note, in case you’ve been using any internal features:

- `oofbar\membership\controllers\BaseController` has been removed. The convenience features it implemented are built into Craft 4.
- `oofbar\membership\controllers\BaseCpController` and `oofbar\membership\controllers\BaseAdminController` have been removed. Permission checks are now done in individual controllers.
- `oofbar\membership\controllers\LogsController` has been removed, as it was never used.
- Custom permissions (`membership-manageGrants` and `membership-viewLogs`) were removed, as they were never checked. Access has been consolidated to `admin` users.

## Installation

To install the plugin, follow these instructions (or just search “Membership” in the [Craft Plugin Store](#)):

1. Open your terminal and go to your Craft project:

    ```bash
    cd /path/to/project
    ```

2. Then tell Composer to load the plugin:

    ```bash
    composer require oof-bar/membership -w
    ```

3. In the Control Panel, go to Settings &rarr; Plugins and click the “Install” button for Membership, or run:

    ```bash
    php craft plugin/install membership
    ```

## Details

Each **grant** is a kind of policy or rule for which plans map to which **user groups**. By default, the plugin doesn't do anything—with no grants configured, it won't make any changes to your users' permissions.

Because Membership operates on user groups (not permissions, directly), it's good to start by designing a sensible group-based permissions structure—for example, if your organization had _Bronze_, _Silver_, and _Gold_ support tiers, you might create three user groups, and assign the relevant permissions to each.

You can create multiple grants per plan—for example, if you wanted to structure your permissions in an additive way, you could grant _Gold_ supporters access to all three groups. In this way, you can be sure that benefits granted to lower support tiers always bubble up to higher ones.

The plugin will never remove a user from a group that is granted by another of their active subscriptions: if `Plan A` and `Plan B` both move Users into `Group 1`, but `Plan B` also adds users to `Group 2`, cancelling a subscription to `Plan B` won't remove the user from `Group 1`, if their `Plan A` subscription is still active.

> **Note**
> Changing the configuration of a grant will _not_ update existing users’ groups.

## Usage

All the configuration happens via the Control Panel. Go to the **Settings** section, and click on the **Membership** tile to manage **Grants**.

> **Note**
> Grants are _not_ stored in project config, and therefore must be configured in each environment. This is currently a [limitation](https://github.com/oof-bar/craft-membership/issues/5) of Commerce. You may need to navigate to the settings page directly, on your production environment—the path is always `settings/membership/grants`.

### Front-end

In your template, you can use the normal Craft user methods to check whether someone has a particular access level:

```twig
{% if currentUser.isInGroup('membersBronze') %}
    <p>Thank you for your support! You have access to our entire lesson catalog.</p>
{% endif %}
```

In addition to checking groups, you can also directly check permissions: For example, if the subscription(s) a user has aren’t as important as whether or not they have a certain capability, you can use the `.can()` method:

```twig
{% set section = craft.app.sections.getSectionByHandle('classifieds') %}

{% if currentUser.can("createEntries:#{section.uid}") %}
    You’re ready to <a href="{{ url('account/classifieds/new') }}">create a listing</a>!
{% endif %}
```

> **Warning**
> Automated management of user permissions via groups can be dangerous. Consider defining a [process or policy](https://putyourlightson.com/articles/securing-your-craft-site-in-2022-part-3) for reviewing and deploying changes to your project’s permissions scheme.

## Auditing

Membership has a lightweight logging system built-in so that store administrators have some visibility into what the plugin is doing. The `{{%membership_logs}}` table keeps track of any actions (successes and failures) taken by the plugin, and the relevant logs are output on the subscription's edit page (as of [Commerce 3.0.11](https://github.com/craftcms/commerce/blob/develop/CHANGELOG.md#3011---2020-02-25)).

## Extensibility

Craft and Yii provide a rich system of [events](https://craftcms.com/docs/4.x/extend/events.html) to help developers alter the behavior of built-in and “pluggable” functionality.

Membership emits two events: one just before a permission is about to be granted, and one when a permission is about to be revoked. Keep in mind that these are _in addition to_ Craft's own permissions events!

### `Permissions::EVENT_BEFORE_GRANT_PERMISSION`

Raised just before a membership to a user group is granted. This is not emitted when a permission is not granted due to a user already being in a given group.

```php
use yii\base\Event;

use oofbar\membership\services\Permissions;
use oofbar\membership\events\GrantPermission as GrantPermissionEvent;

Event::on(
    Permissions::class,
    Permissions::EVENT_BEFORE_GRANT_PERMISSION,
    function (GrantPermissionsEvent $e) {
        // Optionally: prevent the grant from occurring, based on some criteria!
        $e->isValid = false;
    });
```

### `Permissions::EVENT_BEFORE_REVOKE_PERMISSION`

Raised just before a user is removed from a user group. This is not emitted when a grant would have removed a user from a group they were’t in. Instead, Membership creates a log message reflecting this state.

```php
use yii\base\Event;

use oofbar\membership\services\Permissions;
use oofbar\membership\events\RevokePermission as RevokePermissionEvent;

Event::on(
    Permissions::class,
    Permissions::EVENT_BEFORE_REVOKE_PERMISSION,
    function (RevokePermissionsEvent $e) {
        // Optionally: prevent the revocation from occurring, based on some criteria!
        $e->isValid = false;
    });
```

:deciduous_tree:
