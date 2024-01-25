<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\{Delivery, Bonus};

class CompletedBonus extends Model {

    protected $table = 'completed_bonuses';

    protected $guarded = ['id'];


    public function bonus()
    {
        return $this->belongsTo(Bonus::class);
    }

    public function dboy() {
        return $this->belongsTo(Delivery::class);
    }
}