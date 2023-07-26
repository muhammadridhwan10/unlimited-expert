<?php

namespace App\Mail;

use App\Models\Reimbursment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReimbursmentPersonalApprovalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $reimbursment;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Reimbursment $reimbursment)
    {
        $this->reimbursment = $reimbursment;
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
        return $this->markdown('email.reimbursmentpersonal_approval_notification');
    }
}
