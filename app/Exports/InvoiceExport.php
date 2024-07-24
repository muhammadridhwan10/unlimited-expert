<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\ProductServiceCategory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoiceExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if(auth()->user()->type == 'partners')
        {
            $data = Invoice::where('user_id', auth()->user()->id)->with('items')->get();
        }
        else
        {
            $data = Invoice::with('items')->get();
        }

        $result = [];

        foreach($data as $invoice)
        {
            // Format dan hapus field yang tidak diperlukan
            $formattedInvoice = [
                "invoice_id" => \Auth::user()->invoiceNumberFormat($invoice->invoice_id),
                "issue_date" => $invoice->issue_date,
                "due_date" => $invoice->due_date,
                "send_date" => $invoice->send_date,
                "category_id" => ProductServiceCategory::where('id', $invoice->category_id)->first()->name,
                "company" => $invoice->company,
                "client_id" => !empty($invoice->client) ? $invoice->client->name : '',
            ];

            if ($invoice->currency == '$') {
                $formattedInvoice["price"] = \Auth::user()->priceFormat2($invoice->getDue());
                $formattedInvoice["tax"] = \Auth::user()->priceFormat2($invoice->getTotalTax());
            } else {
                $formattedInvoice["price"] = \Auth::user()->priceFormat($invoice->getDue());
                $formattedInvoice["tax"] = \Auth::user()->priceFormat($invoice->getTotalTax());
            }

            foreach($invoice->items as $product)
            {
                if($invoice->currency == '$')
                {
                    $projectName = $product->projects ? $product->projects->project_name : '-';
                    $result[] = array_merge($formattedInvoice, [
                        "project_name" => $projectName,
                    ]);
                }
                else
                {
                    $projectName = $product->projects ? $product->projects->project_name : '-';
                    $result[] = array_merge($formattedInvoice, [
                        "project_name" => $projectName,
                    ]);
                }
            }
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [
            "Invoice Id",
            "Issue Date",
            "Due Date",
            "Send Date",
            "Category",
            "Company",
            "Client Name",
            "Price (After Tax)",
            "Tax",
            "Product Name",
        ];
    }
}
