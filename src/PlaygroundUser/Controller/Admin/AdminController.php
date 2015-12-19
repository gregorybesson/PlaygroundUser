<?php

namespace PlaygroundUser\Controller\Admin;

use Zend\Mvc\Controller\AbstractActionController;
use PlaygroundUser\Options\ModuleOptions;
use Zend\View\Model\ViewModel;
use Zend\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator as ORMPaginator;

class AdminController extends AbstractActionController
{
    protected $options;
    protected $userMapper;
    protected $adminUserService;

    public function listAction()
    {
        $filter        = $this->getEvent()->getRouteMatch()->getParam('filter');
        $roleId        = $this->getEvent()->getRouteMatch()->getParam('roleId');
        $search        = $this->params()->fromQuery('name');

        $role        = $this->getAdminUserService()->getRoleMapper()->findByRoleId($roleId);

        $adapter = new DoctrineAdapter(new ORMPaginator($this->getAdminUserService()->getQueryUsersByRole($role, null, $search)));

        $paginator = new Paginator($adapter);
        $paginator->setItemCountPerPage(50);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));


        return new ViewModel(
            array(
                'users' => $paginator,
                'userlistElements' => $this->getOptions()->getUserListElements(),
                'filter'    => $filter,
                'roleId'    => $roleId,
                'search'    => $search,
            )
        );
    }

    public function createAction()
    {
        $service = $this->getAdminUserService();
        $request = $this->getRequest();
        $form = $this->getServiceLocator()->get('playgrounduseradmin_register_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgrounduser/create', array('userId' => 0)));
        $form->setAttribute('method', 'post');

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-user/admin/user');

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $file = $this->params()->fromFiles('avatar');
            if ($file['name']) {
                $data = array_merge(
                    $data,
                    array('avatar'=> $file['name'])
                );
            }
            $user = $this->getAdminUserService()->create($data);
            if ($user) {
                $this->flashMessenger()->setNamespace('playgrounduser')->addMessage('L\'utilisateur a été créé');

                return $this->redirect()->toRoute('admin/playgrounduser/list');
            }
        }

        return $viewModel->setVariables(array('form' => $form, 'userId' => 0));
    }

    public function editAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        if (!$userId) {
            return $this->redirect()->toRoute('admin/playgrounduser/create');
        }

        $service = $this->getAdminUserService();
        $user = $service->getUserMapper()->findById($userId);

        $form = $this->getServiceLocator()->get('playgrounduseradmin_register_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgrounduser/edit', array('userId' => $userId)));
        $form->setAttribute('method', 'post');

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-user/admin/user');

        $request = $this->getRequest();

        $form->bind($user);

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $file = $this->params()->fromFiles('avatar');
            if ($file['name']) {
                $data = array_merge(
                    $data,
                    array('avatar'=> $file['name'])
                );
            }
            $result = $this->getAdminUserService()->edit($data, $user);

            if ($result) {
                return $this->redirect()->toRoute('admin/playgrounduser/list');
            }
        }

        // I Do fill in the assigned role to the select element. Not that pretty. TODO : Improve this !
        $roleValue = null;

        foreach ($user->getRoles() as $id => $role) {
            $roleValue = $role->getRoleId();
        }

        if ($roleValue) {
            $roleValues = $form->get('roleId')->getValueOptions();
            $roleValues[$roleValue]['selected'] = true;
            $form->get('roleId')->setValueOptions($roleValues);
        }

        return $viewModel->setVariables(
            array(
                'form' => $form,
                'userId' => 0
            )
        );
    }

    public function removeAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserMapper()->findById($userId);
        if ($user) {
            $this->getUserMapper()->remove($user);
            $this->flashMessenger()->setNamespace('playgrounduser')->addMessage('The user was deleted');
        }

        return $this->redirect()->toRoute('admin/playgrounduser/list');
    }

    public function activateAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserMapper()->findById($userId);
        if ($user) {
            $this->getUserMapper()->activate($user);
            $this->flashMessenger()->setNamespace('playgrounduser')->addMessage('The user was activated');
        }

        return $this->redirect()->toRoute('admin/playgrounduser/list');
    }

    public function resetAction()
    {
        $userId = $this->getEvent()->getRouteMatch()->getParam('userId');
        $user = $this->getUserMapper()->findById($userId);
        if ($user) {
            $this->getAdminUserService()->resetPassword($user);
            $this->flashMessenger()->setNamespace('playgrounduser')->addMessage('Un mail a été envoyé à '. $user->getEmail());
        }

        return $this->redirect()->toRoute('admin/playgrounduser/list');
    }

    public function listRoleAction()
    {
        $filter        = $this->getEvent()->getRouteMatch()->getParam('filter');

        $adapter = new \Zend\Paginator\Adapter\ArrayAdapter($this->getAdminUserService()->getRoleMapper()->findAll());

        $paginator = new \Zend\Paginator\Paginator($adapter);
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));


        return new ViewModel(
            array(
                'roles' => $paginator,
                'filter'    => $filter,
            )
        );
    }

    public function createRoleAction()
    {
        $service = $this->getAdminUserService();
        $request = $this->getRequest();
        $form = $this->getServiceLocator()->get('playgrounduseradmin_role_form');
        $form->setAttribute('action', $this->url()->fromRoute('admin/playgrounduser/createrole', array('roleId' => 0)));
        $form->setAttribute('method', 'post');

        $viewModel = new ViewModel();
        $viewModel->setTemplate('playground-user/admin/role');

        if ($request->isPost()) {
            $data = $request->getPost()->toArray();
            $role = $this->getAdminUserService()->createRole($data);
            if ($role) {
                $this->flashMessenger()->setNamespace('playgrounduser')->addMessage('The role has been created');

                return $this->redirect()->toRoute('admin/playgrounduser/listrole');
            }
        }

        return $viewModel->setVariables(array('form' => $form, 'roleId' => 0));
    }

    public function setOptions(ModuleOptions $options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        if (!$this->options instanceof ModuleOptions) {
            $this->setOptions($this->getServiceLocator()->get('playgrounduser_module_options'));
        }

        return $this->options;
    }

    public function getUserMapper()
    {
        if (null === $this->userMapper) {
            $this->userMapper = $this->getServiceLocator()->get('zfcuser_user_mapper');
        }

        return $this->userMapper;
    }

    public function setUserMapper(UserMapperInterface $userMapper)
    {
        $this->userMapper = $userMapper;

        return $this;
    }

    public function getAdminUserService()
    {
        if (null === $this->adminUserService) {
            $this->adminUserService = $this->getServiceLocator()->get('playgrounduser_user_service');
        }

        return $this->adminUserService;
    }

    public function setAdminUserService($service)
    {
        $this->adminUserService = $service;

        return $this;
    }
}
