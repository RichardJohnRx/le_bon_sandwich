<?php


namespace lbs\catalogue\api\errors;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \lbs\catalogue\api\utils\Writer;
use Slim\Container as C;

class NotAllowed{

    public static function error(C $c, Request $rq, Response $rs, $methods): Response{
        $method = $rq->getMethod();
        $uri = $rq->getUri();
            return Writer::json_error($rs, 405,  "Method $method not allowed for uri $uri - (should be )".implode(', ',$method));
    }

}