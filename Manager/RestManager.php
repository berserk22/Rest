<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Manager;

use Core\Traits\App;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Rest\Db\Models\ApiToken;
use Modules\Rest\Db\Models\ApiUser;

class RestManager {

    use App;

    /**
     * @return $this
     */
    public function initEntity(): static {
        if (!$this->getContainer()->has('Rest\ApiUser')){
            $this->getContainer()->set('Rest\ApiUser', function () {
                return "Modules\Rest\Db\Models\ApiUser";
            });
        }

        if (!$this->getContainer()->has('Rest\ApiToken')){
            $this->getContainer()->set('Rest\ApiToken', function ($c) {
                return "Modules\Rest\Db\Models\ApiToken";
            });
        }

        return $this;
    }

    /**
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getApiUserEntity(): string {
        return $this->getContainer()->get('Rest\ApiUser');
    }

    /**
     * @return string
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function getApiTokenEntity(): string {
        return $this->getContainer()->get('Rest\ApiToken');
    }
}
