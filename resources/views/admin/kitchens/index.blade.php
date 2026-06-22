@extends('layouts.admin')
@section('title', 'Kitchens')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Kitchens</h2>
    <button @click="$dispatch('open-modal', {mode: 'create'})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Add Kitchen</button>
</div>
@if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Name</th><th class="px-4 py-3">Code</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Menu Items</th><th class="px-4 py-3">Staff</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
            @foreach($kitchens as $k)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $k->name }}</td>
                <td class="px-4 py-3">{{ $k->code }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $k->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $k->status }}</span></td>
                <td class="px-4 py-3">{{ $k->menu_items_count }}</td>
                <td class="px-4 py-3">{{ $k->users_count }}</td>
                <td class="px-4 py-3 space-x-2">
                    <button @click="$dispatch('open-modal', {mode: 'edit', id: {{ $k->id }}, name: '{{ $k->name }}', code: '{{ $k->code }}', status: '{{ $k->status }}'})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                    <form method="POST" action="{{ route('admin.kitchens.delete', $k->id) }}" class="inline" onsubmit="return confirm('Delete this kitchen?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800 text-xs">Delete</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div x-data="{ open: false, mode: 'create', form: { name: '', code: '', status: 'active' } }" x-cloak
     @open-modal.window="open = true; mode = $event.detail.mode; if(mode=='edit') { form = $event.detail; form.id = $event.detail.id; } else { form = { name: '', code: '', status: 'active' } }"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Add Kitchen' : 'Edit Kitchen'"></h3>
        <form :action="mode === 'create' ? '{{ route('admin.kitchens.store') }}' : '{{ route('admin.kitchens.update', '_id_') }}'.replace('_id_', form.id)" method="POST">
            @csrf <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Code</label><input type="text" name="code" x-model="form.code" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" x-model="form.status" class="w-full px-3 py-2 border rounded-lg text-sm"><option value="active">Active</option><option value="inactive">Inactive</option></select>
                </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
