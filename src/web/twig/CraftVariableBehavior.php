<?php
/**
 * Membership plugin for Craft CMS 3.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2020 oof. Studio
 */

namespace oofbar\membership\web\twig;

use Craft;

use yii\base\Behavior;

use oofbar\membership\Membership;


/**
 * CraftVariableBehavior
 * 
 * Attaches a reference to our plugin to the main CraftVariable instance exposed to Twig templates.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class CraftVariableBehavior extends Behavior
{
    /**
     * @var Membership
     */
    public $membership;

    public function init()
    {
        parent::init();

        // Point `craft.membership` to the oofbar\membership\Membership instance. This allows direct access to our various service APIs from Twig:
        $this->membership = Membership::getInstance();
    }
}
