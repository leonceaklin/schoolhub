<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Edition;
use App\Models\User;
use App\Models\Item;
use App\Models\TransferOrder;
use App\Models\Store;
use App\Models\Charity;
use App\Models\Order;


class Copy extends Model
{
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'modified_on';

    protected $dates = [
        'created_on',
        'modified_on',
        'sold_on',
        'available_since',
        'ordered_on',
        'prepared_since'
    ];

    public function __construct(){
      $this->generateUid();
      $this->generateOrderHash();
    }

    public function _item(){
      return $this->belongsTo(Item::class, 'item');
    }

    public function _store(){
      return $this->belongsTo(Store::class, 'store');
    }

    public function _order(){
      return $this->belongsTo(Order::class, 'order');
    }

    public function _transfer_order(){
      return $this->belongsTo(TransferOrder::class, 'transfer_order');
    }

    public function orderedBy(){
      return $this->belongsTo(User::class, 'ordered_by');
    }

    public function ownedBy(){
      return $this->belongsTo(User::class, 'owned_by');
    }

    public function _edition(){
      return $this->belongsTo(Edition::class, 'edition');
    }

    public function _charity(){
      return $this->belongsTo(Charity::class, 'charity');
    }

    public function getRealCommissionAttribute(){
      if($this->commission != null && $this->commission >= 0 && $this->commission <= 1){
        $commission = $this->commission;
      }
      else if($this->_store->commission != null && $this->_store->commission >= 0 && $this->_store->commission <= 1){
        $commission = $this->_store->commission;
      }
      else{
        $commission = 0;
      }
      return number_format($commission, 2, '.', '');
    }

    public function getRealCharityCommissionAttribute(){
      if($this->charity_commission != null && $this->charity_commission >= 0 && $this->charity_commission <= 1){
        $commission = $this->charity_commission;
      }
      else if($this->_store->charity_commission != null && $this->_store->charity_commission >= 0 && $this->_store->charity_commission <= 1){
        $commission = $this->_store->charity_commission;
      }
      else{
        $commission = 0;
      }
      return number_format($commission, 2, '.', '');
    }

    public function getPaybackAttribute(){
      $price = $this->price;
      if($this->donation == true){
        $payback = 0;
      }
      else{
        $payback = $price*(1 - $this->realCommission);
      }
      return number_format($payback, 2, '.', '');
    }

    public function getEarningsAttribute(){
      $earnings = $this->price - $this->payback - $this->charityAmount;
      return number_format($earnings, 2, '.', '');
    }

    public function getCharityAmountAttribute(){
      $price = $this->price;
      if($this->donation == true){
        $payback = $price;
      }
      else{
        $payback = $price * $this->realCommission * $this->realCharityCommission;
      }
      return number_format($payback, 2, '.', '');
    }

    public function getPublicUrlAttribute(){
      return url("/bookstore/".$this->_item->slug."/".$this->uid);
    }

    public function getLongNameAttribute(){
      $item = $this->_item;
      $name = '"'.$item->title.'"';
      if($this->edition){
        $edition = $this->_edition;
        $name .= " (".__("bookstore.nth_edition", ["number" => $edition->number]).", ".$edition->year."";
        if($edition->name){
          $name .= ' "'.$edition->name.'"';
        }
      $name .= ")";
      }

      return $name;
    }

    public function generateUid(){
        $seed = "0123456789ABCDEFGHJKLMNPQRSTUVWXYZ";
        $uid = '';
        for($i = 0; $i<6; $i++){
          $k = rand(0,(strlen($seed)-1));
          $uid .= $seed[$k];
        }
        $this->uid = $uid;
    }

    public function generateOrderHash(){
      $this->order_hash = self::randomKey(60);
    }

    private static function randomKey($length) {
      $key = "";
      $pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));

      for($i=0; $i < $length; $i++) {
          $key .= $pool[mt_rand(0, count($pool) - 1)];
      }
      return $key;
    }
}
