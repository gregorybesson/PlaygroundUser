<?php

namespace PlaygroundUser\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use PlaygroundUser\Options\ModuleOptions;
use Laminas\View\Model\ViewModel;
use Laminas\Paginator\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineAdapter;
use PlaygroundCore\ORM\Pagination\LargeTablePaginator as ORMPaginator;
use Laminas\ServiceManager\ServiceLocatorInterface;
use ZfcDatagrid\Column;
use ZfcDatagrid\Action;
use ZfcDatagrid\Column\Formatter;
use ZfcDatagrid\Column\Type;
use ZfcDatagrid\Column\Style;
use ZfcDatagrid\Filter;

class AdminController extends AbstractActionController
{
    protected $options;
    protected $userMapper;
    protected $adminUserService;
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

    public function listAction()
    {
        // $filter        = $this->getEvent()->getRouteMatch()->getParam('filter');
        // $roleId        = $this->getEvent()->getRouteMatch()->getParam('roleId');
        // $search        = $this->params()->fromQuery('name');

        // $role        = $this->getAdminUserService()->getRoleMapper()->findByRoleId($roleId);

        // $qb = $this->getAdminUserService()->getQueryUsersByRole($role, null, $search);
        // $query = $qb->getQuery();
        // $adapter = new DoctrineAdapter(new ORMPaginator($query));

        // $paginator = new Paginator($adapter);
        // $paginator->setItemCountPerPage(50);
        // $paginator->setCurrentPageNumber($this->getEvent()->getRouteMatch()->getParam('p'));

        // $roles = $this->getAdminUserService()->getRoleMapper()->findAll();

        // return new ViewModel(
        //     array(
        //         'users' => $paginator,
        //         'userlistElements' => $this->getOptions()->getUserListElements(),
        //         'filter'    => $filter,
        //         'roleId'    => $roleId,
        //         'roles'     => $roles,
        //         'search'    => $search,
        //     )
        // );

        $qb = $this->getAdminUserService()->getQueryUsersByRole();
        /* @var $grid \ZfcDatagrid\Datagrid */
        $grid = $this->getServiceLocator()->get('ZfcDatagrid\Datagrid');
        $grid->setTitle('Users');
        $grid->setDataSource($qb);

        $col = new Column\Select('id', 'u');
        $col->setLabel('Id');
        $col->setIdentity(true);
        $grid->addColumn($col);
        
        $colType = new Type\DateTime(
            'Y-m-d H:i:s',
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM
        );
        $colType->setSourceTimezone('UTC');

        $col = new Column\Select('created_at', 'u');
        $col->setLabel('Created');
        $col->setType($colType);
        $grid->addColumn($col);

        $col = new Column\Select('username', 'u');
        $col->setLabel('Username');
        $grid->addColumn($col);

        $col = new Column\Select('email', 'u');
        $col->setLabel('Email');
        $grid->addColumn($col);

        $col = new Column\Select('firstname', 'u');
        $col->setLabel('Firstname');
        $grid->addColumn($col);

        $col = new Column\Select('lastname', 'u');
        $col->setLabel('Lastname');
        $grid->addColumn($col);

        $col = new Column\Select('optin', 'u');
        $col->setLabel('Optin');
        $grid->addColumn($col);

        $col = new Column\Select('optinPartner', 'u');
        $col->setLabel('Optin Partner');
        $grid->addColumn($col);

        $col = new Column\Select('optin2', 'u');
        $col->setLabel('Optin 2');
        $grid->addColumn($col);

        $col = new Column\Select('optin3', 'u');
        $col->setLabel('Optin 3');
        $grid->addColumn($col);

        $col = new Column\Select('roleId', 'r');
        $col->setLabel('Role');
        $grid->addColumn($col);

        $actions = new Column\Action();
        $actions->setLabel('');
        //$actions->setAttribute('style', 'white-space: nowrap');

        $viewAction = new Column\Action\Button();
        $viewAction->setLabel('Reset Password');
        $rowId = $viewAction->getRowIdPlaceholder();
        $viewAction->setLink('/admin/user/reset/'.$rowId);
        $actions->addAction($viewAction);

        $viewAction = new Column\Action\Button();
        $viewAction->setLabel('Edit');
        $rowId = $viewAction->getRowIdPlaceholder();
        $viewAction->setLink('/admin/user/edit/'.$rowId);
        $actions->addAction($viewAction);

        $viewAction = new Column\Action\Button();
        $viewAction->setLabel('Inactivate');
        $rowId = $viewAction->getRowIdPlaceholder();
        $viewAction->setLink('/admin/user/remove/'.$rowId);
        $actions->addAction($viewAction);

        $grid->addColumn($actions);

        // $action = new Action\Mass();
        // $action->setTitle('This is incredible');
        // $action->setLink('/test');
        // $action->setConfirm(true);
        // $grid->addMassAction($action);

        $grid->setToolbarTemplateVariables(
            [
                'globalActions' => [
                    $this->translate('New User') => $this->adminUrl()->fromRoute('playgrounduser/create', ['userId' => 0])
                ]
            ]
        );
        $grid->render();
        
        return $grid->getResponse();
    }

    public function downloadAction()
    {
        $filter        = $this->getEvent()->getRouteMatch()->getParam('filter');
        $roleId        = $this->getEvent()->getRouteMatch()->getParam('roleId');
        $search        = $this->params()->fromQuery('name');

        $role        = $this->getAdminUserService()->getRoleMapper()->findByRoleId($roleId);

        $users = $this->getAdminUserService()->getArrayUsersByRole($role, null, $search);
        
        $content = "\xEF\xBB\xBF"; // UTF-8 BOM
        $content .= $this->getAdminUserService()->getCSV($users);

        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Encoding: UTF-8');
        $headers->addHeaderLine('Content-Type', 'text/csv; charset=UTF-8');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"users.csv\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));

        $response->setContent($content);

        return $response;
    }

    public function createAction()
    {
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

        $adapter = new \Laminas\Paginator\Adapter\ArrayAdapter($this->getAdminUserService()->getRoleMapper()->findAll());

        $paginator = new \Laminas\Paginator\Paginator($adapter);
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

    public function setUserMapper(\PlaygroundUser\Mapper\User $userMapper)
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
