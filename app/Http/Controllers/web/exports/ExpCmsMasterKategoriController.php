<?php
namespace App\Http\Controllers\web\exports;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\PortalKategori;
use Illuminate\Http\Request;

class ExpCmsMasterKategoriController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | CMS
    |--------------------------------------------------------------------------
    */

    /**
     * Export categories data
     */
    public function export(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'format'         => 'required|in:excel,csv,pdf',
                'filter'         => 'nullable|string',
                'selected_only'  => 'boolean',
                'selected_ids'   => 'nullable|array',
                'selected_ids.*' => 'string',
            ]);

            $format       = $request->format;
            $filter       = $request->filter ?? 'Semua Data';
            $selectedOnly = $request->selected_only ?? false;
            $selectedIds  = $request->selected_ids ?? [];

            // Get data based on selection
            if ($selectedOnly && ! empty($selectedIds)) {
                // Export selected items only
                $uuids = array_map(function ($uuid_enc) {
                    return Helper::decode($uuid_enc);
                }, $selectedIds);

                $data = PortalKategori::whereIn('uuid', $uuids)
                    ->orderBy("type", "ASC")
                    ->orderBy("nama", "ASC")
                    ->get();

                $exportTitle = count($selectedIds) . " Kategori Terpilih";
            } else {
                // Export all data based on filter
                if ($filter == "Semua Data") {
                    $data = PortalKategori::orderBy("type", "ASC")->orderBy("nama", "ASC")->get();
                } else {
                    $data = PortalKategori::whereType($filter)->orderBy("type", "ASC")->orderBy("nama", "ASC")->get();
                }

                $exportTitle = "Master Kategori - " . $filter;
            }

            // Prepare export data
            $exportData = [];
            foreach ($data as $key => $item) {
                // Count sub categories safely
                $subCount = 0;
                if (method_exists($item, 'RelKategoriSub') && $item->RelKategoriSub) {
                    $subCount = $item->RelKategoriSub->count();
                } elseif (method_exists($item, 'GetJumlahKetegoriSub')) {
                    $subCount = $item->GetJumlahKetegoriSub();
                }

                $exportData[] = [
                    'No'            => $key + 1,
                    'Nama Kategori' => $item->nama,
                    'Slug'          => $item->slug,
                    'Tipe'          => $item->type,
                    'Sub Kategori'  => $subCount,
                    'Status'        => $item->status == 1 ? 'Aktif' : 'Tidak Aktif',
                    'Dibuat'        => $item->created_at ? $item->created_at->format('d/m/Y H:i') : '-',
                    'Diubah'        => $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-',
                ];
            }

            // Generate filename
            $timestamp  = date('Y-m-d_H-i-s');
            $filterSlug = strtolower(str_replace([' ', '/'], '-', $filter));
            $filename   = "master-kategori-{$filterSlug}-{$timestamp}";

            // Export based on format
            switch ($format) {
                case 'excel':
                    return $this->exportToExcel($exportData, $filename, $exportTitle);

                case 'csv':
                    return $this->exportToCsv($exportData, $filename);

                case 'pdf':
                    return $this->exportToPdf($exportData, $filename, $exportTitle);

                default:
                    return response()->json(['message' => 'Format tidak didukung'], 400);
            }

        } catch (\Exception $e) {
            Log::error('Export Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat export: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export to Excel format
     */
    private function exportToExcel($data, $filename, $title)
    {
        // Simple Excel export using basic HTML table
        $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>' . $title . '</title>
    </head>
    <body>
        <h2>' . $title . '</h2>
        <p>Diekspor pada: ' . date('d/m/Y H:i:s') . '</p>
        <table border="1" cellpadding="5" cellspacing="0">
            <thead>
                <tr style="background-color: #f8f9fa;">
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Slug</th>
                    <th>Tipe</th>
                    <th>Sub Kategori</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Diubah</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            $html .= '<td>' . $row['No'] . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Nama Kategori']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Slug']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Tipe']) . '</td>';
            $html .= '<td>' . $row['Sub Kategori'] . '</td>';
            $html .= '<td>' . $row['Status'] . '</td>';
            $html .= '<td>' . $row['Dibuat'] . '</td>';
            $html .= '<td>' . $row['Diubah'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"');
    }

    /**
     * Export to CSV format
     */
    private function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');

            // Add BOM for proper UTF-8 handling in Excel
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Add header row
            fputcsv($file, ['No', 'Nama Kategori', 'Slug', 'Tipe', 'Sub Kategori', 'Status', 'Dibuat', 'Diubah']);

            // Add data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row['No'],
                    $row['Nama Kategori'],
                    $row['Slug'],
                    $row['Tipe'],
                    $row['Sub Kategori'],
                    $row['Status'],
                    $row['Dibuat'],
                    $row['Diubah'],
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF format
     */
    private function exportToPdf($data, $filename, $title)
    {
        // Simple PDF using HTML to PDF conversion
        // For production, consider using libraries like DomPDF or TCPDF

        $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>' . $title . '</title>
        <style>
            body { font-family: Arial, sans-serif; font-size: 12px; }
            h2 { color: #333; margin-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f8f9fa; font-weight: bold; }
            .header { margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="header">
            <h2>' . $title . '</h2>
            <p>Diekspor pada: ' . date('d/m/Y H:i:s') . '</p>
            <p>Total: ' . count($data) . ' kategori</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kategori</th>
                    <th>Tipe</th>
                    <th>Sub</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($data as $row) {
            $html .= '<tr>';
            $html .= '<td>' . $row['No'] . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Nama Kategori']) . '</td>';
            $html .= '<td>' . htmlspecialchars($row['Tipe']) . '</td>';
            $html .= '<td>' . $row['Sub Kategori'] . '</td>';
            $html .= '<td>' . $row['Status'] . '</td>';
            $html .= '<td>' . $row['Dibuat'] . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table></body></html>';

        // For basic PDF, we'll return HTML that browsers can print to PDF
        // For production, use proper PDF libraries
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.html"');
    }
}