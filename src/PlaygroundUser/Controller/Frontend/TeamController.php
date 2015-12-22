<?php

namespace PlaygroundUser\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
