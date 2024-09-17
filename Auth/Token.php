<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Auth;

use Modules\Rest\Exceptions\Error\Token as TokenError;
use Slim\Http\ServerRequest as Request;

class Token {

    /**
     * @var array
     */
    private array $options = [];

    /**
     * Token constructor.
     * @param array $options
     */
    public function __construct(array $options = []) {
        $this->options = $options;
        return $this;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws TokenError
     */
    public function check(Request $request): mixed {
        $token = null;

        /** Check for token on header */
        if (isset($this->options['header']) && $request->hasHeader($this->options['header'])) {
            $token = $request->getHeader($this->options['header']);
        }

        /** If nothing on header, try query parameters */
        if (!$token && isset($this->options['parameter']) && !empty($request->getQueryParams()[$this->options['parameter']])) {
            $token = $request->getQueryParams()[$this->options['parameter']];
        }

        /** If nothing on query parameters, try cookies */
        if (!$token && isset($this->options['cookie'])) {
            $cookie_params = $request->getCookieParams();
            if (!empty($cookie_params[$this->options["cookie"]])) {
                $token = $cookie_params[$this->options["cookie"]];
            }
        }

        /** If nothing until now, check argument as last try */
        if (!$token && isset($this->options['argument']) && $route = $request->getAttribute('route')) {
            $argument = $route->getArgument($this->options['argument']);
            if (!empty($argument)) {
                $token = $argument;
            }
        }

        if ($token) {
            return $token;
        }

        throw new TokenError('Token not found');
    }

}
