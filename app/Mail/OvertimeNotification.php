<?php

namespace App\Mail;

use App\Models\UserOvertime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OvertimeNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $overtime;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(UserOvertime $overtime)
    {
        $this->overtime = $overtime;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->from(env('MAIL_USERNAME'))
        // ->view('template_email')
        // ->with(
        //     [
        //         'nama' => env('MAIL_USERNAME'),
        //         'website' => 'Konsultanku',
        //     ]);
        return $this->markdown('email.overtime_notification');
    }
}
