<?php
namespace PlaygroundUser\View\Helper;

use PlaygroundUser\View\Helper\UserListWidget;
use Interop\Container\ContainerInterface;

class UserListWidgetFactory
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return mixed
     */
    public function __invoke(ContainerInterface $container)
    {
        $gameService = $container->get(\PlaygroundUser\Service\User::class);
        return new UserListWidget($gameService);
    }
}