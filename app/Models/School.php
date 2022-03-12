<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Store;

class School extends Model
{

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'modified_on';

    public function users(){
      return $this->hasMany(User::class, "school");
    }

    public function stores(){
      return $this->hasMany(Store::class, "school");
    }

}
