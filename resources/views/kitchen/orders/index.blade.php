@extends('layouts.kitchen')
@section('title', 'Orders')
@section('content')
@php
$initialData = json_encode($orders);
@endphp
<div x-data="ordersManager({{ $initialData }})">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-semibold text-gray-100">Kitchen Orders</h2>
        <div class="flex items-center space-x-3">
            <span class="text-xs text-green-400">● Live</span>
            <button @click="window.location.reload()" class="text-xs text-gray-400 hover:text-gray-200 border border-gray-700 px-3 py-1 rounded">Refresh</button>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-900 text-green-300 px-4 py-3 rounded-lg mb-4 text-sm border border-green-800">{{ session('success') }}</div>
    @endif

    <template x-if="!orders.length">
        <div class="bg-gray-800 rounded-xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-gray-500 text-lg">No pending orders</p>
            <p class="text-gray-600 text-sm mt-1">Waiting for new orders from waiters...</p>
        </div>
    </template>

    <template x-for="order in orders" :key="order.order_id">
        <div class="bg-gray-800 rounded-xl shadow-lg border border-gray-700 overflow-hidden mb-4">
            <div class="flex items-center justify-between px-5 py-3 bg-gray-700/50 border-b border-gray-700">
                <div class="flex items-center space-x-4">
                    <span class="text-lg font-bold text-gray-100" x-text="'Table ' + order.table_no"></span>
                    <span x-show="order.area_name" class="text-xs text-gray-500" x-text="order.area_name"></span>
                    <span class="text-xs text-gray-500">|</span>
                    <span class="text-sm text-gray-400" x-text="order.order_no"></span>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-xs text-gray-500" x-text="order.elapsed + 'm ago'"></span>
                    <span class="px-2 py-0.5 rounded text-xs font-medium" :class="order.status_class" x-text="order.status_label"></span>
                </div>
            </div>

            <div class="divide-y divide-gray-700">
                <template x-for="item in order.items" :key="item.id">
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-700/50">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <span class="text-gray-100 font-medium" x-text="item.qty + 'x'"></span>
                                <span class="text-gray-100" x-text="item.name"></span>
                                <span class="text-xs px-2 py-0.5 rounded-full" :class="item.status_class" x-text="item.status"></span>
                            </div>
                            <div x-show="item.modifiers.length > 0" class="mt-1 text-xs text-gray-400 ml-8">
                                <template x-for="(mod, mIdx) in item.modifiers" :key="mIdx">
                                    <span x-text="'+ ' + mod"></span><span x-show="mIdx < item.modifiers.length - 1">, </span>
                                </template>
                            </div>
                            <div x-show="item.note" class="mt-1 text-xs text-red-400 ml-8 italic" x-text="'Note: ' + item.note"></div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <form method="POST" action="{{ route('kitchen.orders.status') }}" class="inline" x-show="item.is_pending">
                                @csrf @method('PATCH')
                                <input type="hidden" name="item_ids[]" :value="item.id">
                                <input type="hidden" name="status" value="accepted">
                                <button class="px-3 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-700">Accept</button>
                            </form>
                            <form method="POST" action="{{ route('kitchen.orders.status') }}" class="inline" x-show="item.is_accepted">
                                @csrf @method('PATCH')
                                <input type="hidden" name="item_ids[]" :value="item.id">
                                <input type="hidden" name="status" value="started">
                                <button class="px-3 py-1 bg-orange-600 text-white text-xs rounded hover:bg-orange-700">Start</button>
                            </form>
                            <form method="POST" action="{{ route('kitchen.orders.status') }}" class="inline" x-show="item.can_done">
                                @csrf @method('PATCH')
                                <input type="hidden" name="item_ids[]" :value="item.id">
                                <input type="hidden" name="status" value="completed">
                                <button class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700">Done</button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>

            <div class="px-5 py-2 bg-gray-700/50 border-t border-gray-700 flex items-center space-x-2" x-show="order.non_completed">
                <form method="POST" action="{{ route('kitchen.orders.status') }}" class="inline">
                    @csrf @method('PATCH')
                    <template x-for="item in order.items" :key="'m-' + item.id">
                        <input type="hidden" name="item_ids[]" :value="item.id">
                    </template>
                    <input type="hidden" name="status" value="completed">
                    <button class="text-xs text-green-400 hover:text-green-300">Mark all done</button>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
let kitchenAudioCtx = null;
document.addEventListener('click', () => {
    if (!kitchenAudioCtx) {
        kitchenAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
}, { once: true });

function playKitchenAlert() {
    try {
        const ctx = kitchenAudioCtx;
        if (!ctx) return;
        if (ctx.state === 'suspended') ctx.resume();
        const now = ctx.currentTime;
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(660, now);
        gain1.gain.setValueAtTime(0.25, now);
        gain1.gain.exponentialRampToValueAtTime(0.01, now + 0.2);
        osc1.connect(gain1).connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.2);

        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(880, now + 0.12);
        gain2.gain.setValueAtTime(0.25, now + 0.12);
        gain2.gain.exponentialRampToValueAtTime(0.01, now + 0.32);
        osc2.connect(gain2).connect(ctx.destination);
        osc2.start(now + 0.12);
        osc2.stop(now + 0.32);
    } catch (e) {}
}

function ordersManager(initialOrders) {
    return {
        orders: initialOrders,
        printed: new Set(),

        init() {
            this.printed = new Set(JSON.parse(localStorage.getItem('kitchen_printed_orders') || '[]'));
            this.orders.forEach(o => this.printed.add(o.order_no));
            setInterval(() => this.poll(), 10000);
        },

        async poll() {
            try {
                const res = await fetch('/kitchen/orders/data');
                const data = await res.json();
                this.orders = data.orders;
                this.autoPrint(data.orders);
            } catch (e) {}
        },

        autoPrint(newOrders) {
            newOrders.forEach(order => {
                if (!this.printed.has(order.order_no)) {
                    playKitchenAlert();
                    const printUrl = '/kitchen/orders/' + order.order_id + '/print';
                    window.open(printUrl, '_blank', 'width=300,height=600');
                    this.printed.add(order.order_no);
                    localStorage.setItem('kitchen_printed_orders', JSON.stringify([...this.printed]));
                }
            });
        },
    };
}
</script>
@endsection
