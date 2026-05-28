<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest;

use Core\Module\Provider;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Database\MigrationCollection;
use Modules\Rest\Console\Hash;
use Modules\Rest\Db\Schema;
use Modules\Rest\Manager\RestManager;
use Modules\Rest\Manager\RestModel;

class ServiceProvider extends Provider {

    /**
     * @var string
     */
    private string $route = "Rest\Router";

    /**
     * @var string
     */
    private string $routeApi = "Rest\ApiRouter";

    /**
     * @return string[]
     */
    public function console(): array {
        return [
            Hash::class
        ];
    }

    /**
     * @return void
     */
    public function init(): void {
        $container = $this->getContainer();
        if (!$container->has('Rest\Manager')) {
            $manager = new RestManager($this);
            $container->set('Rest\Manager', $manager->initEntity());
        }

        if (!$container->has('Rest\Model')){
            $container->set('Rest\Model', new RestModel($this));
        }


        if (!$container->has($this->route)){
            $container->set($this->route, new Router($this));
        }

        if (!$container->has($this->routeApi)){
            $container->set($this->routeApi, new ApiRouter($this));
        }
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function afterInit(): void {
        $container = $this->getContainer();
        if ($container->has('Modules\Database\ServiceProvider::Migration::Collection')) {
            /* @var $databaseMigration MigrationCollection  */
            $container->get('Modules\Database\ServiceProvider::Migration::Collection')->add(new Schema($this));
        }
    }

    /**
     * @return void
     */
    public function boot(): void {
        $container = $this->getContainer();
        $container->set('Modules\Rest\Controller\IndexController', new Controller\IndexController($this));

        $container->set('Modules\Rest\ApiController\IndexController', new ApiController\IndexController($this));
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function register(): void {
        $container = $this->getContainer();
        if ($container->has($this->route)){
            $container->get($this->route)->init();
        }

        if ($container->has($this->routeApi)){
            $container->get($this->routeApi)->init();
        }
    }
}
