<?php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use App\Models\{Item, Mutation};
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination;

    // Filter
    public string $search = "";
    public string $filterType = "";
    public string $dateFrom = "";
    public string $dateTo = "";

    // Form
    public bool $showModal = false;
    public string $itemSearch = "";
    public array $itemResults = [];
    public ?int $item_id = null;
    public string $selectedItemName = "";
    public string $type = "in";
    public int $quantity = 1;
    public string $date = "";
    public string $note = "";

    public function mount(): void
    {
        $this->date = now()->format("Y-m-d");
    }

    public function rules(): array
    {
        return [
            "item_id" => ["required", "exists:items,id"],
            "type" => ["required", "in:in,out"],
            "quantity" => ["required", "integer", "min:1"],
            "date" => ["required", "date"],
            "note" => ["nullable", "max:500"],
        ];
    }

    #[Computed]
    public function mutations()
    {
        return Mutation::with(["item", "user"])
            ->when(
                $this->search,
                fn($q) => $q->whereHas(
                    "item",
                    fn($q2) => $q2
                        ->where("name", "like", "%{$this->search}%")
                        ->orWhere("code", "like", "%{$this->search}%"),
                ),
            )
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
            ->latest("date")
            ->latest("id")
            ->paginate(15);
    }

    public function updatedItemSearch(): void
    {
        if (strlen($this->itemSearch) >= 2) {
            $this->itemResults = Item::where(function ($q) {
                $q->where("name", "like", "%{$this->itemSearch}%")->orWhere(
                    "code",
                    "like",
                    "%{$this->itemSearch}%",
                );
            })
                ->limit(5)
                ->get(["id", "name", "code", "stock", "unit"])
                ->toArray();
        } else {
            $this->itemResults = [];
        }
    }

    public function selectItem(int $id, string $name): void
    {
        $this->item_id = $id;
        $this->selectedItemName = $name;
        $this->itemSearch = "";
        $this->itemResults = [];
    }

    public function openModal(): void
    {
        $this->reset([
            "item_id",
            "selectedItemName",
            "itemSearch",
            "itemResults",
            "type",
            "quantity",
            "note",
            "showModal",
        ]);
        $this->type = "in";
        $this->quantity = 1;
        $this->date = now()->format("Y-m-d");
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $item = Item::lockForUpdate()->findOrFail($this->item_id);

                if ($this->type === "out" && $item->stock < $this->quantity) {
                    throw new \Exception(
                        "STOCK_INSUFFICIENT:{$item->stock}:{$item->unit}",
                    );
                }

                if ($this->type === "in") {
                    $item->increment("stock", $this->quantity);
                } else {
                    $item->decrement("stock", $this->quantity);
                }

                Mutation::create([
                    "item_id" => $this->item_id,
                    "user_id" => auth()->id(),
                    "type" => $this->type,
                    "quantity" => $this->quantity,
                    "date" => $this->date,
                    "note" => $this->note ?: null,
                ]);
            });

            $this->dispatch("mutation-saved");
            $this->dispatch(
                "notify",
                message: "Transaksi berhasil dicatat.",
                type: "success",
            );
            $this->closeModal();
        } catch (\Exception $e) {
            if (str_starts_with($e->getMessage(), "STOCK_INSUFFICIENT:")) {
                [, $stock, $unit] = explode(":", $e->getMessage());
                $this->addError(
                    "quantity",
                    "Stok tidak mencukupi. Stok tersedia: {$stock} {$unit}",
                );
            } else {
                $this->dispatch(
                    "notify",
                    message: "Terjadi kesalahan: " . $e->getMessage(),
                    type: "error",
                );
            }
        }
    }

    public function closeModal(): void
    {
        $this->reset([
            "showModal",
            "item_id",
            "selectedItemName",
            "itemSearch",
            "itemResults",
            "quantity",
            "note",
        ]);
        $this->type = "in";
        $this->quantity = 1;
        $this->date = now()->format("Y-m-d");
        $this->resetValidation();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterType(): void
    {
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
};
?>

<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Mutasi Barang</h2>
            <p class="text-sm text-gray-600 mt-1">Riwayat transaksi masuk dan keluar barang</p>
        </div>
        <button wire:click="openModal"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Catat Transaksi
        </button>
    </div>

    {{-- Filter --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-4 gap-3">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama/kode barang..."
                   class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
        </div>
        <select wire:model.live="filterType"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Tipe</option>
            <option value="in">Masuk</option>
            <option value="out">Keluar</option>
        </select>
        <input type="date" wire:model.live="dateFrom"
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
        <input type="date" wire:model.live="dateTo"
               class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div wire:loading.class="opacity-50" class="overflow-x-auto transition-opacity">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Barang</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->mutations as $index => $mutation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $this->mutations->firstItem() + $index }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mutation->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $mutation->item->code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $mutation->item->name }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($mutation->type === 'in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    ↑ Masuk
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    ↓ Keluar
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold
                            {{ $mutation->type === 'in' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $mutation->type === 'in' ? '+' : '-' }}{{ $mutation->quantity }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">
                            {{ $mutation->note ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mutation->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm">Tidak ada data mutasi</p>
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

    {{-- Modal Form --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-lg">
                <form wire:submit="save">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Catat Transaksi</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">

                        {{-- Item Search --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Barang *</label>
                            @if($item_id && $selectedItemName)
                                <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-300 rounded-lg">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-800">{{ $selectedItemName }}</span>
                                    <button type="button" wire:click="$set('item_id', null); $set('selectedItemName', '')"
                                            class="ml-auto text-blue-500 hover:text-blue-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="relative">
                                    <input type="text" wire:model.live.debounce.300ms="itemSearch"
                                           placeholder="Ketik nama atau kode barang..."
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('item_id') border-red-500 @enderror">
                                    @if(count($itemResults) > 0)
                                    <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                        @foreach($itemResults as $result)
                                        <button type="button"
                                                wire:click="selectItem({{ $result['id'] }}, '{{ addslashes($result['name']) }}')"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex justify-between items-center border-b border-gray-100 last:border-0">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $result['name'] }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $result['code'] }}</div>
                                            </div>
                                            <span class="text-xs text-gray-500">Stok: <strong>{{ $result['stock'] }}</strong> {{ $result['unit'] }}</span>
                                        </button>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            @endif
                            @error('item_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Transaksi *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" wire:model="type" value="in" class="sr-only peer">
                                    <div class="w-full px-4 py-3 text-center rounded-lg border-2 text-sm font-medium
                                        peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700
                                        border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                        ↑ Barang Masuk
                                    </div>
                                </label>
                                <label class="relative flex cursor-pointer">
                                    <input type="radio" wire:model="type" value="out" class="sr-only peer">
                                    <div class="w-full px-4 py-3 text-center rounded-lg border-2 text-sm font-medium
                                        peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700
                                        border-gray-200 text-gray-600 hover:bg-gray-50 transition">
                                        ↓ Barang Keluar
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Quantity & Date --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah *</label>
                                <input type="number" wire:model.live="quantity" min="1"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('quantity') border-red-500 @enderror">
                                @error('quantity')
                                    <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                                <input type="date" wire:model="date"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('date') border-red-500 @enderror">
                                @error('date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Note --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                            <textarea wire:model="note" rows="2"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('note') border-red-500 @enderror"
                                      placeholder="Opsional..."></textarea>
                            @error('note') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3 bg-gray-50">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Simpan Transaksi</span>
                            <span wire:loading wire:target="save">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
