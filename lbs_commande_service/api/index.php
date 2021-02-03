<?php
require_once __DIR__ . '/../src/vendor/autoload.php';

$api_settings = require_once __DIR__ . '/../src/api/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/api/conf/api_errors.php';
$api_depend = require_once __DIR__ . '/../src/api/conf/api_dependencies.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config = require_once __DIR__ .  '/../src/conf/settings.php';
$c = new \Slim\Container($config);

$app = new \Slim\App($c);

//\lbs\commande\api\bootstrap\LbsBootstrap::startEloquent($api_container->settings['dbconf']);

$app->get('/TD1/commandes/{name}[/]',
 function (Request $req, Response $resp, array $args):Response {
     $controleur = new \lbs\commande\api\controller\CommandeController($this);
     return $controleur->sayHello($req, $resp, $args);
    }
); 

$app->get('/TD1/commanddes/{id}[/]',
    \lbs\commande\api\controller\CommandeController::class . ':uneCommande')
    ->setName('commande');

$app->run();
