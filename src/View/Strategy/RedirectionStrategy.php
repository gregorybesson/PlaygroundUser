<?php

namespace PlaygroundUser\View\Strategy;

use BjyAuthorize\Exception\UnAuthorizedException;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use BjyAuthorize\Guard\Route;
use BjyAuthorize\Guard\Controller;

/**
 * Dispatch error handler, catches exceptions related with authorization and
 * redirects the user agent to a configured location
 *
 * @author Ben Youngblood <bx.youngblood@gmail.com>
 * @author Marco Pivetta  <ocramius@gmail.com>
 */
class RedirectionStrategy implements ListenerAggregateInterface
{
    /**
     * @var string route to be used to handle redirects
     */
    protected $redirectRoute = 'frontend/zfcuser/login';

    /**
     * @var array route to be used to handle redirects
     */
    protected $redirectRouteArray = [];

    /**
     * @var string route to be used to handle redirects
     */
    protected $redirectAdminRoute = 'admin';

    /**
     * @var string URI to be used to handle redirects
     */
    protected $redirectUri;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), -5000);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Handles redirects in case of dispatch errors caused by unauthorized access
     *
     * @param \Zend\Mvc\MvcEvent $event
     */
    public function onDispatchError(MvcEvent $event)
    {
        // Do nothing if the result is a response object
        $result     = $event->getResult();
        $routeMatch = $event->getRouteMatch();
        $response   = $event->getResponse();
        $router     = $event->getRouter();
        $error      = $event->getError();
        $url        = $this->redirectUri;

        if ($result instanceof Response
            || ! $routeMatch
            || ($response && ! $response instanceof Response)
            || ! (
                Route::ERROR === $error
                || Controller::ERROR === $error
                || (
                    Application::ERROR_EXCEPTION === $error
                    && ($event->getParam('exception') instanceof UnAuthorizedException)
                )
            )
        ) {
            return;
        }

        $routeName       = $routeMatch->getMatchedRouteName();
        $areaName        = (strpos($routeName, '/'))?substr($routeName, 0, strpos($routeName, '/')):$routeName;
        $areaName        = ($areaName === 'frontend' || $areaName === 'admin')? $areaName : 'frontend';

        if (null === $url) {
            if ($areaName === 'admin') {
                $url = $router->assemble(array(), array('name' => $this->redirectAdminRoute));
            } else {
                $url = $router->assemble($this->redirectRouteArray, array('name' => $this->redirectRoute));
            }
        }

        $response = $response ?: new Response();

        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(302);

        $event->setResponse($response);
    }

    /**
     * @param string $redirectRoute
     */
    public function setRedirectRoute($redirectRoute)
    {
        $this->redirectRoute = (string) $redirectRoute;
    }

    /**
     * @param array $redirectRouteArray
     */
    public function setRedirectRouteArray($redirectRouteArray)
    {
        $this->redirectRouteArray = $redirectRouteArray;
    }

    /**
     * @param string|null $redirectUri
     */
    public function setRedirectUri($redirectUri)
    {
        $this->redirectUri = $redirectUri ? (string) $redirectUri : null;
    }

        /**
     * @param string $redirectAdminRoute
     */
    public function setRedirectAdminRoute($redirectAdminRoute)
    {
        $this->redirectAdminRoute = (string) $redirectAdminRoute;
    }
}
