<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransferOrderUpdated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($transferOrder)
    {
        $this->transferOrder = $transferOrder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      $this->transferOrder->generateXlsx();
      return $this->from(config("mail.from.address"), config("mail.from.name"))
            ->subject(__("bookstore.transfer_order_updated", ['month' => $this->transferOrder->created_on->format("Y-m")]))
            ->view('mail.transfer_order_updated', ["transferOrder" => $this->transferOrder])
            ->attach(storage_path("app/".$this->transferOrder->xlsxName),
            [
              'as' =>  $this->transferOrder->fileName.".xlsx",
              'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
    }
}
