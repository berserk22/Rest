<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest;

use Core\Traits\App;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Rest\Manager\RestManager;

trait RestTrait {

    use App;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getRestManager(): RestManager {
        return $this->getContainer()->get('Rest\Manager');
    }

    /**
     * @return Router|string|null
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getRestRouter(): Router|string|null {
        return $this->getContainer()->get('Rest\Router');
    }

    /**
     * @return Router|string|null
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getRestApiRouter(): ApiRouter|string|null {
        return $this->getContainer()->get('Rest\ApiRouter');
    }

}
