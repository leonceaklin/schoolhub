<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Copy;
use App\Models\File;
use App\Models\Edition;
use App\Models\Store;

use App\Http\Controllers\TransferOrderController;

class TransferOrder extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'modified_on';

    public function copies(){
      return $this->hasMany(Copy::class, 'transfer_order');
    }

    public function _store(){
      return $this->belongsTo(Store::class, 'store');
    }

    public function getTransferAmountAttribute(){
      $amount = 0;
      foreach($this->copies as $copy){
        $amount += $copy->payback;
      }
      return $amount;
    }

    public function getFileNameAttribute(){
      return str_replace(" ", "_", $this->_store->name)."_Transfer_".$this->created_on->format("Y-m-d");
    }

    public function generateXlsx(){
      $controller = new TransferOrderController();
      $controller->toXlsx($this);
    }

    public function getXlsxNameAttribute(){
      return "transfer_order_".$this->id.".xlsx";
    }
}
