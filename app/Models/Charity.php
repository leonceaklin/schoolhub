<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Item;
use App\Models\Charity;
use App\Models\File;


class Charity extends Model
{
  const CREATED_AT = 'created_on';
  const UPDATED_AT = 'modified_on';

    public function stores(){
      return $this->hasMany(Store::class, 'charity');
    }

    public function items(){
      return $this->hasMany(Item::class, 'charity');
    }

    public function _logo(){
      return $this->belongsTo(File::class, 'logo');
    }
}
