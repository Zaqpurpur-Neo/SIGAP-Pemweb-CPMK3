<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code — {{ $item->code }}</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; background: white; }
            .print-card { box-shadow: none !important; border: 1px solid #ccc; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-8">
    <div class="print-card bg-white rounded-xl shadow-lg p-8 w-full max-w-sm text-center">

        {{-- Header --}}
        <div class="mb-4">
            <div class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-1">SIGAP Inventaris</div>
            <h2 class="text-xl font-bold text-gray-900">{{ $item->name }}</h2>
            <p class="text-sm font-mono text-gray-500">{{ $item->code }}</p>
        </div>

        {{-- QR Code --}}
        <div class="flex justify-center my-6">
            <div id="qrcode"></div>
        </div>

        {{-- Info --}}
        <div class="text-left space-y-1 text-sm border-t border-gray-100 pt-4 mb-6">
            <div class="flex justify-between">
                <span class="text-gray-500">Kategori</span>
                <span class="font-medium">{{ $item->category->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Lokasi</span>
                <span class="font-medium">{{ $item->location->name }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Stok</span>
                <span class="font-medium {{ $item->is_low_stock ? 'text-red-600' : 'text-green-600' }}">
                    {{ $item->stock }} {{ $item->unit }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Dicetak</span>
                <span class="font-medium">{{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Actions --}}
        <div class="no-print flex gap-3 justify-center">
            <button onclick="window.print()"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                🖨️ Print
            </button>
            <a href="{{ route('items.show', $item) }}"
               class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition text-center">
                ← Kembali
            </a>
        </div>
    </div>

    <script>
        new QRCode(document.getElementById("qrcode"), {
            text: {!! Js::from($qrData) !!},
            width: 180,
            height: 180,
            colorDark: "#1e293b",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    </script>
</body>
</html>
