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
use PushNotifications;

use App\Models\Store;
use App\Models\Order;

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

     public function onOrdersDeleted(){
       return $this->onOrdersUpdated();
     }

     public function onOrdersCreated(){
       return $this->onOrdersUpdated();
     }

     public function onOrdersUpdated(){
       $orders = Order::where("status", "prepared")->where("prepared_since", null)
        ->orWhere(function($query){
          $query->where("status", "paid")->where("paid_on", null);
        })->orWhere("status", "deleted")
        ->orWhere(function($query){
          $query->where("modified_on", '>', \Carbon\Carbon::now()->subMinutes(0.2))
          ->orWhere("calculated_on", null);
        })->get();

        $updateCopies = false;

        foreach($orders as $order){
          if($order->status == "paid"){
            foreach($order->copies as $copy){
              if($copy->status == "ordered" || $copy->status == "prepared" || $copy->status == "available"){
                $copy->status = "sold";

                if($order->placedBy != null){
                  $copy->ordered_by = $order->placed_by;
                }
              }

              if($order->placedBy == null && $copy->orderedBy != null){
                $order->placed_by = $copy->ordered_by;
              }
              $copy->save();
            }

            $order->paid_on = date("Y-m-d H:i:s");
            $order->calculate();
            $order->save();
            $updateCopies = true;
          }

          if($order->status == "prepared"){
            foreach($order->copies as $copy){
              if($copy->status == "ordered" || $copy->status == "available"){
                $copy->status = "prepared";
                if($order->placedBy != null){
                  $copy->ordered_by = $order->placed_by;
                }
                $copy->save();
              }

              if($order->placedBy == null && $copy->orderedBy != null){
                $order->placed_by = $copy->ordered_by;
              }
            }

            $order->prepared_since = date("Y-m-d H:i:s");
            $order->calculate();
            $order->save();
            $updateCopies = true;
          }

          if($order->status == "deleted"){
            $canDelete = true;
            foreach($order->copies as $copy){
              if($copy->status == "ordered" || $copy->status == "prepared" || $copy->status == "available"){
                $copy->status = "available";
                $copy->save();
              }
              else{
                $canDelete = false;
              }
            }

            if($canDelete){
              $order->delete();
            }
            else{
              $order->status = "problem";
              $order->calculate();
              $order->save();
            }
            $updateCopies = true;
          }
        }

        if($updateCopies){
          $this->onCopiesUpdated();
        }
     }

     public function onStoresUpdated(){
       $openedStores = Store::where("status", "open")
              ->whereHas("copies", function($query){
                $query->where("status", "ordered")
                  ->orWhere("status", "prepared");
              })
              ->where(function($query){
                $query->where("opened_since", '<', \Carbon\Carbon::now()->subDays(1))
                ->orWhere("opened_since", null);
              })->get();

      foreach($openedStores as $store){
        $store->opened_since = date("Y-m-d H:i:s");
        $store->save();

        $copies = $store->copies()->where("status", "ordered")->orWhere("status", "prepared")->get();
        $userIds = [];
        foreach($copies as $copy){
          $userId = $copy->orderedBy->id;
          if(!in_array($userId, $userIds)){
            $userIds[] = $userId;
            $copies[] = $copy;
          }
        }

        $i = 0;
        foreach($userIds as $userId){
          $copy = $copies[$i];
          PushNotifications::sendNotificationToExternalUser(__("bookstore.store_opened_short_message_no_item"), strval($userId));
          $i++;
        }
      }
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

         Log::info("Transfer order set as completed.", ["id" => $transferOrder->id]);

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

         Log::info("Transfer order set from completed to ".$transferOrder->status.".", ["id" => $transferOrder->id]);

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
       ->orWhere(function($query){
         $query->where('status', 'prepared')->where('ordered_by', null);
       })
       ->orWhere(function($query){
         $query->where('status', 'prepared')->where('order', null);
       })
       ->orWhere(function($query){
         $query->where('status', 'ordered')->where('order', null);
       })
       ->orWhere(function($query){
         $query->where('status', 'prepared')->where('prepared_since', null);
       })
       ->get();

       $updateOrders = false;

       foreach($copies as $copy){
         if($copy->status == "ordered" || $copy->status == "prepared"){
           if($copy->ordered_by == null || $copy->_order == null){
             $copy->status = "available";
             $copy->ordered_on = null;
             $copy->transfer_order = null;
             $copy->prepared_since = null;
             $copy->save();
           }
         }

         if($copy->status == "available"){
            $copySaved = false;

            $copy->ordered_on = null;
            $copy->ordered_by = null;
            $copy->order_hash = null;
            $copy->transfer_order = null;
            $copy->prepared_since = null;

            if($copy->_order != null){
              $order = $copy->_order;
              $copy->order = null;
              $copy->save();
              $order->calculate();

              if(sizeof($order->copies) == 0){
                $order->delete();
              }
              else{
                $order->save();
                $updateOrders = true;
              }
            }

             if(!$copy->available_since){
                 $copy->available_since = date("Y-m-d H:i:s");
                 $copy->save();
                 $copySaved = true;
                 Log::info("Copy made available.",
                  ["id" => $copy->id, "price" => $copy->price, "owned_by" => $copy->ownedBy->id]);

                 if($event == 'updated'){
                   Mail::to($copy->ownedBy->activeEmail, $copy->ownedBy->name)->send(new CopyAvailable($copy));
                   PushNotifications::sendNotificationToExternalUser(__("bookstore.copy_available_short_message", ["item_name" => $copy->longName]), strval($copy->ownedBy->id),
                    $copy->publicUrl);
                 }
             }

             if(!$copySaved){
               $copy->save();
             }
         }

         if($copy->status == "prepared"){
           if($copy->prepared_since == null){
             $copy->prepared_since = date("Y-m-d H:i:s");
             $copy->save();

             if($copy->_order != null){
               $copy->_order->calculate();
               $copy->_order->save();
               $updateOrders = true;
             }
             PushNotifications::sendNotificationToExternalUser(__("bookstore.copy_prepared_short_message", ["item_name" => $copy->longName]), strval($copy->orderedBy->id));
           }
         }

         if($copy->status == "sold"){
             if(!$copy->sold_on){
                 $copy->sold_on = date("Y-m-d H:i:s");

                 if($copy->prepared_since == null){
                   $copy->prepared_since = date("Y-m-d H:i:s");
                 }
                 $copy->save();

                 if($copy->_order != null){
                   $copy->_order->calculate();
                   $copy->_order->save();
                   $updateOrders = true;
                 }

                 //If no IBAN provided
                 if($copy->ownedBy->iban == null || $copy->ownedBy->zip == null || $copy->ownedBy->city == null){
                   Mail::to($copy->ownedBy->activeEmail)->send(new ContactDetailsNeeded($copy->ownedBy));
                 }


                 Log::info("Copy sold.",
                 ["price" => $copy->price, "uid" => $copy->uid, "id" => $copy->id,
                 "owned_by" => $copy->ownedBy != null ? $copy->ownedBy->id : null,
                 "ordered_by" => $copy->orderedBy != null ? $copy->orderedBy->id : null]);
             }
         }
       }


      if($updateOrders){
        $this->onOrdersUpdated();
      }


       $paybackCopies = Copy::where("commission", null)->get();

       foreach($paybackCopies as $copy){
         $store = $copy->_store;

         $copy->commission = $store->commission;
         $copy->charity_commission = $store->charity_commission;

         if($store->_charity != null){
           $copy->charity = $store->charity;
         }
         $copy->save();
       }
     }


}
