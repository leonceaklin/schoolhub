<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactDetailsNeeded extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      $store = $this->user->_school->stores[0];

      return $this->from($store->sender_email, $store->name)
          ->replyTo($store->contact_email, $store->name." Support")
          ->subject(__("bookstore.contact_details_needed_subject"))
          ->view("mail.contact_details_needed", ["user" => $this->user, "store" => $store]);
    }
}
