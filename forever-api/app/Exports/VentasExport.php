<?php

namespace App\Exports;

use App\Models\Venta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VentasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    public function collection()
    {
        return Venta::with(['user.persona'])->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'NRO. FACTURA',
            'CLIENTE / RAZÓN SOCIAL',
            'NIT / CI',
            'MONTO TOTAL (Bs)',
            'IVA (13%)',
            'CC TOTALES',
            'FECHA Y HORA'
        ];
    }

    public function map($venta): array
    {
        $nombre = $venta->razon_social;
        if (!$nombre && $venta->user && $venta->user->persona) {
            $nombre = $venta->user->persona->nombres . ' ' . $venta->user->persona->apellidos;
        }

        return [
            $venta->nro_factura ?? 'S/N',
            mb_strtoupper($nombre ?? 'Consumidor Final'),
            $venta->nit_ci ?? 'S/N',
            $venta->monto_total ?? 0,
            $venta->monto_iva ?? 0,
            $venta->total_cc ?? 0,
            $venta->created_at ? $venta->created_at->format('d/m/Y H:i') : 'S/F',
        ];
    }

    /**
     * Formato de las celdas (Dinero y Decimales)
     */
    public function columnFormats(): array
    {
        return [
            'D' => '#,##0.00 "Bs"',
            'E' => '#,##0.00 "Bs"',
            'F' => '0.000',
        ];
    }

    /**
     * Diseño y Estilos del Excel
     */
    public function styles(Worksheet $sheet)
    {
        // 1. Estilo para el Encabezado (Fila 1)
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669'], // Verde Esmeralda de Forever
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // 2. Bordes para todos los datos
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ]);

        // 3. Alinear a la izquierda nombres y facturas
        $sheet->getStyle('A2:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        
        // 4. Centrar NIT y Fechas
        $sheet->getStyle('C2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G2:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}