<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use App\Models\Attribute;

class EvaluationExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $evaluations;
    protected $filters;
    protected $masterCategories;

    public function __construct($evaluations, $filters = [])
    {
        $this->evaluations = $evaluations;
        $this->filters = $filters;
        $this->masterCategories = Attribute::all()->groupBy('category');
    }

    public function collection()
    {
        return $this->evaluations;
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Nama Karyawan',
            'Branch',
            'Penilai',
            'Periode',
            'Tanggal Evaluasi'
        ];

        // Tambahkan heading untuk setiap indikator
        foreach ($this->masterCategories as $category => $indicators) {
            foreach ($indicators as $indicator) {
                $headings[] = $indicator->name;
            }
        }

        // Tambahkan heading untuk summary
        foreach ($this->masterCategories as $category => $indicators) {
            $headings[] = 'Total ' . strtoupper($category);
        }

        $headings[] = 'Total Final Score';
        $headings[] = 'Rating (Bintang)';
        $headings[] = 'Grade';

        return $headings;
    }

    public function map($evaluation): array
    {
        static $no = 1;
        
        $row = [
            $no++,
            $evaluation->evaluatee->name ?? '-',
            $evaluation->evaluatee->employee->branch->name ?? '-',
            $evaluation->evaluator->name ?? '-',
            $evaluation->quarter ?? '-',
            $evaluation->created_at ? $evaluation->created_at->format('d/m/Y H:i') : '-'
        ];

        // Ambil details evaluation
        $details = $evaluation->details->keyBy('indicator_id');

        // Tambahkan data untuk setiap indikator
        foreach ($this->masterCategories as $category => $indicators) {
            foreach ($indicators as $indicator) {
                $score = $details[$indicator->id]->score ?? 0;
                $row[] = $score;
            }
        }

        // Hitung dan tambahkan data summary per kategori
        $totalWeightedScore = 0;
        foreach ($this->masterCategories as $category => $indicators) {
            $categoryScore = 0;
            foreach ($indicators as $indicator) {
                $score = $details[$indicator->id]->score ?? 0;
                $weight = $indicator->weight ?? 0;
                $categoryScore += $score * ($weight / 100);
            }
            $totalWeightedScore += $categoryScore;
            $row[] = number_format($categoryScore, 2);
        }

        // Total Final Score
        $row[] = number_format($totalWeightedScore, 2);

        // Rating (dalam format angka untuk Excel)
        $rating = round($totalWeightedScore * 2) / 2; // Membulatkan ke 0.5 terdekat
        $row[] = $rating;

        // Grade berdasarkan skor
        $grade = '';
        if ($totalWeightedScore >= 4.5) {
            $grade = 'Excellent';
        } elseif ($totalWeightedScore >= 3.5) {
            $grade = 'Very Good';
        } elseif ($totalWeightedScore >= 2.5) {
            $grade = 'Good';
        } elseif ($totalWeightedScore >= 1.5) {
            $grade = 'Fair';
        } else {
            $grade = 'Poor';
        }
        $row[] = $grade;

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        // Style untuk header
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ]
        ]);

        // Style untuk data
        $sheet->getStyle('A2:' . $lastColumn . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ]);

        // Auto-fit row height
        for ($i = 1; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(-1);
        }

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,   // No
            'B' => 20,  // Nama Karyawan
            'C' => 15,  // Branch
            'D' => 20,  // Penilai
            'E' => 12,  // Periode
            'F' => 18,  // Tanggal
        ];

        // Width untuk kolom indikator (hanya skor)
        $currentColumn = 'G';
        foreach ($this->masterCategories as $category => $indicators) {
            foreach ($indicators as $indicator) {
                $widths[$currentColumn] = 8;  // Skor
                $currentColumn++;
            }
        }

        // Width untuk kolom summary
        foreach ($this->masterCategories as $category => $indicators) {
            $widths[$currentColumn] = 12;
            $currentColumn++;
        }

        // Width untuk total, rating, grade
        $widths[$currentColumn] = 15; // Total Final Score
        $currentColumn++;
        $widths[$currentColumn] = 10; // Rating
        $currentColumn++;
        $widths[$currentColumn] = 12; // Grade

        return $widths;
    }

    public function title(): string
    {
        $title = 'Evaluation Report';
        
        if (!empty($this->filters['cw'])) {
            $title .= ' - ' . $this->filters['cw'];
        }
        
        if (!empty($this->filters['branch_id'])) {
            $branch = \App\Models\Branch::find($this->filters['branch_id']);
            if ($branch) {
                $title .= ' - ' . $branch->name;
            }
        }
        
        return $title;
    }
}