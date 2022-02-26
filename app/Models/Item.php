<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Copy;
use App\File;
use App\Edition;

class Item extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'updated_on';

    public function cover(){
      return $this->belongsTo(File::class, 'cover');
    }

    public function copies(){
      return $this->hasMany(Copy::class, 'item');
    }

    public function editions(){
      return $this->hasMany(Edition::class, 'item');
    }
}
