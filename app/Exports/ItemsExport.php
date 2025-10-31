<?php

namespace App\Exports;

use App\Models\Item;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ItemsExport implements FromCollection, WithMapping, WithEvents, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

   public function collection()
{
    $q = Item::query();

    // Apply filters
    if (!empty($this->filters['date_in_from'])) {
        $q->whereDate('date_in', '>=', $this->filters['date_in_from']);
    }
    if (!empty($this->filters['assign_by_id'])) {
        $q->where('assign_by_id', $this->filters['assign_by_id']);
    }
    if (!empty($this->filters['assign_to_id'])) {
        $q->where('assign_to_id', $this->filters['assign_to_id']);
    }
    if (!empty($this->filters['type_label'])) {
        $q->where('type_label', $this->filters['type_label']);
    }
    if (!empty($this->filters['company_id'])) {
        $q->where('company_id', $this->filters['company_id']);
    }
    if (!empty($this->filters['task'])) {
        $q->where('task', 'like', '%' . $this->filters['task'] . '%');
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

    // 🔴 FIX: Cara yang benar untuk force fresh data
    $items = $q->get()->map(function($item) {
        // Force model refresh untuk bypass cache
        return $item->fresh() ?? $item;
    });

    // Define status order
    $statusOrder = ['Expired', 'Pending', 'In Progress' , 'Completed'];

    // Group and sort
    $grouped = collect();

    foreach ($statusOrder as $status) {
        $statusItems = $items->filter(function($item) use ($status) {
            return strcasecmp($item->status, $status) === 0;
        })->sortBy(function($item) {
            return $item->deadline ? Carbon::parse($item->deadline)->timestamp : PHP_INT_MAX;
        })->values();

        // Add section header row + column headers for each section
        if ($statusItems->isNotEmpty()) {
            // Section title row
            $grouped->push((object)[
                'is_section_header' => true,
                'section_title' => strtoupper($status),
            ]);

            // Column headers row for this section
            $grouped->push((object)[
                'is_column_header' => true,
            ]);

            // Add items for this status
            foreach ($statusItems as $item) {
                $grouped->push($item);
            }
        }
    }

    return $grouped;
}
    public function map($item): array
    {
        // Check if this is a section header
        if (isset($item->is_section_header) && $item->is_section_header) {
            return [
                $item->section_title,
                '', '', '', '', '', '', '', '', '', ''
            ];
        }

        // Check if this is a column header row
        if (isset($item->is_column_header) && $item->is_column_header) {
            return [
                'DATE IN',
                'DEADLINE',
                'ASSIGN BY',
                'ASSIGN TO',
                'COMPANY',
                'PIC',
                'PRODUCT',
                'TASK',
                'REMARKS',
                'INTERNAL/CLIENT',
                'STATUS',
            ];
        }

        return [
            $item->date_in   ? Carbon::parse($item->date_in)->format('d/m/Y') : '',
            $item->deadline  ? Carbon::parse($item->deadline)->format('d/m/Y') : '',
            $item->assign_by_id ?? '',
            $item->assign_to_id ?? '',
            $item->company_id ?? '',
            $item->pic_name ?? '',
            $item->product_id ?? '',
            $item->task ?? '',
            $item->remarks ?? '',
            $item->type_label ?? '',
            $item->status ?? '',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastColumn = 'K';

                // Insert 2 rows for title and timestamp
                $sheet->insertNewRowBefore(1, 2);

                // Title row (Row 1) - Left aligned
                $sheet->setCellValue('A1', 'BGOC INFORMATION HUB');
                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF200'],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Timestamp row (Row 2) - Red color
                $sheet->setCellValue('A2', 'Generated at: ' . now()->timezone('Asia/Kuala_Lumpur')->format('d/m/Y H:i'));
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => 'FF0000'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Style section headers and column headers (starting from row 3)
                $highestRow = $sheet->getHighestRow();
                for ($row = 3; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell("A{$row}")->getValue();

                    // Check if this is a section header (all caps status labels)
                    if (in_array($cellValue, ['PENDING', 'IN PROGRESS', 'COMPLETED', 'EXPIRED'])) {
                        $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 16,
                                'color' => ['rgb' => 'FF0000'],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_LEFT,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'B4C7E7'],
                            ],
                        ]);
                        $sheet->getRowDimension($row)->setRowHeight(22);
                    }
                    // Check if this is a column header row (has 'DATE IN' in first cell)
                    elseif ($cellValue === 'DATE IN') {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'FFFFFF'],
                                'size' => 11,
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical'   => Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => '22255B'],
                            ],
                        ]);
                        $sheet->getRowDimension($row)->setRowHeight(18);
                    }
                }
            },
        ];
    }
}
