<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OpenRouteCardReportExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
         return $this->query;
    }

    public function headings(): array
    {
         return [
             'S.No',
             'Route Card No',
             'Operation',
             'Issue Date',
             'Raw Material',
             'Issued Qty',
             'UOM',
             'Part Number',
             'OK Qty',
             'Rejected Qty',
             'Rework Qty',
             'Process',
             'BOM',
             'Used Qty',
             'Qty In Process',
             'Used UOM',
             'No. Days',
             'RSP',
             'Group',
             'Machine',
             'RM Requisition No'
         ];
    }

    public function map($rc): array
    {
         static $i = 0;
         ++$i;
         return [
             $i,
             optional($rc->current_rcmaster)->rc_id,
             optional($rc->currentprocessmaster)->operation,
             $rc->open_date,
             optional($rc->rm_master)->name,
             $rc->rm_issue_qty,
             ($rc->process_id == 3) ? 'kg' : 'Nos',
             optional($rc->partmaster)->child_part_no,
             $rc->receive_qty,
             $rc->reject_qty,
             $rc->rework_qty,
             (optional($rc->currentproductprocessmaster)->processMaster) ? (optional($rc->currentproductprocessmaster)->processMaster->operation) : '',
             (optional(\App\Models\BomMaster::where('child_part_id', $rc->part_id)->first()))->manual_usage,
             $rc->issue_qty,
             ($rc->receive_qty - $rc->issue_qty),
             (optional($rc->rm_master)->category) ? (optional($rc->rm_master)->category->name) : '',
             ($rc->open_date) ? (new \DateTime($rc->open_date))->diff(new \DateTime())->days : '',
             (optional($rc->currentproductprocessmaster) && optional($rc->currentproductprocessmaster->foremanMaster)) ? optional($rc->currentproductprocessmaster->foremanMaster)->name : '',
             (optional($rc->partmaster)->group_id) ? (optional(\App\Models\GroupMaster::find(optional($rc->partmaster)->group_id)))->name : '',
             (optional($rc->partmaster)->machine_id) ? (optional(\App\Models\MachineMaster::find(optional($rc->partmaster)->machine_id)))->machine_name : '',
             $rc->rm_requisition_no
         ];
    }

    public function styles(Worksheet $sheet)
    {
         $sheet->getStyle('A1:U1')->getFont()->setBold(true);
    }
} 