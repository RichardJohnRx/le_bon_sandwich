<?php


namespace lbs\commande\api\model;


class Commande  extends \Illuminate\Database\Eloquent\Model{

    const CREATED = 1;
    const PAID = 2;
    const PREPARING = 3;
    const READY = 4;
    const COMPLETED = 5;

    protected $table ='commande';
    protected $primaryKey ='id';
    protected $fillable = ['id','nom','mail','livraison','token'];
    protected $hidden = ['created_at','updated_at'];

    public $incrementing = false;
    public $keyType = 'string';

    public function items(){
        return $this->hasMany(Item::class,'command_id');
    }

    public function client(){
        return $this->belongsTo('lbs\commande\api\model\Client','client_id');
    }
}