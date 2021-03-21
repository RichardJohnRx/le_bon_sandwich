<?php

namespace lbs\fidelisation\api\controller;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\fidelisation\api\model\Carte_fidelite;
use lbs\fidelisation\api\utils\Writer;
use Ramsey\Uuid\Uuid;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException ;
use Firebase\JWT\BeforeValidException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Client;

class FidelisationController{

    private $c; 

    public function __construct(\Slim\Container $c){
        $this->c = $c;
    }

    public function getAuth(Request $req, Response $res,array $args): Response{

        if($req->hasHeader('Authorization')){

            $id = $args['id'];
            $auth = base64_decode(explode(" ",$req->getHeader('Authorization')[0])[1]);
            list($user,$pass) = explode(':',$auth);
    
            try{
                $carte_fidelite = Carte_fidelite::select('id','nom_client','mail_client','passwd')->where('id','=',$id)->firstOrFail();
    
                if(!password_verify($pass, $carte_fidelite->passwd)){
                    throw new \Exception("Password failed");
                }
                
                unset($carte_fidelite->passwd);
    
                $token = JWT::encode([
                    'iss' => 'http://api.fidelisation.local/'.$id.'/auth',
                    'aud' => 'http://api.fidelisation.local',
                    'iat' => time(),
                    'exp' => time()+3600,
                    'cid' => $carte_fidelite->id
                ], $this->c->settings['secrets'], 'HS512');
        
                $data = [
                    'Carte' => $carte_fidelite->toArray(),
                    'JWT' => $token
                ];
                $res = $res->withStatus(200)->withHeader('Content-Type','application/json');
                $res->getBody()->write(json_encode($data));

                return $res;

            }catch(\Exception $e){
                $res = $res->withStatus(500)->withHeader('Content-Type','application/json');
                $res->getBody()->write(json_encode($res));
                return $res;
            }

        }else{
            $res = $res->withStatus(401)->withHeader('Content-Type','application/json');
            $res->getBody()->write(
                json_encode(
                    array(
                        'type' => 'error',
                        'error' => 401,
                        'message' => 'no authorization header present'
                    )
                )
            );

            return $res;
        }
    }

    public function getCommandesAuth(Request $req, Response $res,array $args): Response{
        if($req->hasHeader('Authorization')){
            
            try{

                $secrets = $this->c->settings['secrets'];
                $h = $req->getHeader('Authorization')[0];
                $tokenstring= sscanf($h, "Bearer %s")[0];
                $token = JWT::decode($tokenstring, $secrets, ['HS512']);
                $carte = Carte_fidelite::Select('nom_client','mail_client','cumul_achats', 'created_at', 'updated_at', 'cumul_commandes')->where('id','=',$token->cid)->firstOrFail();
                $res = $res->withStatus(200)->withHeader('Content-Type','application/json');
                $res->getBody()->write(json_encode($carte));
                return $res;

            }catch(ExpiredException $e){
                $res = $res->withStatus(401)->withHeader('Content-Type','application/json');
                $res->getBody()->write(json_encode($e));
                return $res;
            }catch(SignatureInvalidException $e){
                $res = $res->withStatus(401)->withHeader('Content-Type','application/json');
                $res->getBody()->write(json_encode($e));
                return $res;
            }catch (BeforeValidException $e){
                $res = $res->withStatus(401)->withHeader('Content-Type','application/json');
                $res->getBody()->write(json_encode($e));
                return $res;
            }catch(\UnexpectedValueException $e){
                $res = $res->withStatus(401)->withHeader('Content-Type','application/json');
                $res->getBody()->write(json_encode($e));
                return $res;
            }
        
        }else{
            $res = $res->withStatus(401)
            ->withHeader('Content-Type','application/json')->withHeader('WWW-authenticate');
            $res->getBody()->write(
                json_encode(
                    array(
                        'type' => 'error',
                        'error' => 401,
                        'message' => 'No authorization header present'
                    )
                )
            );
            return $res;
        }
    }
}