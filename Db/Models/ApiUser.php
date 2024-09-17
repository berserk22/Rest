<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Db\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Database\Model;

class ApiUser extends Model {

    protected $table = 'api_user';

    /**
     * @return HasOne
     */
    public function api_token(): HasOne {
        return $this->hasOne('Modules\Rest\Db\Models\ApiToken');
    }

}
