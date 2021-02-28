<?php

namespace lbs\catalogue\api\model;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $table ='categorie';
    protected $primaryKey ='id';
    protected $fillable = ['id','nom','description'];

    public $incrementing = false;
    public $keyType = 'string';

    public function sandwichs()
    {
        return $this->belongsToMany(Sandwich::class, 'sand2cat', 'sand_id', 'cat_id');
    }
}