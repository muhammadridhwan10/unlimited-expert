<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerInvoiceSend extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $language;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, $language)
    {
        $this->invoice = $invoice;
        $this->language = $language;
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
        if ($this->language === 'english') {
            return $this->markdown('email.customer_invoice_send')
                ->subject('Invoice Submission for ' . $this->invoice->invoice_id);
        } elseif ($this->language === 'indonesian') {
            return $this->markdown('email.customer_invoice_send_in')
                ->subject('Pengiriman Invoice Untuk Pembayaran ');
        }

        // return $this->markdown('email.customer_invoice_send')
        // ->subject('Invoice Submission for ' . $this->invoice->invoice_id);
    }
}
