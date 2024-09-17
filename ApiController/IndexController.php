<?php

/**
 * @author Sergey Tevs
 * @email sergey@tevs.org
 */

namespace Modules\Rest\ApiController;

use DI\DependencyException;
use DI\NotFoundException;
use Modules\Rest\Manager\AbstractManager;
use Modules\Rest\RestTrait;
use Slim\Http\Response;
use Slim\Http\ServerRequest as Request;

class IndexController extends AbstractManager {

    use RestTrait;

    /**
     * @var string
     */
    private string $secret = 'apiSecret';

    /**
     * @var string
     */
    private string $hash;

    /**
     * @return array
     */
    public function options():array  {
        return [
            self::VERSION => 1,
            self::METHOD => 'oauth'
        ];
    }

    /**
     * @return void
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function registerFunctions(): void {
        $this->getRestApiRouter()->getMapBuilder($this);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function token(Request $request, Response $response): Response {
        $data = $request->getParsedBody();
        $user = $this->getRestManager()->getApiUserEntity()::where([
            ['username', '=', $data['username']],
            ['client_id', '=', $data['client_id']],
            ['active', '=', 1]
        ])->get();

        if ($user->count()>0) {
            $user = $user[0];

            $this->setHash($data['password']);
            $data['password'] = $this->getHash();

            $this->setHash($data['client_secret']);
            $data['client_secret'] = $this->getHash();

            if ($user->password !== $data['password'] || $user->client_secret !== $data['client_secret']){
                return $response->withJson([
                    'success'=>false,
                    'error'=>'Password or Client Secret is Invalid'
                ], 401);
            }

            if ($user->api_token !== null){
                $token = $user->api_token;
                $token->access_token = self::generateUUID();
                $token->refresh_token = self::generateUUID();
                $token->scope = $data['scope'];
                $token->token_type = 'bearer';
                $token->expires_in = (int)$this->getConfig("api")['expiresIn'];
            }
            else {
                $apiTokenClass = $this->getRestManager()->getApiTokenEntity();
                $token = new $apiTokenClass();
                $token->api_user_id = $user->id;
                $token->access_token = self::generateUUID();
                $token->refresh_token = self::generateUUID();
                $token->scope = $data['scope'];
                $token->token_type = 'bearer';
                $token->expires_in = (int)$this->getConfig("api")['expiresIn'];
                $token->active = 1;
            }
            $token->save();

            return $response->withJson([
                'access_token'=>$token->access_token,
                'expires_in'=>$token->expires_in,
                'refresh_token'=>$token->refresh_token,
                'scope'=>$token->scope,
                'token_type'=>$token->token_type
            ], 200);
        }
        else {
            return $response->withJson([
                'success'=>false,
                'error'=>'Unknown Username or Client ID',
            ], 401);
        }
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function checkToken(Request $request, Response $response): Response {
        if (isset($request->getQueryParams()['token']) && self::isValidUUID($request->getQueryParams()['token'])){
            $token = $request->getQueryParams()['token'];
            $api_token = $this->getRestManager()->getApiTokenEntity()::where([
                ['refresh_token', '=', $token],
                ['active', '=', 1]
            ])->get();
            if ($api_token->count()>0){
                $api_token = $api_token[0];
                $expires_time = strtotime($api_token->updated_at) + (int)$this->getConfig("api")['refreshTime'];
                if (time()<$expires_time){
                    $api_token->access_token = self::generateUUID();
                    $api_token->refresh_token = self::generateUUID();
                    $api_token->expires_in=(int)$this->getConfig("api")['expiresIn'];
                    $api_token->save();

                    return $response->withJson([
                        'access_token'=>$api_token->access_token,
                        'expires_in'=>$api_token->expires_in,
                        'refresh_token'=>$api_token->refresh_token,
                        'scope'=>$api_token->scope,
                        'token_type'=>$api_token->token_type
                    ], 200);
                }
                else {
                    $data = [
                        'success'=>false,
                        'error'=>'Token has expired',
                    ];
                }
            }
            else {
                $data = [
                    'success'=>false,
                    'error'=>'Unknown Token',
                ];
            }
        }
        else {
            $data = [
                'success'=>false,
                'error'=>'Token is required',
            ];
        }
        return $response->withJson($data, 401);
    }

    /**
     * @param string $str
     * @return void
     */
    public function setHash(string $str): void {
        $hash = hash("sha512", $str);
        $this->hash= hash("sha512", $this->secret.strrev($this->secret.$hash));
    }

    /**
     * @return string
     */
    public function getHash(): string {
        return $this->hash;
    }

    /**
     * @param string $uuid
     * @return bool
     */
    private static function isValidUUID(string $uuid): bool {
        return (bool) preg_match('#^[a-f\d]{8}-(?:[a-f\d]{4}-){3}[a-f\d]{12}$#D', $uuid);
    }

    /**
     * @param bool $keepDashes
     * @return array|string|string[]
     */
    private static function generateUUID(bool $keepDashes = true): array|string {
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
        return $keepDashes ? $uuid : str_replace('-', '', $uuid);
    }

}
