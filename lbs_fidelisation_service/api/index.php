<?php
require_once __DIR__ . '/../src/vendor/autoload.php';

$api_settings = require_once __DIR__ . '/../src/api/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/api/conf/api_errors.php';
use lbs\fidelisation\api\controller\FidelisationController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use lbs\fidelisation\api\middlewares\Cors;

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);


\lbs\fidelisation\api\bootstrap\LbsBootstrap::startEloquent($api_container->settings['db']);

$app->post('/cartes/{id}/auth[/]', FidelisationController::class.':getAuth')->setName('auth')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');

$app->get('/cartes/{id}', FidelisationController::class.':getCommandesAuth')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');
try{
    $app->run();
}catch(Twrowable $e){

}
