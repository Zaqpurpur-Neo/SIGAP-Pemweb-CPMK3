<?php

use Livewire\Component;
use Livewire\Attributes\Lazy;
use App\Models\Item;

new #[Lazy] class extends Component {
    public function render()
    {
        $count = Item::whereColumn("stock", "<=", "minimum_stock")->count();
        return view("livewire.low-stock-badge", compact("count"));
    }
};
?>

<div wire:poll.60s>
    @if($count > 0)
        <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
            {{ $count > 99 ? '99+' : $count }}
        </span>
    @endif
</div>
