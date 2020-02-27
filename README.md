# Membership Plugin for Craft Commerce

> Give your users special access based on their [Commerce Subscriptions](https://docs.craftcms.com/commerce/v3/subscriptions.html).

The Membership plugin works by listening to key [Subscription Events](https://docs.craftcms.com/commerce/v3/events.html#subscription-events) in Craft Commerce, and moving the Subscriber into (or out of) groups based on rules or “Grants” configured in the Control Panel.

Much of it could be implemented in a module specific to your application—this plugin is primarily intended for those who need a simple system for granting access based on active Subscriptions.

The plugin covers creation, cancellation, expiry of Subscriptions, and switching of Plans. At the moment, _it doesn't support special access or restrictions based on Trial periods_.

## Details

Each _Grant_ is a kind of policy or rule for which Plans map to which User Groups. By default, the plugin doesn't do anything—with no Grants configured, it won't make any changes to your Users' permissions.

Because Membership operates on User Groups (not Permissions, directly), it's good to start by designing a sensible group-based permissions structure—for example, if your organization had _Bronze_, _Silver_, and _Gold_ support tiers, you might create three User Groups, and assign the relevant permissions to each.

You can create multiple Grants per Plan—for example, if you wanted to structure your permissions in an additive way, you could grant _Gold_ supporters access to all three groups. In this way, you can be sure that benefits granted to lower support tiers always bubble up to higher ones.

The plugin will never remove a User from a Group that is granted by another of their active Subscriptions: if `Plan A` and `Plan B` both move Users into `Group 1`, but `Plan B` also adds users to `Group 2`, cancelling a Subscription to `Plan B` won't remove the User from `Group 1`, if their `Plan A` Subscription is still active.

## Usage

All the configuration happens via the Control Panel. Go to the Settings section, and click on the _Membership_ tile to manage _Grants_.

In your template, you can use the normal Craft User methods to check whether someone has a particular access level:

```twig
{% if currentUser.isInGroup('membersBronze') %}
    <p>Thank you for your support! You have access to our entire lesson catalog.</p>
{% endif %}
```

In addition to checking groups, you can also directly check permissions: For example, if you didn't really care which Subscription(s) they have, but just need to determine whether or not they have some capability, you can use the `.can()` method:

```twig
{% set section = craft.app.sections.getSectionByHandle('classifieds') %}

{% if currentUser.can("createEntries:#{section.uid}") %}
    You’re ready to <a href="{{ url('account/classifieds/new') }}">create a listing</a>!
{% endif %}
```

## Auditing

We have a lightweight logging system set up so that admin have some visibility into what the plugin is doing. The `{{%membership_logs}}` table keeps track of any actions (successes and failures) taken by the plugin, and the relevant logs are output on the Subscription's edit page (as of [Commerce 3.0.11](https://github.com/craftcms/commerce/blob/develop/CHANGELOG.md#3011---2020-02-25)).

## Extensibility

Craft and Yii provide a rich system of Events to help developers alter the behavior of built-in and “pluggable” functionality.

We emit two events: one just before a permission is about to be granted, and one when a permission is about to be revoked. Keep in mind that these are _in addition to_ Craft's own permissions events!

### `Permissions::EVENT_BEFORE_GRANT_PERMISSION`

Raised just before a membership to a User Group is granted. This is not emitted when a permission is not granted due to a User already being in a given Group.

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

Raised just before membership to a User Group is revoked. This is not emitted if the User was not in the Group a Grant is attempting to revoke.

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

## Requirements

This plugin requires Craft CMS 3.4 and Commerce 3.0. It may work with earlier versions, but it hasn't been tested!

> In order for Subscriptions to work at all, **you must have Stripe Webhooks configured**!

## Installation

To install the plugin, follow these instructions (or just search “Membership” in the [Craft Plugin Store](#)):

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require oof-bar/membership

3. In the Control Panel, go to Settings &rarr; Plugins and click the “Install” button for Membership.

:deciduous_tree: