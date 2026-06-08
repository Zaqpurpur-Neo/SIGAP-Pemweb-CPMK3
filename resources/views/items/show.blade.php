@extends('layouts.app')

@section('title', $item->name)

@section('content')
<div class="space-y-6">
    {{-- Back Button --}}
    <div>
        <a href="{{ route('items.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar Barang
        </a>
    </div>

    {{-- Item Detail Card --}}
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row gap-6">
            @if($item->photo)
                <img src="{{ Storage::url($item->photo) }}" class="w-32 h-32 object-cover rounded-lg flex-shrink-0">
            @else
                <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            @endif
            <div class="flex-1">
                <div class="flex items-start justify-between flex-wrap gap-3">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $item->name }}</h2>
                        <p class="text-gray-500 font-mono">{{ $item->code }}</p>
                        @if($item->description)
                            <p class="mt-2 text-sm text-gray-600">{{ $item->description }}</p>
                        @endif
                    </div>
                    <a href="{{ route('items.qrcode', $item) }}"
                       class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        QR Code
                    </a>
                </div>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Kategori</p>
                        <p class="text-sm font-medium">{{ $item->category->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Lokasi</p>
                        <p class="text-sm font-medium">{{ $item->location->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Stok Saat Ini</p>
                        <p class="text-2xl font-bold {{ $item->is_low_stock ? 'text-red-600' : 'text-green-600' }}">
                            {{ $item->stock }} <span class="text-sm font-normal text-gray-500">{{ $item->unit }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        @if($item->is_low_stock)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Stok Rendah</span>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Stok Aman</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Mutation History Island --}}
    @livewire('item-mutation-history', ['itemId' => $item->id])
</div>
@endsection
