<?php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Computed;
use App\Models\{Item, Category, Location};
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithPagination, WithFileUploads;

    public string $search = "";
    public string $filterCategory = "";
    public string $filterLocation = "";
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = "";
    public string $code = "";
    public string $category_id = "";
    public string $location_id = "";
    public string $unit = "";
    public int $stock = 0;
    public int $minimum_stock = 5;
    public string $description = "";
    public $photo = null;
    public ?string $existingPhoto = null;

    public function rules(): array
    {
        $codeRule = $this->editingId
            ? "unique:items,code,{$this->editingId}"
            : "unique:items,code";

        return [
            "name" => ["required", "min:2", "max:200"],
            "code" => ["required", "max:50", $codeRule],
            "category_id" => ["required", "exists:categories,id"],
            "location_id" => ["required", "exists:locations,id"],
            "unit" => ["required", "max:20"],
            "stock" => $this->editingId ? [] : ["required", "integer", "min:0"],
            "minimum_stock" => ["required", "integer", "min:0"],
            "description" => ["nullable", "max:1000"],
            "photo" => ["nullable", "image", "max:2048"],
        ];
    }

    #[Computed]
    public function items()
    {
        return Item::with(["category", "location"])
            ->filter(
                $this->search,
                $this->filterCategory ?: null,
                $this->filterLocation ?: null,
            )
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy("name")->get();
    }

    #[Computed]
    public function locations()
    {
        return Location::orderBy("name")->get();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }
    public function updatingFilterLocation(): void
    {
        $this->resetPage();
    }

    public function openModal(): void
    {
        $this->reset([
            "name",
            "code",
            "category_id",
            "location_id",
            "unit",
            "stock",
            "minimum_stock",
            "description",
            "photo",
            "existingPhoto",
            "editingId",
        ]);
        $this->minimum_stock = 5;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $item = Item::findOrFail($id);
        $this->editingId = $item->id;
        $this->name = $item->name;
        $this->code = $item->code;
        $this->category_id = (string) $item->category_id;
        $this->location_id = (string) $item->location_id;
        $this->unit = $item->unit;
        $this->stock = $item->stock;
        $this->minimum_stock = $item->minimum_stock;
        $this->description = $item->description ?? "";
        $this->existingPhoto = $item->photo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $photoPath = $this->existingPhoto;

        if ($this->photo) {
            if ($this->existingPhoto) {
                Storage::disk("public")->delete($this->existingPhoto);
            }
            $photoPath = $this->photo->store("items", "public");
        }

        if ($this->editingId) {
            Item::findOrFail($this->editingId)->update([
                "name" => $this->name,
                "code" => $this->code,
                "category_id" => $this->category_id,
                "location_id" => $this->location_id,
                "unit" => $this->unit,
                "minimum_stock" => $this->minimum_stock,
                "description" => $this->description ?: null,
                "photo" => $photoPath,
            ]);
            $this->dispatch(
                "notify",
                message: "Barang berhasil diperbarui.",
                type: "success",
            );
        } else {
            Item::create([
                "name" => $this->name,
                "code" => $this->code,
                "category_id" => $this->category_id,
                "location_id" => $this->location_id,
                "unit" => $this->unit,
                "stock" => $this->stock,
                "minimum_stock" => $this->minimum_stock,
                "description" => $this->description ?: null,
                "photo" => $photoPath,
            ]);
            $this->dispatch(
                "notify",
                message: "Barang berhasil ditambahkan.",
                type: "success",
            );
        }

        $this->dispatch("item-saved");
        $this->reset([
            "showModal",
            "name",
            "code",
            "category_id",
            "location_id",
            "unit",
            "stock",
            "description",
            "photo",
            "existingPhoto",
            "editingId",
        ]);
        $this->minimum_stock = 5;
    }

    public function delete(int $id): void
    {
        $item = Item::findOrFail($id);
        if ($item->mutations()->exists()) {
            $this->dispatch(
                "notify",
                message: "Barang tidak dapat dihapus karena memiliki riwayat mutasi.",
                type: "error",
            );
            return;
        }
        if ($item->photo) {
            Storage::disk("public")->delete($item->photo);
        }
        $item->delete();
        $this->dispatch(
            "notify",
            message: "Barang berhasil dihapus.",
            type: "success",
        );
        $this->dispatch("item-saved");
    }

    public function closeModal(): void
    {
        $this->reset([
            "showModal",
            "name",
            "code",
            "category_id",
            "location_id",
            "unit",
            "stock",
            "description",
            "photo",
            "existingPhoto",
            "editingId",
        ]);
        $this->minimum_stock = 5;
    }
};
?>

