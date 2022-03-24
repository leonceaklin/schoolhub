<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Item;
use App\Models\Copy;
use App\Models\User;


class Order extends Model
{
  const CREATED_AT = 'created_on';
  const UPDATED_AT = 'modified_on';

  protected $dates = [
      'created_on',
      'modified_on',
      'prepared_since',
      'paid_on'
  ];

  public function copies(){
    return $this->hasMany(Copy::class, 'order');
  }

  public function placedBy(){
    return $this->belongsTo(User::class, 'placed_by');
  }

  public function calculate(){
    $this->load("copies");
    $amount = 0;

    $nowPrepared = $this->status != "prepared";
    $nowPaid = $this->status != "paid";

    foreach($this->copies as $copy){
      $amount += $copy->price;
      if($copy->status != "prepared" && $copy->status != "sold" && $copy->status != "paidout"){
        $nowPrepared = false;
        $nowPaid = false;
      }

      else if($copy->status != "sold" && $copy->status != "paidout"){
        $nowPaid = false;
      }
    }

    if($nowPaid){
      $this->status = "paid";
      $this->paid_on = date("Y-m-d H:i:s");
    }

    else if($nowPrepared){
      $this->status = "prepared";
      $this->prepared_since = date("Y-m-d H:i:s");
    }

    $this->amount = $amount;
    $this->calculated_on = date("Y-m-d H:i:s");
  }

}
