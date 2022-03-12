<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($copy)
    {
        $this->copy = $copy;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->copy->_store->sender_email, $this->copy->_store->name)
          ->replyTo($this->copy->_store->contact_email, $this->copy->_store->name." Support")
          ->subject(__("bookstore.order_confirmed_subject", ["store_name" => $this->copy->_store->name]))
          ->view("mail.order_confirmed", ["copy" => $this->copy]);
    }
}
