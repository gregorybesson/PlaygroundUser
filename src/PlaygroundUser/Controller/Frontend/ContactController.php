<?php

namespace PlaygroundUser\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorInterface;

class ContactController extends AbstractActionController
{
    /**
     *
     */
    protected $options;

     /**
     * @var mailService
     */
    protected $mailService;
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
        $mailService = $this->getMailService();

        $to = '';
        $config = $this->getServiceLocator()->get('config');
        if (isset($config['contact']['email'])) {
            $to = $config['contact']['email'];
        }

        $form = $this->getServiceLocator()->get('playgrounduser_contact_form');
        $form->setAttribute('method', 'post');

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost()->toArray();
            $form->setData($data);
            if ($form->isValid()) {
                $from = $data['email'];
                $subject= 'Contact : '.$data['object'];
                $result = $mailService->createHtmlMessage($from, $to, $subject, 'playground-user/email/question', array('data' => $data));

                if ($result) {
                    return $this->redirect()->toRoute('frontend/contact/confirmation');
                }
            }
        }

        return new ViewModel(array(
                'form' => $form,
            ));
    }

    public function confirmationAction()
    {
        return new ViewModel();
    }

    public function getMailService()
    {
        if (!$this->mailService) {
            $this->mailService = $this->getServiceLocator()->get('playgroundgame_message');
        }

        return $this->mailService;
    }

    public function setMailService($mailService)
    {
        $this->mailService = $mailService;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options) {
            $this->setOptions($this->getServiceLocator()->get('playgroundcore_module_options'));
        }

        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }
}
