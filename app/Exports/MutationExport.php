<?php

namespace App\Exports;

use App\Models\Mutation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MutationExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles
{
    protected $dateFrom;
    protected $dateTo;
    protected $category;
    protected $type;

    public function __construct(
        $dateFrom = null,
        $dateTo = null,
        $category = null,
        $type = null,
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->category = $category;
        $this->type = $type;
    }

    public function collection()
    {
        // Mengambil data sesuai dengan filter yang aktif
        return Mutation::with(["item.category", "user"])
            ->when($this->type, fn($q) => $q->where("type", $this->type))
            ->when(
                $this->dateFrom,
                fn($q) => $q->whereDate("date", ">=", $this->dateFrom),
            )
            ->when(
                $this->dateTo,
                fn($q) => $q->whereDate("date", "<=", $this->dateTo),
            )
            ->when(
                $this->category,
                fn($q) => $q->whereHas(
                    "item",
                    fn($q2) => $q2->where("category_id", $this->category),
                ),
            )
            ->latest("date")
            ->latest("id")
            ->get();
    }

    public function headings(): array
    {
        return [
            "Tanggal",
            "Kode Barang",
            "Nama Barang",
            "Kategori",
            "Tipe",
            "Jumlah",
            "Dicatat Oleh",
            "Keterangan",
        ];
    }

    public function map($mutation): array
    {
        return [
            $mutation->date->format("d/m/Y"),
            $mutation->item->code ?? "-",
            $mutation->item->name ?? "-",
            $mutation->item->category->name ?? "-",
            $mutation->type === "in" ? "Masuk" : "Keluar",
            $mutation->quantity,
            $mutation->user->name ?? "-",
            $mutation->note ?? "-",
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Membuat baris heading (baris 1) menjadi tebal
            1 => ["font" => ["bold" => true]],
        ];
    }
}
