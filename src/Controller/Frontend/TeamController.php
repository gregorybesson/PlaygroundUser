<?php

namespace PlaygroundUser\Controller\Frontend;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Laminas\ServiceManager\ServiceLocatorInterface;

class TeamController extends AbstractActionController
{
    /**
     *
     */
    protected $options;

     /**
     * @var teamService
     */
    protected $teamService;

    /**
     *
     * @var ServiceManager
     */
    protected $serviceLocator;

    public function __construct(ServiceLocatorInterface $locator)
    {
        $this->serviceLocator = $locator;
    }

    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function indexAction()
    {
        return new ViewModel();
    }

    public function getTeamService()
    {
        if (!$this->teamService) {
            $this->teamService = $this->getServiceLocator()->get('playgrounduser_team_service');
        }

        return $this->teamService;
    }

    public function setMailService($teamService)
    {
        $this->teamService = $teamService;

        return $this;
    }
}
