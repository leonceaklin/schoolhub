<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Edition;
use App\Models\User;
use App\Models\Item;
use App\Models\TransferOrder;
use App\Models\Store;

class Copy extends Model
{
    const CREATED_AT = 'created_on';
    const UPDATED_AT = 'modified_on';

    protected $dates = [
        'created_on',
        'modified_on',
        'sold_on',
        'available_since',
        'ordered_on'
    ];

    public $commission = 0.15;

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

    public function getPaybackAttribute(){
      $price = $this->price;
      return number_format($price*(1 - $this->commission), 2, '.', '');
    }

    public function getPublicUrlAttribute(){
      return url("/bookstore/".$this->_copy->id."/".$this->uid);
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
