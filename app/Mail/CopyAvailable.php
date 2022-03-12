<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CopyAvailable extends Mailable
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
        return $this->from($copy->_store->sender_email, $copy->_store->name)
          ->replyTo($copy->_store->contact_email, $copy->_store->name." Support")
          ->subject(__("bookstore.copy_available_now"))
          ->view("mail.copy_available", ["copy" => $copy]);
    }
}
