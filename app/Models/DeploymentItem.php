<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentItem extends Model
{
    protected $fillable = ['deployment_id', 'supply_item_id', 'quantity'];

    public function deployment()
    {
        return $this->belongsTo(Deployment::class);
    }

    public function supplyItem()
    {
        return $this->belongsTo(SupplyItem::class, 'supply_item_id');
    }
}