<div>
    {{-- Stock Summary Island --}}
    @livewire('stock-summary')

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen Barang</h2>
            <p class="text-sm text-gray-600 mt-1">Kelola data barang inventaris laboratorium</p>
        </div>
        <button wire:click="openModal"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Barang
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Cari nama/kode barang..."
                   class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <select wire:model.live="filterCategory"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Semua Kategori</option>
            @foreach($this->categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterLocation"
                class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Semua Lokasi</option>
            @foreach($this->locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div wire:loading.class="opacity-50" class="overflow-x-auto transition-opacity">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Foto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->items as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $this->items->firstItem() + $index }}</td>
                        <td class="px-4 py-3">
                            @if($item->photo)
                                <img src="{{ Storage::url($item->photo) }}"
                                     class="w-10 h-10 rounded-lg object-cover">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $item->code }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                            <a href="{{ route('items.show', $item) }}" class="hover:text-blue-600">{{ $item->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->category->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $item->location->name }}</td>
                        <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">
                            {{ $item->stock }} {{ $item->unit }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($item->is_low_stock)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Stok Rendah
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Stok Aman
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('items.qrcode', $item) }}"
                                   class="text-gray-500 hover:text-gray-700" title="QR Code">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                </a>
                                <button wire:click="openEdit({{ $item->id }})" class="text-blue-600 hover:text-blue-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="delete({{ $item->id }})"
                                        wire:confirm="Apakah Anda yakin ingin menghapus barang ini?"
                                        class="text-red-600 hover:text-red-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            Tidak ada barang ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($this->items->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $this->items->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Form --}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeModal"></div>
            <div class="relative bg-white rounded-lg shadow-xl w-full max-w-2xl">
                <form wire:submit="save" enctype="multipart/form-data">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $editingId ? 'Edit Barang' : 'Tambah Barang' }}
                        </h3>
                    </div>
                    <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">

                        {{-- Name & Code --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang *</label>
                                <input type="text" wire:model.live="name"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kode Barang *</label>
                                <input type="text" wire:model.live="code"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('code') border-red-500 @enderror">
                                @error('code') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Category & Location --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                                <select wire:model="category_id"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('category_id') border-red-500 @enderror">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($this->categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi *</label>
                                <select wire:model="location_id"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 @error('location_id') border-red-500 @enderror">
                                    <option value="">Pilih Lokasi</option>
                                    @foreach($this->locations as $loc)
                                        <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                    @endforeach
                                </select>
                                @error('location_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Unit, Stock, Min Stock --}}
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan *</label>
                                <input type="text" wire:model="unit" placeholder="pcs, unit, set..."
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm @error('unit') border-red-500 @enderror">
                                @error('unit') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            @if(!$editingId)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal *</label>
                                <input type="number" wire:model="stock" min="0"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm @error('stock') border-red-500 @enderror">
                                @error('stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stok Minimum *</label>
                                <input type="number" wire:model="minimum_stock" min="0"
                                       class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm @error('minimum_stock') border-red-500 @enderror">
                                @error('minimum_stock') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Photo Upload --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Foto Barang</label>
                            <input type="file" wire:model="photo" accept="image/*"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            @error('photo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                            {{-- Preview --}}
                            <div wire:loading wire:target="photo" class="mt-2 text-xs text-gray-500">Mengupload...</div>
                            @if($photo)
                                <img src="{{ $photo->temporaryUrl() }}" class="mt-2 h-24 w-24 object-cover rounded-lg">
                            @elseif($existingPhoto)
                                <img src="{{ Storage::url($existingPhoto) }}" class="mt-2 h-24 w-24 object-cover rounded-lg">
                            @endif
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea wire:model="description" rows="2"
                                      class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? 'Update' : 'Simpan' }}</span>
                            <span wire:loading wire:target="save">Menyimpan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
