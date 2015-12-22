<?php
namespace PlaygroundUser\Service;

use PlaygroundUser\Mapper\Team as teamMapper;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 *
 *
 */
class Team implements ServiceManagerAwareInterface, EventManagerAwareInterface
{
    /**
     * Service Manager
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * Event Manager
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Team mapper
     * @var PlaygroundUser\Mapper\Team
     */
    protected $teamMapper;


    /**
     * @param  ServiceManager $serviceManager
     * @return \PlaygroundUser\Service\Team
     */
    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * @return \Zend\ServiceManager\ServiceManager
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return mixed
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $this->eventManager = $events;

        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->eventManager instanceof EventManagerInterface) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }

    /**
     * @return \PlaygroundUser\Mapper\TeamMapper
     */
    public function getTeamMapper()
    {
        if ($this->teamMapper == null) {
            $this->teamMapper = $this->getServiceManager()->get('playgrounduser_team_mapper');
        }

        return $this->teamMapper;
    }
}
