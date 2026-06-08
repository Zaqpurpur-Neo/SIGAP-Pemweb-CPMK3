<?php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Computed;
use App\Models\Mutation;

new #[Lazy] class extends Component {
    use WithPagination;

    public int $itemId;

    #[Computed]
    public function mutations()
    {
        return Mutation::with("user")
            ->where("item_id", $this->itemId)
            ->latest("date")
            ->latest("id")
            ->paginate(10);
    }
};
?>

<div>
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Riwayat Mutasi</h3>
            <p class="text-sm text-gray-500 mt-1">Semua transaksi yang melibatkan barang ini</p>
        </div>

        <div wire:loading.block class="p-6 animate-pulse space-y-2">
            @for($i = 0; $i < 3; $i++)
            <div class="h-12 bg-gray-200 rounded"></div>
            @endfor
        </div>

        <div wire:loading.remove class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->mutations as $mutation)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mutation->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($mutation->type === 'in')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">↑ Masuk</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">↓ Keluar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold {{ $mutation->type === 'in' ? 'text-green-700' : 'text-red-700' }}">
                            {{ $mutation->type === 'in' ? '+' : '-' }}{{ $mutation->quantity }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-xs truncate">{{ $mutation->note ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $mutation->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-2 text-sm">Belum ada riwayat mutasi</p>
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
