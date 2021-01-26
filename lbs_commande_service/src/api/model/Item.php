<?php


namespace lbs\commande\api\model;


class Item extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'item';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function commandes() {
        return $this->belongsToMany('app\model\Commande', 'item_commande', 'item_id', 'commande_id');
    }
}