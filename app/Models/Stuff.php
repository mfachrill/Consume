<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    use softDeletes;
    protected $fillable = ["name", "category"];

    public function stuffStock()
    {
        return $this->hasOne(StuffStock::class, 'stuff_id', 'id');
    }
    public function inboundStuffs()
    {
        return $this->hasMany(inboundStuff::class);
    }
    public function lendings()
    {
        return $this->hasMany(Lending::class);
    }
}
