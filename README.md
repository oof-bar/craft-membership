# Membership Plugin for Craft Commerce

> Give your users special access based on their [Commerce subscriptions](https://craftcms.com/docs/commerce/5.x/system/subscriptions.html).

The Membership plugin works by listening to key [Subscription Events](https://craftcms.com/docs/commerce/5.x/extend/events.html#subscription-events) in Craft Commerce, and moving the subscriber into (or out of) groups based on rules or “grants” configured in the control panel.

Much of it could be implemented in a module specific to your application—this plugin is primarily intended for those who need a simple system for granting access based on active subscriptions, and do not wish to maintain that logic themselves.

The plugin handles creation, cancellation, and expiry of subscriptions, as well as switching of plans. At the moment, it does _not_ support special access or restrictions based on “trial” periods.

## Requirements

This plugin requires Craft CMS 5 and Commerce 5. [Version 2.x](https://github.com/oof-bar/craft-membership/tree/v2) is compatible with Craft 4 and Commerce 4, and [version 1.x](https://github.com/oof-bar/craft-membership/tree/v1) works on Craft 3 and Commerce 3.

> [!WARNING]
> In order for subscriptions to work at all, **you must have Stripe webhooks configured**! Stripe has an excellent CLI to help [forward webhooks in development environments](https://stripe.com/docs/webhooks/test).

### Upgrading from Membership 2.x

The 3.x upgrade should require no manual intervention, but it's always important to test your application for consistency.

There are only three functional changes:

- Logs are no longer shown on Subscription edit screens, by default. You must add the **Membership Logs** field layout element by visiting **Commerce** &rarr; **Settings** &rarr; **Subscription Fields**.
- A bug with grant validation was fixed, which may have allowed duplicate/identical grant configurations to exist in prior versions. The plugin now prevents saving grants that have the same plan _and_ user group as another grant.
- A new **Manage grants** permission controls whether non-admin users can create, update, and delete grants.

> [!WARNING]
> Any user with this permission can use it to escalate their own access by creating a grant and then starting a Subscription. It is not granted to any users during the upgrade, and administrators will always be able to manage grants.

## Installation

To install the plugin, follow these instructions (or just search “Membership” in the [Craft Plugin Store](https://plugins.craftcms.com/membership)):

1. Open your terminal and go to your Craft project:

    ```bash
    cd /path/to/project
    ```

2. Require the plugin with Composer:

    ```bash
    composer require oofbar/membership -w
    ```

3. In the control panel, go to **Settings** &rarr; **Plugins** and click the “Install” button for Membership, or run:

    ```bash
    php craft plugin/install membership
    ```

## Details

Each **grant** is a kind of policy or rule that maps **plans** to **user groups**. By default, the plugin doesn’t do anything—with no grants configured, it won’t make any changes to your users’ permissions as subscriptions begin and end.

Because Membership operates on user groups (not permissions, directly), it’s good to start by designing a sensible group-based permissions structure—for example, if your organization had _Bronze_, _Silver_, and _Gold_ support tiers, you might create three user groups, and assign the relevant permissions to each.

You can create multiple grants per plan. For example, if you wanted to structure your permissions additively, you could grant _Gold_ supporters access to all three groups. In this way, you can be sure that benefits granted to lower support tiers always bubble up to higher ones.

The plugin will never remove a user from a group that is granted by another of their active subscriptions: if `Plan A` and `Plan B` both move users into `Group 1`, but `Plan B` also adds users to `Group 2`, a subscription to `Plan B` expiring won’t remove the user from `Group 1` if their `Plan A` subscription is still active (and vice versa).

> [!NOTE]
> Changing the configuration of a grant does _not_ update existing users’ groups.

## Usage

All configuration happens via the control panel. Go to the **Settings** section, and click on the **Membership** tile to manage grants.

> [!NOTE]
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

> [!WARNING]
> Automated management of user permissions via groups can be dangerous. Consider defining a [process or policy](https://putyourlightson.com/articles/securing-your-craft-site-in-2022-part-3) for reviewing and deploying changes to your project’s permissions scheme.

## Auditing

Membership has a lightweight logging system built-in so that store administrators have some visibility into what the plugin is doing. The `{{%membership_logs}}` table keeps track of any actions (successes and failures) taken by the plugin, and the relevant logs are available on the subscription’s edit screen via the **Membership logs** field layout element.

## Extensibility

Craft and Yii provide a rich system of [events](https://craftcms.com/docs/5.x/extend/events.html) to help developers alter the behavior of built-in and “pluggable” functionality.

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
    function(GrantPermissionsEvent $event) {
        // Optionally: prevent the grant from occurring, based on some criteria!
        $event->isValid = false;
    }
);
```

The grant that resulted in the change is available as `$event->grant`, and from there, you can access the plan and user group via `$event->grant->getPlan()` and `$event->grant->getUserGroup()`.

### `Permissions::EVENT_BEFORE_REVOKE_PERMISSION`

Raised just before a user is removed from a user group. This is not emitted if a grant would remove a user from a group they weren’t in. Instead, Membership creates a [log](#auditing) message reflecting this state.

```php
use yii\base\Event;

use oofbar\membership\services\Permissions;
use oofbar\membership\events\RevokePermission as RevokePermissionEvent;

Event::on(
    Permissions::class,
    Permissions::EVENT_BEFORE_REVOKE_PERMISSION,
    function(RevokePermissionsEvent $event) {
        // Optionally: prevent the revocation from occurring, based on some criteria!
        $event->isValid = false;
    }
);
```

The grant that resulted in the change is available as `$event->grant`, and from there, you can access the plan and user group via `$event->grant->getPlan()` and `$event->grant->getUserGroup()`.

:deciduous_tree:
