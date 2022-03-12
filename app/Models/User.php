<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\School;

class User extends Authenticatable
{
  const CREATED_AT = 'created_on';
  const UPDATED_AT = 'modified_on';

    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getActiveEmailAttribute(){
      if(!empty($this->email)){
        $email = $this->email;
      }
      else{
        $email = $this->username."@sbl.ch";
      }
      return $email;
    }

    public function getNameAttribute(){
      return $this->first_name." ".$this->last_name;
    }

    public function _school(){
      return $this->belongsTo(School::class, 'school');
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
