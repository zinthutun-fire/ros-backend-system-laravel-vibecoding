@extends('layouts.admin')
@section('title', 'Tables')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Tables</h2>
    <button @click="$dispatch('open-modal', {mode: 'create'})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Add Table</button>
</div>
@if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif

<form method="GET" class="flex items-center gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by table no..." class="px-3 py-2 border rounded-lg text-sm w-64">
    <select name="area_id" class="px-3 py-2 border rounded-lg text-sm">
        <option value="">All Areas</option>
        @foreach($areas as $area)
            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
    @if(request('search') || request('area_id'))
        <a href="{{ route('admin.tables') }}" class="px-4 py-2 border rounded-lg text-sm hover:bg-gray-50">Clear</a>
    @endif
</form>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">#</th><th class="px-4 py-3">Table No</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">Area</th><th class="px-4 py-3">Capacity</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
            @forelse($tables as $table)
            <tr class="border-t">
                <td class="px-4 py-3">{{ $table->sort_order }}</td>
                <td class="px-4 py-3 font-medium">{{ $table->table_no }}</td>
                <td class="px-4 py-3">{{ $table->name }}</td>
                <td class="px-4 py-3">{{ $table->area->name }}</td>
                <td class="px-4 py-3">{{ $table->capacity }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs @switch($table->status) @case('available') bg-green-100 text-green-700 @break @case('occupied') bg-blue-100 text-blue-700 @break @case('payment') bg-yellow-100 text-yellow-700 @break @default bg-gray-100 text-gray-700 @endswitch">{{ $table->status }}</span></td>
                <td class="px-4 py-3 space-x-2">
                    <button @click="$dispatch('open-modal', {mode: 'edit', id: {{ $table->id }}, table_no: '{{ $table->table_no }}', name: '{{ $table->name }}', capacity: {{ $table->capacity }}, area_id: {{ $table->area_id }}, sort_order: {{ $table->sort_order }}})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                    <form method="POST" action="{{ route('admin.tables.delete', $table->id) }}" class="inline" onsubmit="return confirm('Delete this table?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No tables found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div x-data="{ open: false, mode: 'create', form: { table_no: '', name: '', capacity: 4, area_id: '', sort_order: 0 } }"
     x-cloak
     @open-modal.window="open = true; mode = $event.detail.mode; if(mode=='edit') { form.id = $event.detail.id; form.table_no = $event.detail.table_no; form.name = $event.detail.name; form.capacity = $event.detail.capacity; form.area_id = $event.detail.area_id; form.sort_order = $event.detail.sort_order; } else { form = { id: '', table_no: '', name: '', capacity: 4, area_id: '', sort_order: 0 } }"
     x-show="open"
     class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
     @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Add Table' : 'Edit Table'"></h3>
        <form :action="mode === 'create' ? '{{ route('admin.tables.store') }}' : '{{ route('admin.tables.update', '_id_') }}'.replace('_id_', form.id)" method="POST">
            @csrf
            <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Table No</label><input type="text" name="table_no" x-model="form.table_no" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Capacity</label><input type="number" name="capacity" x-model="form.capacity" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Area</label>
                    <select name="area_id" x-model="form.area_id" required class="w-full px-3 py-2 border rounded-lg text-sm">
                        @foreach($areas as $area)<option value="{{ $area->id }}">{{ $area->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Sort Order</label><input type="number" name="sort_order" x-model="form.sort_order" class="w-full px-3 py-2 border rounded-lg text-sm"></div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection