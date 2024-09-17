<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Db\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Database\Model;

class ApiToken extends Model {

    protected $table = 'api_token';

    public function api_user(): BelongsTo {
        return $this->belongsTo('Modules\Rest\Db\Models\ApiUser');
    }

}
