<?php


namespace lbs\catalogue\api\errors;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use \lbs\catalogue\api\utils\Writer;
use Slim\Container as C;

class Internal{

    public static function error(C $c, Request $rq, Response $rs, $error) : Response{
        $uri = $rq->getUri(); 
        return Writer::json_error($rs, 500, "Internal server error: {$error->getMessage()} trace: {$error->getTraceAsString()} file: {$error->getFile()} line: {$error->getLine()}");
    }

}