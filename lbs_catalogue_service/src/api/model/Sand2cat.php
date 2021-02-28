<?php

namespace lbs\catalogue\api\model;

use Illuminate\Database\Eloquent\Model;

class Sand2cat extends Model
{
    protected $table ='sand2cat';
    protected $primaryKey ='sand_id';
    protected $fillable = ['sand_id','cat_id'];

    public $incrementing = false;
}