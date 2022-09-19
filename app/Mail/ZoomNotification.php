<?php

namespace App\Mail;

use App\Models\ZoomMeeting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ZoomNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $zoommeeting;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ZoomMeeting $zoommeeting)
    {
        $this->zoommeeting = $zoommeeting;
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
        return $this->markdown('email.zoom_notification');
    }
}
