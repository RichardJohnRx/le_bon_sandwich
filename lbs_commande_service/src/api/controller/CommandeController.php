<?php


namespace lbs\commande\api\controller;


use Slim\Http\Request;
use Slim\Http\Response;

class CommandeController{

    public function getCommande(Request $rq, Response $rs, array $args): Response{
        $commandes = Commande::select(['id','mail','montant'])->get();
        $rs = $rs->withStatus(200)
            ->withHeader('Content-Type','application/json;charset=utf-8');
        $rs->getBody()->write(json_encode([
            'type' => 'collection',
            'count' => count($commandes),
            'commandes'  => $commandes->toArray()
        ]));
        return $rs;
    }

    public function replaceCommande(Request $rq, Response $rs, array $args): Response{
        $commande_data = $rq->getParsedBody();
        if(!isset($commande_data['nom_client']))
            return Writer::json_error($rs,400,"missing data : nom_client");
        if(!isset($commande_data['mail_client']))
            return Writer::json_error($rs,400,"missing data : mail_client");
    }

}