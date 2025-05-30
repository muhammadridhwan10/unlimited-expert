<?php
$totalAmount = 0;

$invoiceProducts = \App\Models\InvoiceProduct::where('invoice_id', $invoice->id)->get();


foreach ($invoiceProducts as $invoiceProduct) {

    $rate = $invoiceProduct->tax;
    $price = $invoiceProduct->price;

    if($invoice->operator == "+")
    {
            $totalAmount += $price + ($price * $rate / 100);
    }
    else
    {
            $totalAmount += $price - ($price * $rate / 100);
    }
    
}

$categoryIds = explode(',', $invoice->category_id);
$productNames = \App\Models\ProductServiceCategory::whereIn('id', $categoryIds)->pluck('name');
$productPeriods = $invoiceProducts->pluck('productService.periode')->first();

$invoiceUrl = $invoice->invoice_url;
$shortenedUrl = url('/invoice/' . hash('crc32', $invoiceUrl));

$invoiceId    = \Crypt::encrypt($invoice->id);
$invoice_url  = route('invoice.pdf', $invoiceId);

$settings = \App\Models\Utility::settings();

?>
<div style="background-color:#f6f6f6;font-family:sans-serif;font-size:14px;line-height:1.4;margin:0;padding:0">
    <span style="color:transparent;display:none;height:0;max-height:0;max-width:0;opacity:0;overflow:hidden;width:0">Dear {{ $invoice->client->clients->name_invoice ?? 'Client' }} I trust this email finds you well, I am writing to inform you that the invoice for the recent services/products provided by {{ $invoice->client->name }} is now ready for your review and payment.</span>
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;background-color:#f6f6f6;width:100%" width="100%" bgcolor="#f6f6f6">
        <tbody>
            <tr>
                <td style="font-family:sans-serif;font-size:14px;vertical-align:top" valign="top">&nbsp;</td>
                <td style="font-family:sans-serif;font-size:14px;vertical-align:top;display:block;max-width:580px;padding:10px;width:580px;margin:0 auto" width="580" valign="top">
                    <div style="box-sizing:border-box;display:block;margin:0 auto;max-width:580px;padding:10px">


                        <table role="presentation" style="border-collapse:separate;background:#ffffff;border-radius:3px;width:100%" width="100%">


                            <tbody>
                                <tr>
                                    <td style="font-family:sans-serif;font-size:14px;vertical-align:top;box-sizing:border-box;padding:20px" valign="top">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;width:100%" width="100%">
                                            <tbody>
                                                <tr>
                                                    <td style="font-family:sans-serif;font-size:14px;vertical-align:top" valign="top">
                                                        <div style="width:180px">
                                                            <img src="https://i.postimg.cc/9XbjrKPR/logo-light.png" style="border:none;max-width:100%" width="150px" class="CToWUd">
                                                        </div>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Dear {{ $invoice->client->clients->name_invoice ?? 'Client' }}</p>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">I trust this email finds you well.</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">I am writing to inform you that the invoice for the recent services/products provided by {{ $invoice->client->name }} is now ready for your review and payment.</p>
                                                        <br>
                                                        {{-- @if($invoice->company == 'ARA' && $invoice->category_id == 9)
                                                        <p style='color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px'>
                                                        The Scope of work for this invoice is as follows :</p>
                                                        <ul style='list-style-type: disc; padding-left: 20px;'>
                                                            <li style='color:#444444;'>Prepare a chart of accounts according to the client's business.</li>
                                                            <li style='color:#444444;'>Determine accounting policies (e.g., revenue recognition, depreciation).</li>
                                                            <li style='color:#444444;'>Determine tax arrangements according to applicable regulations.</li>
                                                            <li style='color:#444444;'>Enter opening balances for key accounts (if necessary).</li>
                                                        </ul>
                                                        <br>
                                                        @endif --}}
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Please find the details of the invoice below : </p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Invoice Details : </p>
                                                        <br>
                                                        <ul style="list-style-type: none; padding: 0; margin: 0;">
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Invoice Number:</strong> {{ $invoice->invoice_id }}
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Invoice Date:</strong> {{ $invoice->issue_date }}
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Due Date:</strong> {{ $invoice->due_date }}
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Description of Service/Product:</strong> 
                                                                @foreach ($productNames as $item)
                                                                    {{ $item . ' Service' }}
                                                                    @if (!$loop->last && count($productNames) > 1)
                                                                        ,
                                                                    @endif
                                                                    @if ($loop->last)
                                                                        <br>
                                                                    @endif
                                                                @endforeach
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Total Amount:</strong> 
                                                                @if ($invoice->currency == '$')
                                                                    {{\App\Models\Utility::priceFormat2($settings,$totalAmount)}}
                                                                @elseif($invoice->currencies == '€')
                                                                    {{\App\Models\Utility::priceFormat3($settings,$totalAmount)}}
                                                                @elseif($invoice->currencies == 'S$')
                                                                    {{\App\Models\Utility::priceFormat4($settings,$totalAmount)}}
                                                                @else
                                                                    {{\App\Models\Utility::priceFormat($settings,$totalAmount)}}
                                                                @endif 
                                                            </li>
                                                        </ul>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">You can view and download the detailed invoice by clicking on the following link : {{ $invoice_url }}</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Payment Instructions : </p>
                                                        <br>
                                                        @if($invoice->company == "KAP")
                                                        <ul style="list-style-type: none; padding: 0; margin: 0;">
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Bank Name:</strong> PT Bank Negara Indonesia (Persero)
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Account Holder:</strong> Akuntan Publik Agus Ubaidillah dan Rekan
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Account Number:</strong> 03467-27205
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Payment Due Date:</strong> 7 Working Days After Invoice Date
                                                            </li>
                                                        </ul>
                                                        @elseif($invoice->company == "ARA")
                                                        <ul style="list-style-type: none; padding: 0; margin: 0;">
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Bank Name:</strong> PT Bank Negara Indonesia (Persero)
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Account Holder:</strong> All Rich Associate
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Account Number (Rp):</strong> 05666-47352
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Account Number ($):</strong> 0566791928
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Payment Due Date:</strong> 7 Working Days After Invoice Date
                                                            </li>
                                                        </ul>
                                                        @elseif($invoice->company == "XGA")
                                                        <ul style="list-style-type: none; padding: 0; margin: 0;">
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Bank Name:</strong> PT Bank Negara Indonesia (Persero)
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Account Holder:</strong> X Group Advisory Firma
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Account Number:</strong> 567183534
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Payment Due Date:</strong> 7 Working Days After Invoice Date
                                                            </li>
                                                        </ul>
                                                        @endif
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Kindly process the payment by the specified due date to avoid any potential late fees. If you have already made the payment, please accept our sincere thanks.</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Should you have any questions or require further clarification regarding the invoice, please feel free to reach out to our finance department at info@au-partners.com</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Thank You for your prompt attention to this matter, and we appreciate your continued partnership.</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Best regards,</p>
                                                        <br>
                                                        <br>
                                                        <br>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Melya Lubis</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Head of Business Support.</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>


                            </tbody>
                        </table>



                        <div style="clear:both;margin-top:10px;text-align:center;width:100%">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;width:100%" width="100%">
                                <tbody>
                                    <tr>
                                        <td style="font-family:sans-serif;vertical-align:top;padding-bottom:10px;padding-top:10px;color:#999999;font-size:12px;text-align:center" valign="top" align="center">
                                            <span style="color:#999999;font-size:12px;text-align:center">
                                                PT AUP Group Indonesia, Wisma Staco 6th Floor<br>
                                                Jl. Casablanca Kav. 18, Jakarta Selatan 12870
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                    </div>
                </td>
                <td style="font-family:sans-serif;font-size:14px;vertical-align:top" valign="top">&nbsp;</td>
            </tr>
        </tbody>
    </table>
    <img alt="" src="https://ci4.googleusercontent.com/proxy/bjsHr5sTCJWAY3Z4Z3Q81nHxsFwM7d2v8to1zb5XJP2bcPt_yPijh27D983_FFb2c8_IZujLINr_foLQTI4DA-cluLQPXXSItc_I-k-6sfKWLn4dAplTZnfGgTcZVTOmvxylrskBaM9AO2Z7TJPNJg8iDbpiA33R_tLsEGPYXs_oI3w1ul3f8snf-meMsgf-NW0-78067HnZJkVhUACr=s0-d-e1-ft#http://9md53ctc.r.ap-southeast-2.awstrack.me/I0/0108017e56a1881f-7e0e46a5-4c6c-47b5-a048-b28cf4c62fd2-000000/70WTFACnYMycuYZfmAOh7flBvms=33" style="display:none;width:1px;height:1px" class="CToWUd">
    <div class="yj6qo"></div>
    <div class="adL">
    </div>
</div>
