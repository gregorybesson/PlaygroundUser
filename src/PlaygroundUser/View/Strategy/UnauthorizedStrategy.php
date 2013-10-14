<?php

namespace PlaygroundUser\View\Strategy;

use BjyAuthorize\Service\Authorize;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;

class UnauthorizedStrategy implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

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

        // get url to the zfcuser/login route
        $options['name'] = 'admin';
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

        // C'est difficile de faire des devs si on a un redirect pour cacher l'erreur.
        $environnement = getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : null; 
        if ($environnement !== 'development') {
            $response->getHeaders()->addHeaderLine('Location', $url . '?redirect=' . $redirect);
            $response->setStatusCode(302);
        }

    }
}