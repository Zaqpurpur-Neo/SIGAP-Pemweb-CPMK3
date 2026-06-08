<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Location;

new class extends Component {
    use WithPagination;

    public string $search = "";
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = "";
    public string $code = "";
    public string $description = "";

    public function rules(): array
    {
        $uniqueNameRule = $this->editingId
            ? "unique:locations,name,{$this->editingId}"
            : "unique:locations,name";

        $uniqueCodeRule = $this->editingId
            ? "unique:locations,code,{$this->editingId}"
            : "unique:locations,code";

        return [
            "name" => ["required", "min:2", "max:100", $uniqueNameRule],
            "code" => [
                "required",
                "min:2",
                "max:10",
                "alpha_num",
                $uniqueCodeRule,
            ],
            "description" => ["nullable", "max:500"],
        ];
    }

    public function messages(): array
    {
        return [
            "name.required" => "Nama lokasi wajib diisi.",
            "name.min" => "Nama lokasi minimal 2 karakter.",
            "name.max" => "Nama lokasi maksimal 100 karakter.",
            "name.unique" => "Nama lokasi sudah digunakan.",
            "code.required" => "Kode lokasi wajib diisi.",
            "code.min" => "Kode lokasi minimal 2 karakter.",
            "code.max" => "Kode lokasi maksimal 10 karakter.",
            "code.alpha_num" =>
                "Kode lokasi hanya boleh berisi huruf dan angka.",
            "code.unique" => "Kode lokasi sudah digunakan.",
            "description.max" => "Deskripsi maksimal 500 karakter.",
        ];
    }

    public function render()
    {
        $locations = Location::withCount("items")
            ->where(function ($q) {
                $q->where("name", "like", "%{$this->search}%")->orWhere(
                    "code",
                    "like",
                    "%{$this->search}%",
                );
            })
            ->orderBy("name")
            ->paginate(10);

        return view("livewire.location-manager", compact("locations"));
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(): void
    {
        $this->reset(["name", "code", "description", "editingId"]);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $location = Location::findOrFail($id);
        $this->editingId = $location->id;
        $this->name = $location->name;
        $this->code = $location->code;
        $this->description = $location->description ?? "";
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            Location::find($this->editingId)->update([
                "name" => $this->name,
                "code" => $this->code,
                "description" => $this->description ?: null,
            ]);
            $this->dispatch(
                "notify",
                message: "Lokasi berhasil diperbarui.",
                type: "success",
            );
        } else {
            Location::create([
                "name" => $this->name,
                "code" => $this->code,
                "description" => $this->description ?: null,
            ]);
            $this->dispatch(
                "notify",
                message: "Lokasi berhasil ditambahkan.",
                type: "success",
            );
        }

        $this->reset(["name", "code", "description", "editingId", "showModal"]);
    }

    public function delete(int $id): void
    {
        $location = Location::withCount("items")->findOrFail($id);

        if ($location->items_count > 0) {
            $this->dispatch(
                "notify",
                message: "Lokasi tidak dapat dihapus karena masih memiliki barang.",
                type: "error",
            );
            return;
        }

        $location->delete();
        $this->dispatch(
            "notify",
            message: "Lokasi berhasil dihapus.",
            type: "success",
        );
    }

    public function closeModal(): void
    {
        $this->reset(["name", "code", "description", "editingId", "showModal"]);
    }
};
?>

<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Manajemen Lokasi</h2>
            <p class="text-sm text-gray-600 mt-1">Kelola lokasi penyimpanan barang inventaris</p>
        </div>
        @if (auth()->user()->role === 'admin')
            <button wire:click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Lokasi
            </button>
        @endif
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Cari lokasi..."
                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Barang</th>
                        @if (auth()->user()->role === 'admin')
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($locations as $index => $location)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $locations->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $location->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $location->code }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">
                                {{ $location->description ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $location->items_count }} barang
                                </span>
                            </td>
                            @if (auth()->user()->role === 'admin')
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <button wire:click="openEdit({{ $location->id }})"
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="delete({{ $location->id }})"
                                            wire:confirm="Apakah Anda yakin ingin menghapus lokasi ini?"
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'admin' ? 6 : 5 }}" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mt-2 text-sm">Tidak ada lokasi ditemukan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($locations->hasPages())
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $locations->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                {{-- Modal Content --}}
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                {{ $editingId ? 'Edit Lokasi' : 'Tambah Lokasi' }}
                            </h3>

                            <div class="space-y-4">
                                {{-- Name --}}
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nama Lokasi <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="name"
                                           wire:model.live="name"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Code --}}
                                <div>
                                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                                        Kode Lokasi <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text"
                                           id="code"
                                           wire:model.live="code"
                                           placeholder="contoh: LKA"
                                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('code') border-red-500 @enderror">
                                    @error('code')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        Deskripsi
                                    </label>
                                    <textarea id="description"
                                              wire:model.live="description"
                                              rows="3"
                                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"></textarea>
                                    @error('description')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingId ? 'Update' : 'Simpan' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Menyimpan...
                                </span>
                            </button>
                            <button type="button"
                                    wire:click="closeModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
