<?php
/**
 * Membership plugin for Craft CMS 3.x
 *
 * Give your users special access based on their Commerce Subscriptions.
 *
 * @link      https://oof.studio/
 * @copyright Copyright (c) 2020 oof. Studio
 */

namespace oofbar\membership\controllers;

use Craft;
use craft\web\Controller;

/**
 * Base Controller
 *
 * Provides a few utility/shortcut methods to the Plugin's other controllers.
 *
 * @author    oof. Studio
 * @package   Membership
 * @since     1.0.0
 */
class BaseController extends Controller
{
    /**
     * Returns a successful response based on the content type that the client accepts.
     * 
     * @param string $message
     * @param mixed $redirectParams Data to make available when rendering the redirect string template. Can be an object or associative array.
     */
    protected function _sendSuccessResponse(string $message, $redirectParams = null)
    {
        if (Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'message' => $message,
                'data' => $redirectParams
            ]);
        }

        Craft::$app->getSession()->setNotice($message);

        return $this->redirectToPostedUrl($redirectParams);
    }

    /**
     * Returns an error based on the content type that the client accepts.
     * 
     * @param string $message
     * @param array|null $params Route params to pass back to the template.
     * @return Response|null
     */
    protected function _sendErrorResponse(string $message, array $params = [])
    {
        if (Craft::$app->getRequest()->getAcceptsJson()) {
            return $this->asJson([
                'success' => false,
                'message' => $message
            ]);
        }

        Craft::$app->getSession()->setError($message);

        if ($params) {
            Craft::$app->getUrlManager()->setRouteParams($params);
        }

        return null;
    }
}