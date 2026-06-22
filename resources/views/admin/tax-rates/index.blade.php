@extends('layouts.admin')
@section('title', 'Tax Rates')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Tax Rates</h2>
    <button @click="$dispatch('open-modal', {mode: 'create'})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Add Tax Rate</button>
</div>
@if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Name</th><th class="px-4 py-3">Rate</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Active</th><th class="px-4 py-3">Default</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
            @foreach($taxRates as $tax)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $tax->name }}</td>
                <td class="px-4 py-3">{{ $tax->rate }}%</td>
                <td class="px-4 py-3">{{ $tax->type }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $tax->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">{{ $tax->is_active ? 'Yes' : 'No' }}</span></td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $tax->is_default ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">{{ $tax->is_default ? 'Yes' : 'No' }}</span></td>
                <td class="px-4 py-3 space-x-2">
                    <button @click="$dispatch('open-modal', {mode: 'edit', id: {{ $tax->id }}, name: '{{ $tax->name }}', rate: {{ $tax->rate }}, type: '{{ $tax->type }}', is_active: {{ $tax->is_active ? 'true' : 'false' }}, is_default: {{ $tax->is_default ? 'true' : 'false' }}})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                    <form method="POST" action="{{ route('admin.tax-rates.delete', $tax->id) }}" class="inline" onsubmit="return confirm('Delete this tax rate?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800 text-xs">Delete</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div x-data="{ open: false, mode: 'create', form: { name: '', rate: 0, type: 'percentage', is_active: true, is_default: false } }" x-cloak
     @open-modal.window="open = true; mode = $event.detail.mode; if(mode=='edit') { form = $event.detail; form.id = $event.detail.id; } else { form = { name: '', rate: 0, type: 'percentage', is_active: true, is_default: false } }"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Add Tax Rate' : 'Edit Tax Rate'"></h3>
        <form :action="mode === 'create' ? '{{ route('admin.tax-rates.store') }}' : '{{ route('admin.tax-rates.update', '_id_') }}'.replace('_id_', form.id)" method="POST">
            @csrf <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Rate (%)</label><input type="number" step="0.01" name="rate" x-model="form.rate" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" x-model="form.type" class="w-full px-3 py-2 border rounded-lg text-sm"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select>
                </div>
                <div><label class="flex items-center"><input type="checkbox" name="is_active" x-model="form.is_active" class="mr-2"> Active</label></div>
                <div><label class="flex items-center"><input type="checkbox" name="is_default" x-model="form.is_default" class="mr-2"> Default</label></div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm border rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection
