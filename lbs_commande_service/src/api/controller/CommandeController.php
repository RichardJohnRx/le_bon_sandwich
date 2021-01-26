<?php


namespace lbs\commande\api\controller;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\commande\api\model\Commande;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CommandeController{

    private $commandes = [
        ["id" => "45RF56TH","mail_client"=>"a@a.fr","date_commande"=>"1-12-2020","montant"=>50.0],
        ["id" => "46RF56TH","mail_client"=>"b@a.fr","date_commande"=>"2-12-2020","montant"=>45.0],
        ["id" => "57RF56TH","mail_client"=>"c@a.fr","date_commande"=>"3-12-2020","montant"=>27.5],
        ["id" => "01RF56TH","mail_client"=>"d@a.fr","date_commande"=>"1-12-2020","montant"=>30.0]
    ];

    public function listCommandes(Request $rq, Response $rs, array $args) : Response{
        $data = [
            "type" => "collection",
            "count" => count($this->commandes),
            "commandes" => $this->commandes
        ];

        $rs = $rs->withHeader('Content-Type','application/json');
        $rs->getBody()->write(json_encode($data));
        return $rs;
    }

    public function uneCommande(Request $rq, Response $rs, array $args) : Response{
        $id = $args['id'];
        $res = null;
        foreach ($this->commandes as $commande){
            if($commande['id'] === $id) $res = $commande;
        }
        $rs = $rs->withHeader('Content-Type','application/json');
        if(is_null($res)){
            $rs = $rs->withStatus(404);
            $data = [
                "type" => "error",
                "code" => 404,
                "msg" => "commande $id not found"
            ];
        } else {
            $data = [
                "type" => "resource",
                "commande" => $res
            ];
        }
        $rs->getBody()->write(json_encode($data));
        return $rs;
    }

    public function createCommande(Request $rq, Response $rs, array $args) : Response{
        if($rq->getAttribute('has_errors')){
            return Writer::json_error($rs,400,$rq->getAttribute('errors'));
        }
        $commande_data = $rq->getParsedBody();
    }

    public function getCommande(Request $rq, Response $rs, array $args): Response{
        $id = $args['id'];
        try{
            $commande = Commande::select(['id', 'livraison', 'nom', 'mail','status','montant'])
                ->with('items')
                ->where('id','=',$id)
                ->firstOrFail();
            $data = [
                'type' => 'resource',
                'commande' => $commande->toArray()
            ];

            return Writer::json_output($rs, 200,$data);
        } catch (ModelNotFoundException $e) {
            ($this->c->get('logger.error'))->error("commande $id not found",[404]);
            return Writer::json_error($rs,404, "commande $id not found");
        }

    }

    public function getCommandes(Request $rq, Response $rs, array $args): Response{
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