<?php


namespace lbs\catalogue\api\errors;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \lbs\catalogue\api\utils\Writer;
use Slim\Container as C;

class BadUri{

    public static function error(C $c, Request $rq, Response $rs) : Response{
        $uri = $rq->getUri();
        return Writer::json_error($rs, 400,  "The request $uri was not recognized : malformed uri");
    }

}