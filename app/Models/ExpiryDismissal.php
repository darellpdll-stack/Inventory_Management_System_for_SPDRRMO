<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpiryDismissal extends Model
{
    protected $fillable = ['user_id', 'supply_item_id'];
}