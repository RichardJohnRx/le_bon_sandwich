<?php
require_once __DIR__ . '/../src/vendor/autoload.php';

$api_settings = require_once __DIR__ . '/../src/api/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/api/conf/api_errors.php';
$api_depend = require_once __DIR__ . '/../src/api/conf/api_dependencies.php';

$api_container = new \Slim\Container(array_merge($api_settings,$api_errors,$api_depend));

$app = new \Slim\App($api_container);

\lbs\commande\api\bootstrap\LbsBootstrap::startEloquent($api_container->settings['dbconf']);

$app->get('/TD1/commandes[/]',
    \lbs\commande\api\controller\CommandeController::class . ':listCommandes');

$app->get('/TD1/commandes/{id}[/]',
    \lbs\commande\api\controller\CommandeController::class . ':uneCommande')
    ->setName('commande');

$app->post('/commandes[/]',
    \lbs\commande\api\controller\CommandeController::class . ':createCommande')
    ->add(new sv(cv::create_validators()));

$app->post('/commandes/{id}/paiement[/]',
    \lbl\commande\api\controller\CommandeController::class . ':payCommande')
    ->add(\lbs\commande\api\middlewares\Token::class . ':check')
    ->add(new sv(cv::payment_validators()))
    ->setName('paiement');


