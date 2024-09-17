<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Auth;

use Core\Exception;
use Modules\Rest\Exceptions\Error\Token as TokenError;
use Modules\Rest\RestTrait;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Http\ServerRequest as Request;
use Slim\Interfaces\RouteInterface;
use UnexpectedValueException;

class Auth {

    use RestTrait;

    /**
     * @var array
     */
    private array $options = [
        'secure' => true,
        'relaxed' => ['127.0.0.1'],
        'path' => null,
        'passthrough' => null,
        'authenticator' => null,
        'error' => null,
        'header' => 'Authorization',
        'regex' => '/Bearer\s+(.*)$/i',
        'parameter' => 'token',
        'cookie' => 'token',
        'argument' => 'token'
    ];

    /**
     * @var array
     */
    private array $response = [];

    /**
     * @param array $options
     */
    public function __construct(array $options = []) {
        /** Rewrite options */
        $this->fill($options);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param RouteInterface $runner
     * @return Response
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response, RouteInterface $runner): Response {
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();

        $responseToReturn = null;

        /** If rules say we should not authenticate call next and return. */
        if (false === $this->shouldAuthenticate($request)) {
            $responseToReturn = $runner->handle($request);
        }

        /** HTTP allowed only if secure is false or server is in relaxed array. */
        if (!$responseToReturn && true === $this->options["secure"] && (!in_array($host, $this->options["relaxed"]) && "https" !== $scheme)) {
            $responseToReturn = $response->withJson(['message' => 'Required HTTPS for token authentication.'], 401);
        }

        /** Call custom authenticator function */
        if (!$responseToReturn && empty($this->options['authenticator'])) {
            throw new UnexpectedValueException('authenticator option has not been set or it is not callable.');
        }

        if (!$responseToReturn) {
            try {
                $authenticated_request = $this->options['authenticator']($request, $this);
                if (!$authenticated_request) {
                    $responseToReturn = $this->error($request, $response);
                } else {
                    $responseToReturn = $runner->run($request);
                }
            } catch (\Modules\Rest\Exceptions\Errors $e) {
                $this->setResponseMessage($e->getMessage());
                $responseToReturn = $this->error($request, $response);
            }
        }

        return $responseToReturn;
    }


    /**
     * @param array $options
     */
    public function fill(array $options = []): void {
        foreach ($options as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function shouldAuthenticate(Request $request):bool {
        $uri = $request->getUri()->getPath();
        $uri = '/' . trim($uri, '/');

        /** If request path is matches passthrough should not authenticate. */
        foreach ((array)$this->options["passthrough"] as $passthrough) {
            $passthrough = rtrim($passthrough, "/");
            if (preg_match("@^{$passthrough}(/.*)?$@", $uri)) {
                return false;
            }
        }

        /** Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["path"] as $path) {
            $path = rtrim($path, "/");
            if (preg_match("@^{$path}(/.*)?$@", $uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws Exception
     */
    public function error(Request $request, Response $response):Response {
        /** If exists a custom error function callable, ignore remaining code */
        if (!empty($this->options['error'])) {
            $custom_error_response = $this->options['error']($request, $response, $this);
            if ($custom_error_response instanceof Response) {
                return $custom_error_response;
            } else {
                throw new Exception("The error function must return an object of class Response.");
            }
        }

        if ($this->getResponseMessage()) {
            $res['message'] = $this->getResponseMessage();
        }
        else {
            $res['message'] = 'Invalid authentication token';
        }

        if ($this->getResponseToken()) {
            $res['token'] = $this->getResponseToken();
        }

        return $response->withJson($res, 401, JSON_PRETTY_PRINT);
    }

    /**
     * @param Request $request
     * @return String
     * @throws TokenError
     */
    public function getToken(Request $request):string {
        $token = new Token([
            'header' => $this->options['header'],
            'regex' => $this->options['regex'],
            'parameter' => $this->options['parameter'],
            'cookie' => $this->options['cookie'],
            'argument' => $this->options['argument']
        ]);

        $token = $token->check($request);
        $request->withAttribute('authorization', $token);
        $this->setResponseToken($token);
        return is_array($token)?$token[0]:$token;
    }

    /**
     * @param $message
     * @return Auth
     */
    public function setResponseMessage($message): Auth {
        $this->response['message'] = $message;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getResponseMessage(): mixed {
        return $this->response['message'] ?? null;
    }

    /**
     * @param $token
     * @return Auth
     */
    public function setResponseToken($token): Auth {
        $this->response['token'] = $token;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getResponseToken(): mixed {
        return $this->response['token'] ?? null;
    }

    /**
     * @param array $args
     * @return Auth|null
     */
    public function setResponseArray(array $args = []): null|static {
        foreach ($args as $name => $text) {
            return $this->response[$name] = $text;
        }
        return $this;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getResponseByName($name): mixed {
        return $this->response[$name] ?? null;
    }

    /**
     * @param $secure
     * @return Auth
     */
    public function setSecure($secure): Auth {
        $this->options['secure'] = (bool) $secure;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSecure(): mixed {
        return $this->options['secure'];
    }

    /**
     * @param $relaxed
     * @return Auth
     */
    public function setRelaxed($relaxed): Auth {
        $this->options['relaxed'] = (array) $relaxed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRelaxed(): mixed {
        return $this->options['relaxed'];
    }

    /**
     * @param $path
     * @return Auth
     */
    public function setPath($path): Auth {
        $this->options['path'] = (array) $path;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath(): mixed {
        return $this->options['path'];
    }

    /**
     * @param $passthrough
     * @return Auth
     */
    public function setPassthrough($passthrough): Auth {
        $this->options['passthrough'] = (array) $passthrough;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassthrough(): mixed {
        return $this->options['passthrough'];
    }

    /**
     * @param callable $error
     * @return Auth
     */
    public function setError(callable $error): Auth {
        $this->options['error'] = $error;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getError(): mixed {
        return $this->options['error'];
    }

    /**
     * @param callable $authenticator
     * @return Auth
     */
    public function setAuthenticator(callable $authenticator): Auth {
        $this->options['authenticator'] = $authenticator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthenticator(): mixed {
        return $this->options['authenticator'];
    }

    /**
     * @param $header
     * @return Auth
     */
    public function setHeader($header): Auth {
        $this->options['header'] = $header;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHeader(): mixed {
        return $this->options['header'];
    }

    /**
     * @param $regex
     * @return Auth
     */
    public function setRegex($regex): Auth {
        $this->options['regex'] = $regex;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRegex(): mixed {
        return $this->options['regex'];
    }

    /**
     * @param $parameter
     * @return Auth
     */
    public function setParameter($parameter): Auth {
        $this->options['parameter'] = $parameter;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParameter(): mixed {
        return $this->options['parameter'];
    }

    /**
     * @param $argument
     * @return Auth
     */
    public function setArgument($argument): Auth {
        $this->options['argument'] = $argument;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getArgument(): mixed {
        return $this->options['argument'];
    }

    /**
     * @param $cookie
     * @return Auth
     */
    public function setCookie($cookie): Auth {
        $this->options['cookie'] = $cookie;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCookie(): mixed {
        return $this->options['cookie'];
    }
}
