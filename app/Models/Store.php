<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Copy;
use App\Models\File;
use App\Models\TransferOrder;
use App\Models\Charity;

class Store extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'modified_on';

    public function transfer_orders(){
      return $this->hasMany(TransferOrder::class, 'store');
    }

    public function copies(){
      return $this->hasMany(Copy::class, 'store');
    }


    public function _charity(){
      return $this->belongsTo(Charity::class, 'charity');
    }
}
