<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\TransferOrder;
use App\Models\Store;
use App\Models\Copy;

use App\Mail\TransferOrderCreated;
use Illuminate\Support\Facades\Mail;

class CreateTransferOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transferorders:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates transfer orders for each store with sold copies that are not yet listed in a transfer order';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $stores = Store::all();
      foreach($stores as $store){
        $copies = $store->copies()->doesntHave("_transfer_order")->where("status", "sold")->whereHas('ownedBy', function($query){
          $query->where("iban", "!=", '');
        })->get();

        if(sizeof($copies) > 0){
          $transferOrder = new TransferOrder();
          $transferOrder->store = $store->id;
          $transferOrder->save();

          foreach($copies as $copy){
            $copy->transfer_order = $transferOrder->id;
            $copy->save();
          }


          Mail::to($store->contact_email)->send(new TransferOrderCreated($transferOrder));
        }
      }

    }
}
