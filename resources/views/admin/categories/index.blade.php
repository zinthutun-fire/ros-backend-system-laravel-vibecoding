@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Categories</h2>
    <button @click="$dispatch('open-modal', {mode: 'create'})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Add Category</button>
</div>
@if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Name</th><th class="px-4 py-3">Sort</th><th class="px-4 py-3">Items</th><th class="px-4 py-3">Active</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
            @foreach($categories as $c)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $c->name }}</td>
                <td class="px-4 py-3">{{ $c->sort_order }}</td>
                <td class="px-4 py-3">{{ $c->menu_items_count }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $c->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $c->is_active ? 'Yes' : 'No' }}</span></td>
                <td class="px-4 py-3 space-x-2">
                    <button @click="$dispatch('open-modal', {mode: 'edit', id: {{ $c->id }}, name: '{{ $c->name }}', description: '{{ $c->description ?? '' }}', sort_order: {{ $c->sort_order }}, is_active: {{ $c->is_active ? 'true' : 'false' }}})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                    <form method="POST" action="{{ route('admin.categories.delete', $c->id) }}" class="inline" onsubmit="return confirm('Delete this category?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800 text-xs">Delete</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div x-data="{ open: false, mode: 'create', form: { name: '', description: '', sort_order: 0, is_active: true } }" x-cloak
     @open-modal.window="open = true; mode = $event.detail.mode; if(mode=='edit') { form = $event.detail; form.id = $event.detail.id; } else { form = { name: '', description: '', sort_order: 0, is_active: true } }"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Add Category' : 'Edit Category'"></h3>
        <form :action="mode === 'create' ? '{{ route('admin.categories.store') }}' : '{{ route('admin.categories.update', '_id_') }}'.replace('_id_', form.id)" method="POST">
            @csrf <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Description</label><textarea name="description" x-model="form.description" class="w-full px-3 py-2 border rounded-lg text-sm"></textarea></div>
                <div><label class="block text-sm font-medium text-gray-700">Sort Order</label><input type="number" name="sort_order" x-model="form.sort_order" class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="flex items-center"><input type="checkbox" name="is_active" x-model="form.is_active" class="mr-2"> Active</label></div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
