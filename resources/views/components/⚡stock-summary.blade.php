<?php
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use App\Models\{Item, Category, Location};

new #[Lazy] class extends Component {
    #[On("item-saved")]
    #[On("mutation-saved")]
    public function refresh(): void {}

    #[Computed]
    public function totalItems(): int
    {
        return Item::count();
    }

    #[Computed]
    public function lowStockCount(): int
    {
        return Item::whereColumn("stock", "<=", "minimum_stock")->count();
    }

    #[Computed]
    public function totalCategories(): int
    {
        return Category::count();
    }

    #[Computed]
    public function totalLocations(): int
    {
        return Location::count();
    }
};
?>

<div>
    {{-- Skeleton saat lazy loading --}}
    <div wire:loading.block class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @for($i = 0; $i < 4; $i++)
        <div class="bg-white rounded-lg shadow p-5 animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-3"></div>
            <div class="h-8 bg-gray-200 rounded w-1/2"></div>
        </div>
        @endfor
    </div>

    {{-- Stat Cards --}}
    <div wire:loading.remove class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
            <p class="text-sm font-medium text-gray-500">Total Barang</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $this->totalItems }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
            <p class="text-sm font-medium text-gray-500">Stok Rendah</p>
            <p class="text-3xl font-bold mt-1 {{ $this->lowStockCount > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $this->lowStockCount }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
            <p class="text-sm font-medium text-gray-500">Kategori</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $this->totalCategories }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
            <p class="text-sm font-medium text-gray-500">Lokasi</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $this->totalLocations }}</p>
        </div>
    </div>
</div>
