<?php
$totalAmount = 0;

$invoiceProducts = \App\Models\InvoiceProduct::with('productService')->where('invoice_id', $invoice->id)->get();


foreach ($invoiceProducts as $invoiceProduct) {

    $tax = \App\Models\Tax::find($invoiceProduct->productService->tax_id);
    $rate = $tax->rate;
    $price = $invoiceProduct->price;
    
    $totalAmount += $price - ($price * $rate / 100);
    
}

$productNames = $invoiceProducts->pluck('productService.name')->first();
$productPeriods = $invoiceProducts->pluck('productService.periode')->first();

$invoiceUrl = $invoice->invoice_url;
$shortenedUrl = url('/invoice/' . hash('crc32', $invoiceUrl));

$invoiceId    = \Crypt::encrypt($invoice->id);
$invoice_url  = route('invoice.pdf', $invoiceId);

?>
<div style="background-color:#f6f6f6;font-family:sans-serif;font-size:14px;line-height:1.4;margin:0;padding:0">
    <span style="color:transparent;display:none;height:0;max-height:0;max-width:0;opacity:0;overflow:hidden;width:0">Kepada {{ $invoice->customer->name }} Salam Hormat, Kami berharap Bapak/Ibu dalam keadaan baik. Sehubungan dengan {{ $productNames }} yang telah kami sediakan, bersama ini kami lampirkan invoice terkait untuk pembayaran .</span>
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
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Kepada {{ $invoice->customer->name }}</p>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Salam Hormat,</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Kami berharap Bapak/Ibu dalam keadaan baik. Sehubungan dengan {{ $productNames }} yang telah kami sediakan, bersama ini kami lampirkan invoice terkait untuk pembayaran.</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Rincian Invoice : </p>
                                                        <br>
                                                        <ul style="list-style-type: none; padding: 0; margin: 0;">
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Nomor Invoice:</strong> {{ $invoice->invoice_id }}
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Tanggal Invoice:</strong> {{ $invoice->issue_date }}
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Jatuh Tempo:</strong> {{ $invoice->due_date }}
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Deskripsi Jasa:</strong> {{ $productNames }} untuk {{ $productPeriods }}
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Total Tagihan:</strong> {{ 'Rp' . $totalAmount }}
                                                            </li>
                                                        </ul>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Anda dapat mengunduh dan melihat rincian lengkap invoice melalui lampiran berikut : {{ $invoice_url }}</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Petunjuk Pembayaran : </p>
                                                        <br>
                                                        <ul style="list-style-type: none; padding: 0; margin: 0;">
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Nama Bank:</strong> PT Bank Negara Indonesia (Persero)
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Pemegang Rekening:</strong> Akuntan Publik Agus Ubaidillah dan Rekan
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Nomor Rekening:</strong> 03467-27205
                                                            </li>
                                                            <li style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin-bottom:5px;">
                                                                <strong>Batas Pembayaran:</strong> 7 Hari Kerja Setelah Tanggal Invoice
                                                            </li>
                                                        </ul>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Mohon agar pembayaran dapat dilakukan sesuai dengan batas waktu yang telah ditentukan dalam invoice. Untuk segala pertanyaan atau klarifikasi lebih lanjut, silakan hubungi tim keuangan kami di info@au-partners.com</p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Kami mengucapkan terima kasih atas kerja sama dan kepercayaan Anda. Kami berharap agar transaksi ini dapat berjalan lancar, dan kami siap membantu jika ada yang diperlukan./p>
                                                        <br>
                                                        <p style="color:#444444;font-family:sans-serif;font-size:14px;font-weight:normal;margin:0;margin-bottom:5px">Terima kasih dan salam hormat,</p>
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
                                                PT AUP Group Indonesia, Wisma Staco Lantai 6<br>
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
