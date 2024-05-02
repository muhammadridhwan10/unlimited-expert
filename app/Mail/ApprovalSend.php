<?php

namespace App\Mail;

use App\Models\ProjectOrders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApprovalSend extends Mailable
{
    use Queueable, SerializesModels;

    public $projectOrder;
    protected $ccEmails;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(ProjectOrders $projectOrder, $ccEmails = [])
    {
        $this->projectOrder = $projectOrder;
        $this->ccEmails = $ccEmails;
    }

    /**
     * Build the message.
     *
     * @return
     */
    public function build()
    {

        $mail = $this->markdown('email.approval_send')
                ->subject('Approval Project Orders for ' . $this->projectOrder->order_number);
        
        if (!empty($this->ccEmails)) {
            $mail->cc($this->ccEmails);
        }
        
        return $mail;
        
    }
}
