<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Copy;
use App\Models\Item;
use App\Models\Edition;
use App\Models\File;

use ReallySimpleJWT\Token;
use Illuminate\Support\Facades\Log;
use App\Classes\CredentialsManager;

use App\Mail\TransferOrderCreated;
use Illuminate\Support\Facades\Mail;


class WebhookController extends Controller
{
    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */

     private $secret;

     public function __construct(){

     }

     public function onCopiesCreated(){
       return $this->copiesWebhook();
     }

     public function onCopiesUpdated(){
       return $this->copiesWebhook();
     }

     public function copiesWebhook(){
       $copies = Copy::where('sold_on', null)->where('status', 'sold')
       ->orWhere(function($query){
         $query->where('available_since', null)->where('status', 'available');
       })
       ->orWhere(function($query){
         $query->where('status', 'available')->where('ordered_by', "!=", null);
       })
       ->orWhere(function($query){
         $query->where('status', 'available')->where('ordered_on', "!=", null);
       })
       ->get();

       foreach($copies as $copy){
         if($copy->status == "available"){
            $copySaved = false;
            $copy->ordered_on = null;
            $copy->ordered_by = null;
            $copy->order_hash = null;

             if(!$copy->available_since){
                 $copy->available_since = date();
                 $copy->save();
                 $copySaved = true;
                 Log::info("Copy available (".$copy->uid.") ".$copy->price." CHF by ".$copy->ownedBy->email);

                 if($event == 'updated'){
                   Mail::to($copy->ownedBy->activeEmail, $copy->ownedBy->name)->send(new CopyAvailable($copy));
                 }
             }

             if(!$copySaved){
               $copy->save();
             }
         }

         if($copy->status == "sold"){
             if(!$copy->sold_on){
                 $copy->sold_on = date();
                 $copy->save();
                 Log::info("Copy sold (".$copy->uid.") ".$copy->price." CHF to ".$copy->orderedBy->email);
             }
         }
       }
     }


}
