<?php

namespace App\Exports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Query items with filters
     */
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

    /**
     * Column headings
     */
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
            'REMARKS'
        ];
    }

    /**
     * Map each row to columns
     */
    public function map($item): array
    {
        return [
            $item->date_in ? date('Y-m-d', strtotime($item->date_in)) : '',
            $item->deadline ? date('Y-m-d', strtotime($item->deadline)) : '',
            $item->assign_by_id ?? '',
            $item->assign_to_id ?? '',
            $item->type_label ?? '',
            $item->company_id ?? '',
            $item->pic_name ?? '',
            $item->product_id ?? '',
            $item->status ?? '',
            $item->remarks ?? ''
        ];
    }

    /**
     * Style the header row
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '22255B'] // Your brand color
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ],
        ];
    }
}
