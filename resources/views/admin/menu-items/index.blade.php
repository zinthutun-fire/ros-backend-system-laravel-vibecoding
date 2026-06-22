@extends('layouts.admin')
@section('title', 'Menu Items')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Menu Items</h2>
    <button @click="$dispatch('open-modal', {mode: 'create'})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Add Item</button>
</div>
@if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Name</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Kitchen</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Modifiers</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
            @foreach($menuItems as $item)
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
                    <button @click="$dispatch('open-modal', {mode: 'edit', id: {{ $item->id }}, name: '{{ $item->name }}', category_id: {{ $item->category_id }}, kitchen_id: {{ $item->kitchen_id }}, price: {{ $item->price }}, description: '{{ $item->description ?? '' }}', status: '{{ $item->status }}'})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                    @if($item->has_modifiers)
                    <button @click="$dispatch('open-modifier-modal', {item_id: {{ $item->id }}, item_name: '{{ $item->name }}'})" class="text-green-600 hover:text-green-800 text-xs">Modifiers</button>
                    @endif
                    <form method="POST" action="{{ route('admin.menu-items.delete', $item->id) }}" class="inline" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800 text-xs">Delete</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div x-data="{ open: false, mode: 'create', form: { name: '', category_id: '', kitchen_id: '', price: '', description: '', status: 'available' } }" x-cloak
     @open-modal.window="open = true; mode = $event.detail.mode; if(mode=='edit') { form = $event.detail; form.id = $event.detail.id; } else { form = { name: '', category_id: '', kitchen_id: '', price: '', description: '', status: 'available' } }"
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
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>

<div x-data="{ open: false, form: { name: '', price_adjustment: 0 }, item_id: null, item_name: '' }" x-cloak
     @open-modifier-modal.window="open = true; item_id = $event.detail.item_id; item_name = $event.detail.item_name; form = { name: '', price_adjustment: 0, menu_item_id: item_id }"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-sm">
        <h3 class="text-lg font-semibold mb-1">Add Modifier</h3>
        <p class="text-sm text-gray-500 mb-4" x-text="'for ' + item_name"></p>
        <form method="POST" action="{{ route('admin.menu-items.modifiers.store') }}">
            @csrf
            <input type="hidden" name="menu_item_id" x-model="item_id">
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="e.g. Extra Cheese"></div>
                <div><label class="block text-sm font-medium text-gray-700">Price Adjustment ($)</label><input type="number" step="0.01" name="price_adjustment" x-model="form.price_adjustment" class="w-full px-3 py-2 border rounded-lg text-sm"></div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700">Add Modifier</button>
            </div>
        </form>
    </div>
</div>
@endsection
