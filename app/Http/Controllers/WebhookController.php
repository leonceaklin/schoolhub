<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Copy;
use App\Models\Item;
use App\Models\Edition;
use App\Models\TransferOrder;

use ReallySimpleJWT\Token;
use Illuminate\Support\Facades\Log;
use App\Classes\CredentialsManager;

use App\Mail\CopyAvailable;

use App\Mail\TransferOrderUpdated;
use App\Mail\ContactDetailsNeeded;
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

     public function onTransferOrdersCreated(){

     }

     public function onTransferOrdersUpdated(){
       $transferOrders = TransferOrder::where("status", "!=", "sent")->where("status", "!=", "completed")
       ->where("modified_on", '>', \Carbon\Carbon::now()->subMinutes(0.5)->timestamp)->get();

       foreach($transferOrders as $transferOrder){
         Mail::to($transferOrder->_store->contact_email)->send(new TransferOrderUpdated($transferOrder));
       }

       //Update copies of completed transfer orders
       $transferOrdersCompleted = TransferOrder::where("status", "completed")->where("completed_on", null)->get();

       foreach($transferOrdersCompleted as $transferOrder){
         $transferOrder->completed_on = date("Y-m-d H:i:s");
         $transferOrder->save();

         Log::info("Transfer order set as completed. ID: ".$transferOrder->id);

         foreach($transferOrder->copies as $copy){
           $copy->status = "paidout";
           $copy->save();
         }
       }

       //Update copies of not completed transfer orders
       $transferOrdersNotCompleted = TransferOrder::where("status", "!=", "completed")->where("completed_on", "!=", null)->get();


       foreach($transferOrdersNotCompleted as $transferOrder){
         $transferOrder->completed_on = null;
         $transferOrder->save();

         Log::info("Transfer order set from completed to ".$transferOrder->status.". ID: ".$transferOrder->id);

         foreach($transferOrder->copies as $copy){
           $copy->status = "sold";
           $copy->save();
         }
       }
     }

     public function onCopiesCreated(){
       return $this->copiesWebhook('created');
     }

     public function onCopiesUpdated(){
       return $this->copiesWebhook('updated');
     }

     public function copiesWebhook($event){
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
       ->orWhere(function($query){
         $query->where('status', 'ordered')->where('ordered_by', null);
       })
       ->get();

       foreach($copies as $copy){
         if($copy->status == "ordered"){
           if($copy->ordered_by == null){
             $copy->status = "available";
             $copy->ordered_on = null;
             $copy->transfer_order = null;
             $copy->save();
           }
         }

         if($copy->status == "available"){
            $copySaved = false;
            $copy->ordered_on = null;
            $copy->ordered_by = null;
            $copy->order_hash = null;
            $copy->transfer_order = null;

             if(!$copy->available_since){
                 $copy->available_since = date("Y-m-d H:i:s");
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
                 $copy->sold_on = date("Y-m-d H:i:s");
                 $copy->save();

                 //If no IBAN provided
                 if($copy->ownedBy->iban == null || $copy->ownedBy->zip == null || $copy->ownedBy->city == null){
                   Mail::to($copy->ownedBy->activeEmail)->send(new ContactDetailsNeeded($copy->ownedBy));
                 }

                 Log::info("Copy sold (".$copy->uid.") ".$copy->price." CHF to ".$copy->orderedBy != null ? $copy->orderedBy->email : 'nobody…');
             }
         }
       }
     }


}
