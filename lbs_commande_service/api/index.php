<?php
require_once __DIR__ . '/../src/vendor/autoload.php';

$api_settings = require_once __DIR__ . '/../src/api/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/api/conf/api_errors.php';
use lbs\commande\api\controller\CommandeController;

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

$app->get('/commandes[/]', CommandeController::class.':getCommandes');
 
$app->get('/commandes/{id}[/]',
\lbs\commande\api\controller\CommandeController::class.':getCommande')->setName('commande');

try{
    $app->run();
}catch(Twrowable $e){

}


