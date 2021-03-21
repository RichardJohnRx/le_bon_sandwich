<?php
require_once __DIR__ . '/../src/vendor/autoload.php';

$api_settings = require_once __DIR__ . '/../src/api/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/api/conf/api_errors.php';
use lbs\catalogue\api\controller\CatalogueController;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use lbs\catalogue\api\middlewares\Cors;

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);


\lbs\catalogue\api\bootstrap\LbsBootstrap::startEloquent($api_container->settings['db']);

//Pagination et filtre
$app->get('/sandwichs[/]', CatalogueController::class.':getSandwichs')->setName('sandwichs')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');

$app->get('/categories/{id}/sandwichs[/]', CatalogueController::class.':getSandwichCategories')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');

$app->get('/sandwichs/{id}[/]', CatalogueController::class.':getSandwich')->setName('sandwich');

$app->get('/categories/{id}[/]', CatalogueController::class.':getCategorie')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');

$app->get('/categories[/]', CatalogueController::class.':getCategories')->add(Cors::class.':checkHeaderOrigin')->add(Cors::class.':headersCORS');

try{
    $app->run();
}catch(Twrowable $e){

}
 