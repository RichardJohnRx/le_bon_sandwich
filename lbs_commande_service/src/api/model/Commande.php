<?php


namespace lbs\commande\api\model;


class Commande  extends \Illuminate\Database\Eloquent\Model{
    protected $table ='commande';
    protected $primaryKey ='idCommande';
    public $timestamps=false;

}