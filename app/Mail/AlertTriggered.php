<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertTriggered extends Mailable
{
    use Queueable, SerializesModels;

    public $alert;
    public $rate;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($alert, $rate)
    {
      $this->alert = $alert;
      $this->rate = $rate;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      return $this->from('info@xalerts.com')
        ->view('alert-triggered');
    }
}
