<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Exceptions\Error;

use Modules\Rest\Exceptions\Errors;
use Core\Exception;

class NotFound extends Exception implements Errors {}
