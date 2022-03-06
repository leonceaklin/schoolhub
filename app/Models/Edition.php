<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Item;


class Edition extends Model
{
  const CREATED_AT = 'created_on';
  const UPDATED_AT = 'modified_on';

    public function _item(){
      return $this->belongsTo(Item::class, 'item');
    }
}
