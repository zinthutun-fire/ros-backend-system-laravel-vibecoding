@extends('layouts.kitchen')
@section('title', 'Dashboard')
@section('content')
@php
$initialData = json_encode([
    'pendingItems' => $pendingItems,
    'inProgressItems' => $inProgressItems,
    'completedToday' => $completedToday,
    'activeOrders' => $activeOrders,
]);
@endphp
<div x-data="dashboardManager({{ $initialData }})">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-yellow-500">
            <p class="text-sm text-gray-400">Pending Items</p>
            <p class="text-3xl font-bold text-yellow-400" x-text="stats.pendingItems">{{ $pendingItems }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
            <p class="text-sm text-gray-400">In Progress</p>
            <p class="text-3xl font-bold text-blue-400" x-text="stats.inProgressItems">{{ $inProgressItems }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-green-500">
            <p class="text-sm text-gray-400">Completed Today</p>
            <p class="text-3xl font-bold text-green-400" x-text="stats.completedToday">{{ $completedToday }}</p>
        </div>
        <div class="bg-gray-800 rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
            <p class="text-sm text-gray-400">Active Orders</p>
            <p class="text-3xl font-bold text-indigo-400" x-text="stats.activeOrders">{{ $activeOrders }}</p>
        </div>
    </div>

    <div class="bg-gray-800 rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-100 mb-2">Kitchen: {{ $kitchen?->name ?? 'Unassigned' }}</h3>
        <p class="text-gray-400 text-sm">
            @if($kitchen)
                Code: <strong>{{ $kitchen->code }}</strong>
            @else
                No kitchen assigned. Ask an admin to assign you to a kitchen.
            @endif
        </p>
    </div>
</div>

<script>
function dashboardManager(initial) {
    return {
        stats: {
            pendingItems: initial.pendingItems,
            inProgressItems: initial.inProgressItems,
            completedToday: initial.completedToday,
            activeOrders: initial.activeOrders,
        },

        init() {
            setInterval(() => this.poll(), 10000);
        },

        async poll() {
            try {
                const res = await fetch('/kitchen/dashboard/data');
                const data = await res.json();
                this.stats.pendingItems = data.pendingItems;
                this.stats.inProgressItems = data.inProgressItems;
                this.stats.completedToday = data.completedToday;
                this.stats.activeOrders = data.activeOrders;
            } catch (e) {}
        },
    };
}
</script>
@endsection
