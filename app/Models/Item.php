<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Copy;
use App\Models\File;
use App\Models\Edition;

class Item extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'modified_on';

    public function _cover(){
      return $this->belongsTo(File::class, 'cover');
    }

    public function copies(){
      return $this->hasMany(Copy::class, 'item');
    }

    public function editions(){
      return $this->hasMany(Edition::class, 'item');
    }

    public function hasEdition($id){
      return Edition::where('id', $id)->where('item', $this->id)->exists();
    }
}
