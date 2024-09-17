<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest;

use Modules\Rest\Controller\IndexController;

class Router extends \Core\Module\Router {

    use RestTrait;

    /**
     * @var string
     */
    public string $routerType = "api-docs";

    /**
     * @var string
     */
    public string $router = "/api-docs";

    /**
     * @var array|string[][]
     */
    public array $mapForUriBuilder = [
        'swagger' => [
            'callback' => 'swagger',
            'pattern' =>'/v{version:[0-9]+}',
            'method' => ['GET']
        ],
    ];

    public string $controller = IndexController::class;

}
