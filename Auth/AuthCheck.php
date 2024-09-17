<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\Auth;

use Core\Exception;
use Core\Utils\Curl;
use DI\DependencyException;
use DI\NotFoundException;
use Modules\Rest\RestTrait;

class AuthCheck {

    use RestTrait;

    /**
     * @var mixed
     */
    protected mixed $message;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param string $token
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException|Exception
     */
    public function valid(string $token): bool {
        $token = str_replace('Bearer ', '', $token);
        if ((bool)$this->getConfig('auth')['external'] === true){
            $token_info = Curl::get('http://localhost:8081/oauth/check_token?token='.$token);
            $token_info = json_decode($token_info, true);
            if (isset($token_info['active']) && $token_info['active'] === true){
                return true;
            }
            else {
                $this->message = $token_info['error_description'];
            }
        }
        else {
            $api_token = $this->getRestManager()->getApiTokenEntity()::where([
                ['access_token', '=', $token],
                ['active', '=', 1]
            ])->get();
            if ($api_token->count()>0){
                $api_token = $api_token[0];
                $last_update = strtotime($api_token->updated_at);
                $next_update = $last_update + $api_token->expires_in;
                if ($next_update > time()){
                    return true;
                }
                else {
                    $this->message = "Token has expired";
                }
            }
            else {
                $this->message = "Unknown Token";
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getMessage(): mixed {
        return $this->message;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function getConfig(string $key): mixed {
        if (empty($this->config)){
            $this->config = $this->getContainer()->get('config')->getSetting('api');
        }
        if (!empty($key)){
            return $this->config[$key];
        }
        return null;
    }

}
