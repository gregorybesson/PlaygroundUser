<?php
/**
 * Copyright (c) 2013 AdFab Connect.
 * All rights reserved.
 *
 * @package     PlaygroundUser
 * @author      Greg Besson <bessong@gmail.com>
 * @copyright   2013 AdFab Connect.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://connect.adfab.fr
 */
namespace PlaygroundUser\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\HeadScript;
use Zend\Stdlib\RequestInterface;

class FacebookLogin extends AbstractHelper
{
    /**
     * @var Tracker
     */
    protected $tracker;

    /**
     * @var string
     */
    protected $container = 'HeadScript';

    /**
     * @var bool
     */
    protected $rendered = false;
    protected $request;
    protected $renderer;

    public function __construct ($config, RequestInterface $request, $renderer)
    {
        $this->config  = $config;
        $this->request = $request;
        $this->renderer = $renderer;
    }

    public function getContainer ()
    {
        return $this->container;
    }

    public function setContainer ($container)
    {
        $this->container = $container;
    }

    public function __invoke ()
    {
        // Do not render the script twice
        if ($this->rendered) {
            return;
        }

        // We return if we are in a console request
        if ((get_class($this->request) == 'Zend\Console\Request')) {
            
            return;
        }
        
        //print_r($this->config);
        if(!isset($this->config['providers']['Facebook']) || !$this->config['providers']['Facebook']['enabled'] ){
            
            return;
        }

        // We need to be sure $container->appendScript() can be called
        $container = $this->view->plugin($this->getContainer());
        if (!$container instanceof HeadScript) {
            throw new RuntimeException(sprintf(
                'Container %s does not extend HeadScript view helper',
                 $this->getContainer()
            ));
        }

        $script = sprintf("var FbDomainAuthId = '%s';\n", $this->config['providers']['Facebook']['keys']['id']);
        $script .= sprintf("var FbDomainAuthScope = '%s';\n", $this->config['providers']['Facebook']['scope']);

        $container->prependScript($script);
        $container->appendFile($this->renderer->libAssetPath() . '/js/fbregister.js');

        $this->rendered = true;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function getRequest()
    {
        return $this->request;
    }
}
