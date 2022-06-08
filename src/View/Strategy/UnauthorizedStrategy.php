<?php

namespace PlaygroundUser\View\Strategy;

use BjyAuthorize\Service\Authorize;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\ServiceManager\ServiceLocatorAwareInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface as Response;

class UnauthorizedStrategy implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    /**
     * @var \Laminas\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();
    protected $serviceLocator;
    protected $options = null;

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }


    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), -5000);
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }


    public function onDispatchError(MvcEvent $e)
    {
        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof Response) {
            return;
        }

        $router = $e->getRouter();
        $match  = $e->getRouteMatch();

        // get url to the lmcuser/login route
        $authAdminConfig = $this->getOptions()->getAdmin();
        $options['name'] = $authAdminConfig['route_login_fail'];
        $url = $router->assemble(array(), $options);

        // Work out where were we trying to get to
        $options['name'] = $match->getMatchedRouteName();
        $redirect = $router->assemble($match->getParams(), $options);

        // set up response to redirect to login page
        $response = $e->getResponse();
        if (!$response) {
            $response = new HttpResponse();
            $e->setResponse($response);
        }

        // Don't redirect in case of dev.
        $environnement = getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : null;
        if ($environnement !== 'development') {
            $response->getHeaders()->addHeaderLine('Location', $url . '?redirect=' . $redirect);
            $response->setStatusCode(302);
        }
    }

    public function getOptions()
    {
        if ($this->options === null) {
            $this->options = $this->getServiceLocator()->get('playgrounduser_module_options');
        }

        return  $this->options;
    }
}
