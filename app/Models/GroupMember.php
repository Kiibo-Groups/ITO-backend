<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Delivery;

class GroupMember extends Model {

    protected $table = 'group_members';

    protected $guarded = ['id'];


    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function dboy() {
        return $this->belongsTo(Delivery::class, 'member_id');
    }
}