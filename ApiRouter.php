<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest;

use Modules\Rest\ApiController\IndexController;

class ApiRouter extends \Core\Module\ApiRouter {

    use RestTrait;

    /**
     * @var int
     */
    public int $version = 1;

    /**
     * @var string
     */
    public string $routerType = "oauth";

    /**
     * @var array|string[][]
     */
    public array $mapForUriBuilder = [
        'token'=>[
            'callback' => 'token',
            'pattern' =>'/token',
            'method' => ['GET', 'POST']
        ],
        'check_token'=>[
            'callback' => 'checkToken',
            'pattern' =>'/check_token',
            'method' => ['GET']
        ]
    ];

    public string $controller = IndexController::class;

}
