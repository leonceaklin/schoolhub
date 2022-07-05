<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{

    protected $table = 'directus_files';
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'modified_on';

    public function getThumbnailUrlAttribute(){
      return "https://content.schoolhub.ch/schoolhub/assets/".$this->private_hash."?key=directus-large-contain";
    }
}
