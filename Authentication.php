<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest;

use Core\Utils\Curl;
use DI\DependencyException;
use DI\NotFoundException;
use Exception;

class Authentication {

    use RestTrait;

    /**
     * @var mixed
     */
    protected mixed $message;

    /**
     * @param string $token
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    public function valid(string $token): bool {
        $token = str_replace('Bearer ', '', $token);

        // http://localhost:8081/oauth/check_token?token=$token
        /*
           app.oauth.uri.check_token = http://localhost:8081/oauth/check_token?token=$token
           app.oauth.client.id = clientid
           app.oauth.client.secret = test
         */

        if ((bool)$this->getConfig()['auth']['external'] === true){
            //return true;
            $token_info = Curl::get('http://localhost:8081/oauth/check_token?token='.$token);
            $token_info = json_decode($token_info, true);
            if (isset($token_info['active']) && $token_info['active'] === true){
                return true;
            }
            else {
                $this->message = $token_info['error_description'];
                return false;
            }
        }
        else {
            $api_token = $this->getRestManager()->getApiTokenEntity()::where([
                ['access_token', '=', $token],
                ['active', '=', 1]
            ])->first();
            if (!is_null($api_token)){
                $last_update = strtotime($api_token->updated_at);
                $next_update = $last_update + $api_token->expires_in;
                if ($next_update > time()){
                    return true;
                }
                else {
                    $this->message = "Token ist abgelaufen";
                    return false;
                }
            }
            else {
                $this->message = "Unbekannte Token";
                return false;
            }
        }
    }

    /**
     * @return mixed
     */
    public function getMessage(): mixed {
        return $this->message;
    }

}
