@extends('layouts.admin')
@section('title', 'Menu Items')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Menu Items</h2>
    <button @click="$dispatch('open-modal', {mode: 'create'})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Add Item</button>
</div>
@if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif

<form method="GET" class="flex items-center gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." class="px-3 py-2 border rounded-lg text-sm w-64">
    <select name="category_id" class="px-3 py-2 border rounded-lg text-sm">
        <option value="">All Categories</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
        @endforeach
    </select>
    <select name="kitchen_id" class="px-3 py-2 border rounded-lg text-sm">
        <option value="">All Kitchens</option>
        @foreach($kitchens as $k)
            <option value="{{ $k->id }}" {{ request('kitchen_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
    @if(request('search') || request('category_id') || request('kitchen_id'))
        <a href="{{ route('admin.menu-items') }}" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Clear</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Name</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Kitchen</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Modifiers</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
            @forelse($menuItems as $item)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $item->name }}</td>
                <td class="px-4 py-3">{{ $item->category->name }}</td>
                <td class="px-4 py-3">{{ $item->kitchen->code }}</td>
                <td class="px-4 py-3">${{ number_format($item->price, 2) }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $item->status === 'available' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $item->status }}</span></td>
                <td class="px-4 py-3">
                    @if($item->has_modifiers && $item->activeModifiers->count() > 0)
                        <span class="text-xs text-gray-500">{{ $item->activeModifiers->count() }} modifiers</span>
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 space-x-2">
                    <button @click="$dispatch('open-modal', {mode: 'edit', id: {{ $item->id }}, name: '{{ $item->name }}', category_id: {{ $item->category_id }}, kitchen_id: {{ $item->kitchen_id }}, price: {{ $item->price }}, description: '{{ $item->description ?? '' }}', has_modifiers: {{ $item->has_modifiers ? 'true' : 'false' }}, status: '{{ $item->status }}'})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                    @php
                        $modifiersData = $item->activeModifiers->map(fn($m) => [
                            'id' => $m->id,
                            'name' => $m->name,
                            'price_adjustment' => (float) $m->price_adjustment,
                            'is_active' => $m->is_active,
                        ])->values()->toArray();
                    @endphp
                    <button @click='$dispatch("open-modifier-modal", {item_id: {{ $item->id }}, item_name: @json($item->name), modifiers: {{ json_encode($modifiersData) }}})' class="text-green-600 hover:text-green-800 text-xs">Modifiers</button>
                    <form method="POST" action="{{ route('admin.menu-items.delete', $item->id) }}" class="inline" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800 text-xs">Delete</button></form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No menu items found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Menu Item Create/Edit Modal --}}
<div x-data="{ open: false, mode: 'create', form: { name: '', category_id: '', kitchen_id: '', price: '', description: '', has_modifiers: false, status: 'available' } }" x-cloak
     @open-modal.window="open = true; mode = $event.detail.mode; if(mode=='edit') { form = $event.detail; form.id = $event.detail.id; } else { form = { name: '', category_id: '', kitchen_id: '', price: '', description: '', has_modifiers: false, status: 'available' } }"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg">
        <h3 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Add Menu Item' : 'Edit Menu Item'"></h3>
        <form :action="mode === 'create' ? '{{ route('admin.menu-items.store') }}' : '{{ route('admin.menu-items.update', '_id_') }}'.replace('_id_', form.id)" method="POST">
            @csrf <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2"><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Category</label>
                    <select name="category_id" x-model="form.category_id" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Kitchen</label>
                    <select name="kitchen_id" x-model="form.kitchen_id" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        @foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Price ($)</label><input type="number" step="0.01" name="price" x-model="form.price" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" x-model="form.status" class="w-full px-3 py-2 border rounded-lg text-sm"><option value="available">Available</option><option value="unavailable">Unavailable</option></select>
                </div>
                <div class="col-span-2"><label class="block text-sm font-medium text-gray-700">Description</label><textarea name="description" x-model="form.description" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea></div>
                <div class="col-span-2 flex items-center gap-2">
                    <input type="hidden" name="has_modifiers" value="0">
                    <input type="checkbox" name="has_modifiers" value="1" x-model="form.has_modifiers" id="has_modifiers" class="rounded border-gray-300">
                    <label for="has_modifiers" class="text-sm text-gray-700">Has modifiers</label>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- Manage Modifiers Modal --}}
<div x-data="{ open: false, item_id: null, item_name: '', modifiers: [] }" x-cloak
     @open-modifier-modal.window="open = true; item_id = $event.detail.item_id; item_name = $event.detail.item_name; modifiers = $event.detail.modifiers"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-start justify-center z-50 pt-10" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg max-h-[85vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Modifiers <span class="text-gray-500 font-normal" x-text="'for ' + item_name"></span></h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>

        <template x-if="modifiers.length > 0">
            <div class="mb-6">
                <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Current Modifiers</h4>
                <div class="space-y-2">
                    <template x-for="mod in modifiers" :key="mod.id">
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2 text-sm">
                            <div class="flex items-center gap-3">
                                <span x-text="mod.name" class="font-medium"></span>
                                <span x-text="'$' + parseFloat(mod.price_adjustment).toFixed(2)" class="text-gray-500"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click='$dispatch("open-edit-modifier", {id: mod.id, name: mod.name, price_adjustment: mod.price_adjustment, is_active: mod.is_active, menu_item_id: item_id})'
                                        class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                                <form method="POST" :action="'{{ route('admin.menu-items.modifiers.delete', '_id_') }}'.replace('_id_', mod.id)" class="inline" onsubmit="return confirm('Delete this modifier?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
        <template x-if="modifiers.length === 0">
            <p class="text-sm text-gray-400 mb-6">No modifiers yet.</p>
        </template>

        {{-- Add Modifier Form --}}
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Add New Modifier</h4>
        <form method="POST" action="{{ route('admin.menu-items.modifiers.store') }}">
            @csrf
            <input type="hidden" name="menu_item_id" x-model="item_id">
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Name</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="e.g. Extra Cheese">
                </div>
                <div class="w-28">
                    <label class="block text-xs text-gray-500 mb-1">Price</label>
                    <input type="number" step="0.01" name="price_adjustment" value="0" class="w-full px-3 py-2 border rounded-lg text-sm">
                </div>
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 whitespace-nowrap">Add</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modifier Modal --}}
<div x-data="{ open: false, form: { id: null, name: '', price_adjustment: 0, is_active: true } }" x-cloak
     @open-edit-modifier.window="open = true; form = { id: $event.detail.id, name: $event.detail.name, price_adjustment: $event.detail.price_adjustment ?? 0, is_active: $event.detail.is_active ?? true }"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold mb-4">Edit Modifier</h3>
        <form method="POST" :action="'{{ route('admin.menu-items.modifiers.update', '_id_') }}'.replace('_id_', form.id)">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Price Adjustment ($)</label><input type="number" step="0.01" name="price_adjustment" x-model="form.price_adjustment" class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" x-model="form.is_active" id="is_active_edit" class="rounded border-gray-300">
                    <label for="is_active_edit" class="text-sm text-gray-700">Active</label>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection