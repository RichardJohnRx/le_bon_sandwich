<?php
require_once __DIR__ . '/../src/vendor/autoload.php';

$api_settings = require_once __DIR__ . '/../src/api/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/api/conf/api_errors.php';
use lbs\commande\api\controller\CommandeController;
use \DavidePastore\Slim\Validation\Validation as Validation;
use lbs\commande\api\middlewares\Cors;

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);

\lbs\commande\api\bootstrap\LbsBootstrap::startEloquent($api_container->settings['db']);

$app->get('/TD1/commandes[/]',
 function (Request $req, Response $resp, array $args):Response {
     $controleur = new \lbs\commande\api\controller\CommandeController($this);
     return $controleur->listCommandes($req, $resp, $args);
    }
); 

$app->get('/TD1/commandes/{id}[/]',
function (Request $req, Response $resp, array $args):Response {
    $controleur = new \lbs\commande\api\controller\CommandeController($this);
    return $controleur->uneCommande($req, $resp, $args);
   }
); 

$app->get('/commandes[/]', CommandeController::class.':getCommandes')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');

$app->post('/commandes[/]', CommandeController::class.':createCommande')->add(new Validation(lbs\commande\api\validator\CommandValidator::validators()))->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');
 
$app->get('/commandes/{id}[/]',
\lbs\commande\api\controller\CommandeController::class.':getCommande')->setName('commande')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

try{
    $app->run();
}catch(Twrowable $e){

}


