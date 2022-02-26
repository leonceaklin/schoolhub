<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Item;


class Edition extends Model
{
  const CREATED_AT = 'created_on';
  const UPDATED_AT = 'updated_on';
  
    public function item(){
      return $this->belongsTo(Item::class, 'item');
    }
}
