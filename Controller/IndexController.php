<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Controller;

use Core\Module\Controller;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Rest\RestTrait;
use Modules\Router\Methods;
use OpenApi\Analysers\TokenAnalyser;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class IndexController extends Controller {

    use RestTrait;

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function registerFunctions(): void {
        $this->getRestRouter()->getMapBuilder($this);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function swagger(Request $request, Response $response): Response {
        $version = "v".$request->getAttribute('version');
        if ($this->getContainer()->has('Router\Methods')){
            /** @var $methods Methods */
            $methods = $this->getContainer()->get('Router\Methods');
            $files = [];
            $analyser = new TokenAnalyser();
            $openapi = (new Generator())
                ->setProcessors([])
                ->setAnalyser($analyser)
                ->setVersion(OpenApi::VERSION_3_0_0);
            $analyser->setGenerator($openapi);
            foreach ($methods->getRoutingsList() as $key => $module) {
                if (!is_int($key) && strstr($key, $version.'_')){
                    $dir = str_replace(['\\', 'Modules'], ['/', 'modules'], $module['instance'].'.php');
                    $files[]=realpath(__DIR__.'/../../../').'/'.$dir;
                    $openapi->addNamespace($module['instance']);
                }
            }
            $openapi =(new Generator())->generate($files);
            header('Content-Type: application/json');
            echo $openapi->toJson();
            exit;
        }
        return $this->getView()->render($response, 'error/404')->withStatus(404);
    }
}
