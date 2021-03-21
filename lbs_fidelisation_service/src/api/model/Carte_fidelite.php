<?php
namespace lbs\fidelisation\api\model;

class Carte_fidelite extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'carte_fidelite';
    protected $primaryKey = 'id';
    public $timestamps = true;   
    public $incrementing = false;
}