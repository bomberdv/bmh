<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Educations extends Model
{
    protected $guarded = array();
    public $timestamps = false;

    public function amils()
    {
        return $this->belongsTo('App\Models\Amils')->first();
    }
}
