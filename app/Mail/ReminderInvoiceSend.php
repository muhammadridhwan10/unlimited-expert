<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReminderInvoiceSend extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    protected $ccEmails;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, $ccEmails = [])
    {
        $this->invoice = $invoice;
        $this->ccEmails = $ccEmails;
    }

    /**
     * Build the message.
     *
     * @return
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
        $mail = $this->markdown('email.reminder_invoice_send')
        ->subject('Invoice Payment Reminder For ' . $this->invoice->invoice_id);

        if (!empty($this->ccEmails)) {
            $mail->cc($this->ccEmails);
        }

        return $mail;

        // return $this->markdown('email.customer_invoice_send')
        // ->subject('Invoice Submission for ' . $this->invoice->invoice_id);
    }
}
