<?php

namespace lbs\catalogue\api\model;

use Illuminate\Database\Eloquent\Model;

class Sandwich extends Model
{
    protected $table ='sandwich';
    protected $primaryKey ='id';
    protected $fillable = ['id','nom','description','type_pain','img', 'prix'];

    public $incrementing = false;
    public $keyType = 'string';

    public function categorie(){
        return $this->belongsToMany(Categorie::class, 'sand2cat', 'sand_id', 'cat_id');
    }
}