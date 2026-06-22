@extends('layouts.admin')
@section('title', 'Users')
@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-xl font-semibold">Users</h2>
    <button @click="$dispatch('open-modal', {mode: 'create'})" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700">+ Add User</button>
</div>
@if(session('success'))<div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">{{ session('success') }}</div>@endif
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Kitchen</th><th class="px-4 py-3">Active</th><th class="px-4 py-3">Actions</th></tr></thead>
        <tbody>
            @foreach($users as $u)
            <tr class="border-t">
                <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                <td class="px-4 py-3">{{ $u->email }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs
                    @switch($u->role) @case('admin') bg-purple-100 text-purple-700 @break @case('manager') bg-blue-100 text-blue-700 @break @case('cashier') bg-green-100 text-green-700 @break @case('waiter') bg-yellow-100 text-yellow-700 @break @default bg-gray-100 text-gray-700 @endswitch">{{ $u->role }}</span></td>
                <td class="px-4 py-3">{{ $u->kitchen?->code ?? '—' }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs {{ $u->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $u->is_active ? 'Yes' : 'No' }}</span></td>
                <td class="px-4 py-3 space-x-2">
                    <button @click="$dispatch('open-modal', {mode: 'edit', id: {{ $u->id }}, name: '{{ $u->name }}', email: '{{ $u->email }}', role: '{{ $u->role }}', kitchen_id: {{ $u->kitchen_id ?? 'null' }}, is_active: {{ $u->is_active ? 'true' : 'false' }}})" class="text-blue-600 hover:text-blue-800 text-xs">Edit</button>
                    <form method="POST" action="{{ route('admin.users.delete', $u->id) }}" class="inline" onsubmit="return confirm('Delete this user?')">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800 text-xs">Delete</button></form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div x-data="{ open: false, mode: 'create', form: { name: '', email: '', password: '', role: 'waiter', kitchen_id: '', is_active: true } }" x-cloak
     @open-modal.window="open = true; mode = $event.detail.mode; if(mode=='edit') { form = $event.detail; form.id = $event.detail.id; form.password = ''; } else { form = { name: '', email: '', password: '', role: 'waiter', kitchen_id: '', is_active: true } }"
     x-show="open" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.away="open = false">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4" x-text="mode === 'create' ? 'Add User' : 'Edit User'"></h3>
        <form :action="mode === 'create' ? '{{ route('admin.users.store') }}' : '{{ route('admin.users.update', '_id_') }}'.replace('_id_', form.id)" method="POST">
            @csrf <input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : 'POST'">
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700">Name</label><input type="text" name="name" x-model="form.name" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Email</label><input type="email" name="email" x-model="form.email" required class="w-full px-3 py-2 border rounded-lg text-sm"></div>
                <div><label class="block text-sm font-medium text-gray-700">Password</label><input type="password" name="password" x-model="form.password" :required="mode==='create'" class="w-full px-3 py-2 border rounded-lg text-sm" placeholder="Leave blank to keep current"></div>
                <div><label class="block text-sm font-medium text-gray-700">Role</label>
                    <select name="role" x-model="form.role" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="admin">Admin</option><option value="manager">Manager</option><option value="cashier">Cashier</option><option value="waiter">Waiter</option><option value="kitchen">Kitchen</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700">Kitchen</label>
                    <select name="kitchen_id" x-model="form.kitchen_id" class="w-full px-3 py-2 border rounded-lg text-sm">
                        <option value="">— None —</option>
                        @foreach($kitchens as $k)<option value="{{ $k->id }}">{{ $k->name }}</option>@endforeach
                    </select>
                </div>
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
