<?php


namespace lbs\catalogue\api\controller;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\catalogue\api\utils\Writer;
use lbs\catalogue\api\model\Sandwich;
use lbs\catalogue\api\model\Categorie;
use lbs\catalogue\api\model\Sand2cat;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CatalogueController{

    protected $c; 

    public function __construct(\Slim\Container $c = null){
        $this->c = $c;
    }
    
    public function getSandwichs(Request $rq, Response $rs, array $args): Response{
        
        $type = $rq->getQueryParam('t', null);
        $page = intval($rq->getQueryParam('page', 1));
        $size = intval($rq->getQueryParam('size', 10));
        
        try{
            $sandwichs = Sandwich::select('id as reference', 'nom as nom_sandwich', 'prix as tarif', 'description', 'type_pain', 'img as image', 'ref')->orderBy('nom');

            if(!is_null($type))
                $sandwichs = $sandwichs->where('type_pain', 'LIKE', "%$type%");
            
            $count = $sandwichs->count();
            $last = intdiv($count, $size) + 1;

            if($page > $last) $page = $last;

            $rows = $sandwichs->skip(($page-1)*$size)->take($size)->get();

            $sandwichs_data = [];

            foreach($rows as $sandwich){
                $sandwichs_data[] = [
                    'sandwich' => $sandwich->toArray(),
                    'links' => [
                        'self' => ['href'=> $this->c->get('router')->pathFor('sandwichs', ['ref' => $sandwich->reference])]
                    ]
                ];
            }

            $url_sandwichs = $this->c->get('router')->pathFor('sandwichs', []);
            $next = (($page + 1 > $last) ? $last : $page + 1);
            $prev = (($page - 1 < 1) ? 1 : $page - 1);

            $data = [
                'type' => 'collection',
                'count' => $count,
                'size' => $size,
                'date' => date('d-m-Y'),
                'sandwichs' => $sandwichs_data,
                'links' => [
                    "next" => ["href" => $url_sandwichs . "?page=$next&size=$size"],
                    "prev" => ["href" => $url_sandwichs . "?page=$prev&size=$size"],
                    "last" => ["href" => $url_sandwichs . "?page=$last&size=$size"],
                    "first" => ["href" => $url_sandwichs . "?page=1&size=$size"]
                ]
            ];

            return Writer::json_output($rs, 200, $data);

        } catch (\Exception $e) {
            return Writer::json_error($rs,500, "erreur backend incompréhensible ({$e->getMessage()})");
        }
    }

    public function getSandwich(Request $rq, Response $rs, array $args): Response{
        $id = $args['id'];
        try{

            // SELECT * FROM categorie c INNER JOIN sand2cat sc ON c.id = sc.cat_id INNER JOIN sandwich s ON s.id = sc.sand_id WHERE s.id = 5
            
            $sandwich = Sandwich::select()
                ->where('id','=',$id)
                ->firstOrFail();

            $categories = Categorie::select('categorie.id', 'categorie.nom')
            ->join('sand2cat', 'categorie.id' ,'=', 'cat_id')
            ->join('sandwich', 'sandwich.id' ,'=', 'sand_id')
            ->where(["sandwich.id" => $id])
            ->get();

                $links = array(
                    "sandwichs" => array(
                        "href" => "http://api.catalogue.local:19180/sandwichs/",
                    ),
                    
                    "self" => array(
                        "href" => "http://api.catalogue.local:19180/sandwichs/" . $id . "/",
                    )
                );

            $data = [
                'type' => 'resource',
                "links" => $links,
                'sandwich' => $sandwich->toArray(),
                'categories' => $categories->toArray()
            ];

            return Writer::json_output($rs, 200,$data);
        } catch (ModelNotFoundException $e) {
            return Writer::json_error($rs,404, "sandwich $id not found");
        }

    }

    public function getSandwichCategories(Request $rq, Response $rs, array $args): Response{

        //4
        $id = (int)$args["id"];

        // SELECT * FROM sandwich s INNER JOIN sand2cat sc ON s.id = sc.sand_id WHERE sc.cat_id = 1

        $categories = Categorie::find(["id" => $id]);

        foreach ($categories as $category) {
            $order = array();
            $order["id"] = $category->id;
            $order["nom"] = $category->nom;
            $order["description"] = $category->description;
        }

        $sandwichs = Sandwich::select()->join('sand2cat', 'id' ,'=', 'sand_id')->where(["cat_id" => $order["id"]])->get();

        $links = array(
            "categories" => array(
                "href" => "http://api.catalogue.local:19180/categories/",
            ),
            "categorie" => array(
                "href" => "http://api.catalogue.local:19180/categories/" . $id . "/",
            ),
            "self" => array(
                "href" => "http://api.catalogue.local:19180/categories/" . $id . "/sandwichs/",
            )
        );

        $data = [
            "type" => "resource",
            "links" => $links,
            "date" => date("Y-m-d"),
            "count" => $sandwichs->count(),
            "categorie nom" => $order["nom"],
            "categorie description" => $order["description"],
            "sandwichs" =>  $sandwichs->toArray()
        ];

        return Writer::json_output($rs, 200,$data);
    }

    public function getCategorie(Request $rq, Response $rs, array $args){

        $id = (int)$args["id"];
        try{
                $categories = Categorie::find(["id" => $id]);
                
                foreach ($categories as $category) {
                    $order = array();
                    $order["id"] = $category->id;
                    $order["nom"] = $category->nom;
                    $order["description"] = $category->description;
                }
                $links = array(
                    "categories_sandwich" => array(
                        "href" => "http://api.catalogue.local:19180/categories/" . $id . "/sandwichs/",
                    ),
                    "categories" => array(
                        "href" => "http://api.catalogue.local:19180/categories/",
                    ),
                    "self" => array(
                        "href" => "http://api.catalogue.local:19180/categories/" . $id . "/",
                    )
                );

                $data = [
                    "type" => "resource",
                    "date" => date("Y-m-d"),
                    "categorie" => $order,
                    "links" => $links
                ];
                
                return Writer::json_output($rs, 200,$data);
                
            } catch (ModelNotFoundException $e) {
                return Writer::json_error($rs,404, "categorie $id not found");
            }
    }

    public function getCategories(Request $rq, Response $rs, array $args){

        try{
            $categories = Categorie::select()->get();

            $links = array(
                "self" => array(
                    "href" => "http://api.catalogue.local:19180/categories/",
                ),
            );

            $rs = $rs->withStatus(200)
                ->withHeader('Content-Type','application/json;charset=utf-8');
            $rs->getBody()->write(json_encode([
                'type' => 'collection',
                'count' => count($categories),
                "links" => $links,
                'categories'  => $categories->toArray()
            ]));

            return $rs;
                
            } catch (ModelNotFoundException $e) {
                return Writer::json_error($rs,404, "categorie $id not found");
            }
    }
}