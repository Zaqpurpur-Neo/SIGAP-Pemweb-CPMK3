<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MutationExport;

class ReportController extends Controller
{
    public function index()
    {
        return view("reports.index");
    }

    public function downloadExport(Request $request)
    {
        // Ambil parameter filter dari query string
        $dateFrom = $request->query("date_from");
        $dateTo = $request->query("date_to");
        $category = $request->query("category");
        $type = $request->query("type");

        $filename = "Laporan_Mutasi_" . now()->format("Y-m-d_His") . ".xlsx";

        // Download langsung dari controller
        return Excel::download(
            new MutationExport($dateFrom, $dateTo, $category, $type),
            $filename,
        );
    }
}
