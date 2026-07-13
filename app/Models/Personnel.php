<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $table = 'personnel';

    protected $fillable = [
        'name', 'position', 'employee_id',
        'contact_number', 'department', 'address', 'photo',
    ];
}