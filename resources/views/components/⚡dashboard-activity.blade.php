<?php
use Livewire\Component;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use App\Models\Mutation;

new #[Lazy] class extends Component {
    #[Computed]
    public function todayTotal(): int
    {
        return Mutation::whereDate("date", today())->count();
    }

    #[Computed]
    public function todayIn(): int
    {
        return Mutation::whereDate("date", today())
            ->where("type", "in")
            ->sum("quantity");
    }

    #[Computed]
    public function todayOut(): int
    {
        return Mutation::whereDate("date", today())
            ->where("type", "out")
            ->sum("quantity");
    }
};
?>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aktivitas Hari Ini</h3>

    <div wire:loading.block class="grid grid-cols-3 gap-4 animate-pulse">
        @for($i = 0; $i < 3; $i++)
        <div class="h-16 bg-gray-200 rounded-lg"></div>
        @endfor
    </div>

    <div wire:loading.remove class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="text-center p-4 bg-blue-50 rounded-lg">
            <p class="text-3xl font-bold text-blue-600">{{ $this->todayTotal }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Transaksi</p>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-lg">
            <p class="text-3xl font-bold text-green-600">+{{ $this->todayIn }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Barang Masuk</p>
        </div>
        <div class="text-center p-4 bg-red-50 rounded-lg">
            <p class="text-3xl font-bold text-red-600">-{{ $this->todayOut }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Barang Keluar</p>
        </div>
    </div>
</div>
