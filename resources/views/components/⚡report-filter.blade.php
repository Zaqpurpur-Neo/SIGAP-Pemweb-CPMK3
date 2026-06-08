<?php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\{Mutation, Category};

new class extends Component {
    use WithPagination;

    public string $dateFrom = "";
    public string $dateTo = "";
    public string $filterCategory = "";
    public string $filterType = "";

    #[Computed]
    public function mutations()
    {
        return Mutation::with(["item.category", "user"])
            ->when(
                $this->filterType,
                fn($q) => $q->where("type", $this->filterType),
            )
            ->when(
                $this->dateFrom,
                fn($q) => $q->whereDate("date", ">=", $this->dateFrom),
            )
            ->when(
                $this->dateTo,
                fn($q) => $q->whereDate("date", "<=", $this->dateTo),
            )
            ->when(
                $this->filterCategory,
                fn($q) => $q->whereHas(
                    "item",
                    fn($q2) => $q2->where("category_id", $this->filterCategory),
                ),
            )
            ->latest("date")
            ->latest("id")
            ->paginate(20);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy("name")->get();
    }

    public function resetFilter(): void
    {
        $this->reset(["dateFrom", "dateTo", "filterCategory", "filterType"]);
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }
    public function updatingDateTo(): void
    {
        $this->resetPage();
    }
    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }
    public function updatingFilterType(): void
    {
        $this->resetPage();
    }
};
?>

<div>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Laporan Mutasi</h2>
        <p class="text-sm text-gray-600 mt-1">Filter dan lihat data mutasi barang</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="date" wire:model.live="dateFrom"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <input type="date" wire:model.live="dateTo"
                   class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <select wire:model.live="filterCategory"
                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <select wire:model.live="filterType"
                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Tipe</option>
                    <option value="in">Masuk</option>
                    <option value="out">Keluar</option>
                </select>
                <button wire:click="resetFilter"
                        class="px-3 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Reset
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div wire:loading.class="opacity-50" class="overflow-x-auto transition-opacity">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dicatat Oleh</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">QR</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->mutations as $index => $mutation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $this->mutations->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mutation->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $mutation->item->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mutation->item->category->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($mutation->type === 'in')
                                <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">↑ Masuk</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800">↓ Keluar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold {{ $mutation->type === 'in' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $mutation->type === 'in' ? '+' : '-' }}{{ $mutation->quantity }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mutation->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($mutation->item)
                            <a href="{{ route('items.qrcode', $mutation->item) }}"
                               class="text-gray-400 hover:text-gray-600" target="_blank">
                                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm">Tidak ada data untuk filter yang dipilih</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($this->mutations->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $this->mutations->links() }}
        </div>
        @endif
    </div>
</div>
