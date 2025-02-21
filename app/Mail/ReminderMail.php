<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $planning;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($planning)
    {
        $this->planning = $planning;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Reminder for Your Planning')
                    ->view('email.reminder')
                    ->with([
                        'title' => $this->planning->title,
                        'start_date' => $this->planning->start_date,
                    ]);
    }
}
