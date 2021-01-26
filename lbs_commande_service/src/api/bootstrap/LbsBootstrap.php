<?php


namespace lbs\commande\api\bootstrap;

use \Illuminate\Database\Capsule\Manager;

class LbsBootstrap{
    public static function startEloquent($file){
        $conf = parse_ini_file($file);
        $db = new Manager();

        $db->addConnection($conf);
        $db->setAsGlobal();
        $db->bootEloquent();
    }
}