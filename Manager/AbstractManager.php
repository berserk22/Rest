<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Manager;

use Core\Module\Controller;
use ReflectionException;
use Slim\Http\Response;

abstract class AbstractManager extends Controller {

    const VERSION = 1;

    const METHOD = 2;

    /**
     * @return array
     */
    abstract public function options():array;

    /**
     * @param string $resource
     * @return object
     * @throws ReflectionException
     */
    protected function loadResource(string $resource): object {
        $reflection = new \ReflectionClass($resource);
        return $reflection->newInstanceWithoutConstructor();
    }

    /**
     * @param Response $response
     * @param array $data
     * @param int $code
     * @return Response
     */
    protected function response(Response $response, array $data = [], int $code = 200):Response {
        return $response->withJson(['success' => true, 'data' => $data], $code);
    }

}
