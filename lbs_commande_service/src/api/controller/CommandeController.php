<?php


namespace lbs\commande\api\controller;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\commande\api\model\Commande;
use lbs\commande\api\model\Item;
use lbs\commande\api\utils\Writer;
use Ramsey\Uuid\Uuid;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Client;

class CommandeController{

    private $commandes = [
        ["id" => "45RF56TH","mail_client"=>"a@a.fr","date_commande"=>"1-12-2020","montant"=>50.0],
        ["id" => "46RF56TH","mail_client"=>"b@a.fr","date_commande"=>"2-12-2020","montant"=>45.0],
        ["id" => "57RF56TH","mail_client"=>"c@a.fr","date_commande"=>"3-12-2020","montant"=>27.5],
        ["id" => "01RF56TH","mail_client"=>"d@a.fr","date_commande"=>"1-12-2020","montant"=>30.0]
    ];

    private $c; 

    public function __construct(\Slim\Container $c){
        $this->c = $c;
    }
    
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

        if (!$rq->getAttribute('has_errors')) {
            $getBody = $rq->getBody();
            $json = json_decode($getBody, true);
            $mail = $json["mail"];
            $nom = $json["nom"];
            $prix_commande = 0;
            $livraison_date = $json['livraison']['date'];
            $livraison_heure = $json['livraison']['heure'];
            $getBody = json_decode($rq->getBody());

            $client = new Client(["base_uri" => "http://api.catalogue.local"]);

            foreach ($getBody->items as $item) {
                $response = $client->get($item->uri);
                $sandwichs = json_decode($response->getBody());

                $order = array();
                $order["commande"]["uri"] = $item->uri;
                $order["commande"]["libelle"] = $sandwichs->sandwich->nom;
                $order["commande"]["tarif"] = $sandwichs->sandwich->prix;
                $order["commande"]["quantite"] = $item->q;
                $orders["commandes"][] = $order;
            }
            
            $livraison = [
                'date' => $livraison_date,
                'heure' => $livraison_heure
            ];
            
            $token = random_bytes(32);
            $token = bin2hex($token);

            $newCommande = new Commande();

            $newCommande->id = Uuid::uuid4();
            $newCommande->nom = (filter_var($nom, FILTER_SANITIZE_STRING));
            $newCommande->livraison = $livraison['date'].' '.$livraison['heure'];
            $newCommande->mail = (filter_var($mail, FILTER_SANITIZE_EMAIL));

            foreach ($orders["commandes"] as $commande) {
                $item = new item();
                $item->uri = $commande["commande"]["uri"];
                $item->libelle = $commande["commande"]["libelle"];
                $item->tarif = $commande["commande"]["tarif"];
                $item->quantite = $commande["commande"]["quantite"];
                $item->command_id = $newCommande->id;
                $item->save();
                $prix_commande += $commande["commande"]["tarif"] * $commande["commande"]["quantite"];
            }

            $newCommande->montant = $prix_commande;
            $newCommande->token = $token;

            $newCommande->save();

            $rs = $rs->withStatus(201)
                ->withHeader('Location', 'http://api.commande.local:19080/commandes/' . $newCommande->id)
                ->withHeader('Content-Type', 'application/json;charset=utf-8');
            $rs->getBody()->write(json_encode([
                "commande" => Commande::select("nom", "mail", "livraison", 'id', 'token', 'montant')->find($newCommande->id),
                "items" => $getBody->items
            ]));

            return $rs;
        } else {
            $errors = $rq->getAttribute('errors');
            $rs = $rs->withStatus(400)
                ->withHeader('Content-Type', 'application/json;charset=utf-8');
            $rs->getBody()->write(json_encode($errors));
            return $rs;
        }
    }

    public function getCommande(Request $rq, Response $rs, array $args): Response{
        try{
            $id = $args['id'];

            if(!empty($rq->getHeader('token')[0])){
                $token = $rq->getHeader('token')[0];
            }else if(!empty($rq->getQueryParam('token', null))){
                $token = $rq->getQueryParam('token', null);
            }else{
                $token = null;
            }

            $commande = Commande::select(['id', 'livraison', 'nom', 'mail','status','montant', 'token'])
                ->with('items')
                ->where('id','=',$id)
                ->where('token', $token)
                ->get();

                $links = array(
                    "commandes" => array(
                        "href" => "http://api.commande.local:19080/commandes/",
                    ),
                    
                    "commande" => array(
                        "href" => "http://api.commande.local:19080/commandes/" . $id . "",
                    ),

                    "self" => array(
                        "href" => "http://api.commande.local:19080/commandes/" .$id."?token=". $token . "",
                    )
                );

                $data = [
                    'type' => 'resource',
                    'links' => $links,
                    'commande' => $commande->toArray(),
                ];

            return Writer::json_output($rs, 200,$data);
        } catch (ModelNotFoundException $e) {
            ($this->c->get('logger.error'))->error("commande $id not found",[404]);
            return Writer::json_error($rs,404, "commande $id not found");
        }

    }

    public function getCommandes(Request $rq, Response $rs, array $args): Response{
        try{
            $commandes = Commande::select(['id','mail','montant'])->get();
            $rs = $rs->withStatus(200)
                ->withHeader('Content-Type','application/json;charset=utf-8');
            $rs->getBody()->write(json_encode([
                'type' => 'collection',
                'count' => count($commandes),
                'commandes'  => $commandes->toArray()
            ]));
            return $rs;
        } catch (ModelNotFoundException $e) {
            ($this->c->get('logger.error'))->error("commande $id not found",[404]);
            return Writer::json_error($rs,404, "commandes not found");
        }
    }

    public function replaceCommande(Request $rq, Response $rs, array $args): Response{
        $commande_data = $rq->getParsedBody();
        if(!isset($commande_data['nom_client']))
            return Writer::json_error($rs,400,"missing data : nom_client");
        if(!isset($commande_data['mail_client']))
            return Writer::json_error($rs,400,"missing data : mail_client");
    }

}