@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
            <p class="text-sm font-medium text-gray-500">Total Barang</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_items'] }}</p>
        </div>
        <a href="{{ route('items.index') }}"
           class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500 hover:shadow-md transition">
            <p class="text-sm font-medium text-gray-500">Stok Rendah</p>
            <p class="text-3xl font-bold mt-1 {{ $stats['low_stock'] > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $stats['low_stock'] }}
            </p>
        </a>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
            <p class="text-sm font-medium text-gray-500">Mutasi Bulan Ini</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['mutations_month'] }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
            <p class="text-sm font-medium text-gray-500">Total Kategori</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $stats['total_categories'] }}</p>
        </div>
    </div>

    {{-- Grid 2 kolom --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Mutasi Terbaru --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">10 Mutasi Terbaru</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipe</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentMutations as $mutation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $mutation->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $mutation->item->name ?? '-' }}</td>
                            <td class="px-4 py-2 text-center">
                                @if($mutation->type === 'in')
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Masuk</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800">Keluar</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center text-sm font-medium {{ $mutation->type === 'in' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mutation->type === 'in' ? '+' : '-' }}{{ $mutation->quantity }}
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500 text-sm">Belum ada mutasi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Stok Rendah --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Barang Stok Rendah</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Min</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($lowStockItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-sm">
                                <a href="{{ route('items.show', $item) }}" class="font-medium text-blue-600 hover:underline">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600">{{ $item->category->name }}</td>
                            <td class="px-4 py-2 text-center text-sm font-bold text-red-600">{{ $item->stock }}</td>
                            <td class="px-4 py-2 text-center text-sm text-gray-500">{{ $item->minimum_stock }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-green-600 text-sm">✓ Semua stok aman</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Aktivitas Hari Ini (Lazy Island) --}}
    @livewire('dashboard-activity')
</div>
@endsection
