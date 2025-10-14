<?php

namespace App\Exports;

use App\Models\Item;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithEvents, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $q = Item::query();

        if (!empty($this->filters['date_in_from'])) {
            $q->whereDate('date_in', '>=', $this->filters['date_in_from']);
        }
        if (!empty($this->filters['assign_by_id'])) {
            $q->where('assign_by_id', $this->filters['assign_by_id']);
        }
        if (!empty($this->filters['type_label'])) {
            $q->where('type_label', $this->filters['type_label']);
        }
        if (!empty($this->filters['company_id'])) {
            $q->where('company_id', $this->filters['company_id']);
        }
        if (!empty($this->filters['pic_name'])) {
            $q->where('pic_name', 'like', '%' . $this->filters['pic_name'] . '%');
        }
        if (!empty($this->filters['product_id'])) {
            $q->where('product_id', $this->filters['product_id']);
        }
        if (!empty($this->filters['status'])) {
            $q->where('status', $this->filters['status']);
        }

        return $q->orderBy('deadline', 'asc')
                 ->orderBy('date_in', 'asc')
                 ->get();
    }

    public function headings(): array
    {
        return [
            'DATE IN',
            'DEADLINE',
            'ASSIGN BY',
            'ASSIGN TO',
            'INTERNAL/CLIENT',
            'COMPANY',
            'PIC',
            'PRODUCT',
            'STATUS',
            'REMARKS',
        ];
    }

    public function map($item): array
    {
        return [
            $item->date_in   ? Carbon::parse($item->date_in)->format('d/m/Y') : '',
            $item->deadline  ? Carbon::parse($item->deadline)->format('d/m/Y') : '',
            $item->assign_by_id ?? '',
            $item->assign_to_id ?? '',
            $item->type_label ?? '',
            $item->company_id ?? '',
            $item->pic_name ?? '',
            $item->product_id ?? '',
            $item->status ?? '',
            $item->remarks ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sheet + geometry
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'J'; // 10 columns (A..J). Update if you add columns.

                // Insert 2 rows at the very top for Title + Timestamp
                $sheet->insertNewRowBefore(1, 2);

                // Title row (Row 1)
                $sheet->setCellValue('A1', 'BGOC INFORMATION HUB');
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        // Bright yellow banner like your screenshot
                        'startColor' => ['rgb' => 'FFF200'],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Timestamp row (Row 2)
                $sheet->setCellValue('A2', 'Generated at: ' . now()->format('d/m/Y H:i'));
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                    'font' => ['size' => 11],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Header row is now on Row 3 (because we inserted 2 rows)
                $headerRow = 3;
                $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        // Your brand dark blue #22255b
                        'startColor' => ['rgb' => '22255B'],
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(20);
            },
        ];
    }
}
