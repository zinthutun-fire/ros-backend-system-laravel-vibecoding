@extends('layouts.cashier')
@section('title', 'Tables')
@section('content')
<div x-data="tableManager()">
    {{-- Header & Filters --}}
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h5 class="fw-semibold mb-1">Table Overview</h5>
            <p class="text-muted small mb-0">
                <span x-text="tables.length" class="fw-semibold text-dark"></span> tables
                · <span x-text="tables.filter(t => t.status === 'available').length" class="fw-semibold text-success"></span> available
                · <span x-text="tables.filter(t => t.status === 'paid').length" class="fw-semibold text-primary"></span> paid
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group btn-group-sm" role="group">
                <button @click="filter = ''" class="btn btn-outline-secondary" :class="filter === '' ? 'active' : ''">All</button>
                <button @click="filter = 'available'" class="btn btn-outline-success" :class="filter === 'available' ? 'active' : ''">Available</button>
                <button @click="filter = 'occupied'" class="btn btn-outline-info" :class="filter === 'occupied' ? 'active' : ''">Occupied</button>
                <button @click="filter = 'payment'" class="btn btn-outline-danger" :class="filter === 'payment' ? 'active' : ''">Payment</button>
                <button @click="filter = 'paid'" class="btn btn-outline-primary" :class="filter === 'paid' ? 'active' : ''">Paid</button>
            </div>
            <small class="text-muted d-flex align-items-center gap-1">
                <i class="bi bi-arrow-repeat text-success"></i> Live
            </small>
        </div>
    </div>

    {{-- Table Grid --}}
    <div class="row g-2" id="table-grid">
        <template x-for="table in filteredTables" :key="table.id">
            <div class="col-4 col-md-3 col-lg-2">
                <button @click="table.status === 'paid' ? closeTable(table) : selectTable(table)"
                    class="card border table-card w-100 text-center p-3 position-relative"
                    :class="{
                        'border-success': table.status === 'available',
                        'border-info': table.status === 'occupied',
                        'border-warning': table.status === 'ordering',
                        'border-danger': table.status === 'payment',
                        'border-primary': table.status === 'paid',
                        'border-secondary': table.status === 'reserved'
                    }">
                    {{-- Merged Badge --}}
                    <template x-if="table.is_merged">
                        <span class="badge bg-purple position-absolute top-0 start-0" style="font-size:0.5rem;">+</span>
                    </template>

                    {{-- Table Number --}}
                    <div class="fs-5 fw-bold text-dark" x-text="table.table_no"></div>

                    {{-- Status Badge --}}
                    <div class="mt-1">
                        <span class="badge badge-status"
                            :class="{
                                'bg-success': table.status === 'available',
                                'bg-info': table.status === 'occupied',
                                'bg-warning text-dark': table.status === 'ordering',
                                'bg-danger': table.status === 'payment',
                                'bg-primary': table.status === 'paid',
                                'bg-secondary': table.status === 'reserved'
                            }" x-text="table.status"></span>
                    </div>

                    {{-- Area --}}
                    <small class="text-muted d-block text-truncate mt-1" x-text="table.area"></small>

                    {{-- Extra --}}
                    <template x-if="table.status === 'payment'">
                        <span class="badge bg-danger mt-2 d-inline-flex align-items-center gap-1" style="font-size:0.6rem;">
                            <span class="spinner-grow spinner-grow-sm" style="width:0.4rem;height:0.4rem;"></span>
                            Bill Requested
                        </span>
                    </template>
                    <template x-if="table.status === 'paid'">
                        <span class="badge bg-primary mt-2" style="font-size:0.6rem;">Click to Close</span>
                    </template>
                    <template x-if="table.status === 'occupied' || table.status === 'payment'">
                        <div class="small fw-bold text-dark mt-2" x-text="table.current_order_price ? table.current_order_price.toFixed(2) + ' Ks' : ''"></div>
                    </template>
                </button>
            </div>
        </template>

        <template x-if="filteredTables.length === 0">
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-table fs-1 text-secondary mb-2 d-block"></i>
                    <p class="fw-medium mb-1">No tables matched</p>
                    <button @click="filter = ''" class="btn btn-sm btn-link text-decoration-none">Clear filter</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Table Detail Modal --}}
    <div x-show="selectedTable" x-cloak @click.away="selectedTable = null" class="position-fixed" style="top:0;left:0;right:0;bottom:0;z-index:1050;">
        <div class="d-flex align-items-center justify-content-center" style="height:100%;background:rgba(0,0,0,0.5);">
            <div @click.stop class="bg-white rounded-3 shadow-lg mx-3" style="max-width:720px;max-height:90vh;overflow-y:auto;width:100%;">
                <template x-if="selectedTable">
                    <div>
                        <div class="d-flex justify-content-between align-items-start p-4 border-bottom">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h5 class="fw-semibold mb-0" x-text="'Table ' + selectedTable.table_no"></h5>
                                    <span class="badge badge-status"
                                        :class="{
                                            'bg-success': selectedTable.status === 'available',
                                            'bg-info': selectedTable.status === 'occupied',
                                            'bg-warning text-dark': selectedTable.status === 'ordering',
                                            'bg-danger': selectedTable.status === 'payment',
                                            'bg-primary': selectedTable.status === 'paid',
                                            'bg-secondary': selectedTable.status === 'reserved'
                                        }" x-text="selectedTable.status"></span>
                                </div>
                                <small class="text-muted" x-text="selectedTable.area"></small>
                                <template x-if="selectedTable.is_merged">
                                    <span class="badge bg-purple bg-opacity-10 text-purple d-inline-flex align-items-center gap-1 mt-1">
                                        <i class="bi bi-layers"></i> Merged (Group: <span x-text="selectedTable.merged_group_code" class="fw-bold font-monospace"></span>)
                                    </span>
                                </template>
                            </div>
                            <button @click="selectedTable = null" class="btn-close"></button>
                        </div>
                        <div class="p-4">
                            <template x-if="selectedTable.orders && selectedTable.orders.length > 0">
                                <div class="d-flex flex-column gap-3">
                                    <template x-for="order in selectedTable.orders" :key="order.id">
                                        <div class="bg-light rounded-3 p-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <h6 class="mb-0 fw-semibold small" x-text="order.order_no"></h6>
                                                    <span class="badge badge-status"
                                                        :class="order.status === 'paid' ? 'bg-success' : 'bg-info'"
                                                        x-text="order.status"></span>
                                                </div>
                                            </div>
                                            <table class="table table-sm small mb-0">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Item</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-end">Price</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <template x-for="item in groupedItems(order.items)" :key="item.name">
                                                        <tr>
                                                            <td>
                                                                <span class="fw-medium" x-text="item.name"></span>
                                                                <template x-if="item.modifiers">
                                                                    <small class="text-muted d-block" x-text="item.modifiers"></small>
                                                                </template>
                                                            </td>
                                                            <td class="text-center fw-medium" x-text="item.qty"></td>
                                                            <td class="text-end fw-medium"><span x-text="item.subtotal.toFixed(2)"></span> Ks</td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                            <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                                                <small class="text-muted fw-medium">Total</small>
                                                <span class="fw-bold"><span x-text="order.grand_total.toFixed(2)"></span> Ks</span>
                                            </div>
                                            <template x-if="order.merge">
                                                <div class="mt-2 p-2 bg-purple bg-opacity-10 border border-purple border-opacity-25 rounded small text-purple d-flex align-items-center gap-2">
                                                    <i class="bi bi-layers"></i>
                                                    Merged with <strong x-text="order.merge.tables.join(', ')"></strong>
                                                    (Group: <span x-text="order.merge.group_code" class="fw-bold font-monospace"></span>)
                                                </div>
                                            </template>
                                            <div class="mt-3 d-flex flex-wrap gap-2" x-show="order.status !== 'paid' && order.status !== 'cancelled'">
                                                <button @click="openTransfer(order)" class="btn btn-sm btn-outline-indigo d-flex align-items-center gap-1">
                                                    <i class="bi bi-arrow-left-right"></i> Transfer
                                                </button>
                                                <button @click="openMerge(order)" class="btn btn-sm btn-outline-purple d-flex align-items-center gap-1">
                                                    <i class="bi bi-layers"></i> Merge
                                                </button>
                                                <a :href="'{{ route('cashier.orders') }}/' + order.id" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1">
                                                    <i class="bi bi-credit-card"></i> Payment
                                                </a>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="!selectedTable.orders || selectedTable.orders.length === 0">
                                <div class="text-center py-5 text-muted">
                                    <i class="bi bi-receipt fs-1 text-secondary mb-2 d-block"></i>
                                    <p class="fw-medium mb-1">No active orders for this table</p>
                                    <template x-if="selectedTable.is_merged">
                                        <small class="text-purple">This table is part of a merged group.</small>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- Transfer Modal --}}
    <div x-show="showTransfer" x-cloak @click.away="showTransfer = false" class="position-fixed" style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1050;display:flex;align-items:center;justify-content:center;">
        <div @click.stop class="bg-white rounded-3 shadow-lg mx-3" style="max-width:480px;width:100%;">
            <div class="p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-indigo bg-opacity-10 rounded p-2">
                        <i class="bi bi-arrow-left-right text-indigo"></i>
                    </div>
                    <h5 class="mb-0 fw-semibold">Transfer Order</h5>
                </div>
                <p class="small text-muted bg-light rounded p-3 mb-3">
                    Transfer <strong class="text-dark" x-text="transferOrder?.order_no"></strong> from
                    Table <strong class="text-dark" x-text="selectedTable?.table_no"></strong>
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-medium">Target Table</label>
                    <select x-model="transferTargetId" class="form-select form-select-sm">
                        <option value="">Select a table...</option>
                        <template x-for="t in availableTables" :key="t.id">
                            <option :value="t.id" x-text="t.table_no + ' (' + t.area + ')'"></option>
                        </template>
                    </select>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button @click="showTransfer = false" class="btn btn-sm btn-secondary">Cancel</button>
                    <button @click="doTransfer()" :disabled="!transferTargetId" class="btn btn-sm btn-primary" :class="{'disabled': !transferTargetId}">Transfer</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Merge Modal --}}
    <div x-show="showMerge" x-cloak @click.away="showMerge = false" class="position-fixed" style="top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:1050;display:flex;align-items:center;justify-content:center;">
        <div @click.stop class="bg-white rounded-3 shadow-lg mx-3" style="max-width:480px;width:100%;">
            <div class="p-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="bg-purple bg-opacity-10 rounded p-2">
                        <i class="bi bi-layers text-purple"></i>
                    </div>
                    <h5 class="mb-0 fw-semibold">Merge Tables</h5>
                </div>
                <p class="small text-muted bg-light rounded p-3 mb-3">
                    Merge Table <strong class="text-dark" x-text="selectedTable?.table_no"></strong> with:
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-medium">Target Table</label>
                    <select x-model="mergeTargetId" class="form-select form-select-sm">
                        <option value="">Select a table...</option>
                        <template x-for="t in mergeableTables" :key="t.id">
                            <option :value="t.id" x-text="t.table_no + ' (' + t.area + ')'"></option>
                        </template>
                    </select>
                </div>
                <div class="d-flex justify-content-end gap-2">
                    <button @click="showMerge = false" class="btn btn-sm btn-secondary">Cancel</button>
                    <button @click="doMerge()" :disabled="!mergeTargetId" class="btn btn-sm btn-purple" :class="{'disabled': !mergeTargetId}">Merge</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-purple { background-color: #6f42c1 !important; }
    .text-purple { color: #6f42c1 !important; }
    .border-purple { border-color: #6f42c1 !important; }
    .bg-indigo { background-color: #6610f2 !important; }
    .text-indigo { color: #6610f2 !important; }
    .btn-outline-indigo { color: #6610f2; border-color: #6610f2; }
    .btn-outline-indigo:hover { background: #6610f2; color: #fff; }
    .btn-outline-purple { color: #6f42c1; border-color: #6f42c1; }
    .btn-outline-purple:hover { background: #6f42c1; color: #fff; }
    .btn-purple { background: #6f42c1; color: #fff; border-color: #6f42c1; }
    .btn-purple:hover { background: #5530a0; color: #fff; }
    .bg-opacity-10 { --bs-bg-opacity: 0.1; }
    .border-opacity-25 { --bs-border-opacity: 0.25; }
    [x-cloak] { display: none !important; }
</style>

<script>
function tableManager() {
    return {
        tables: [],
        filter: '',
        selectedTable: null,
        showTransfer: false,
        showMerge: false,
        transferOrder: null,
        transferTargetId: '',
        mergeTargetId: '',
        pollInterval: null,

        get filteredTables() {
            if (!this.filter) return this.tables;
            return this.tables.filter(t => t.status === this.filter);
        },

        get availableTables() {
            return this.tables.filter(t => t.status === 'available' && t.id !== this.selectedTable?.id);
        },

        get mergeableTables() {
            return this.tables.filter(t =>
                (t.status === 'occupied' || t.status === 'ordering') &&
                t.id !== this.selectedTable?.id
            );
        },

        init() {
            this.loadTables();
            this.pollInterval = setInterval(() => this.loadTables(), 5000);
        },

        destroy() {
            if (this.pollInterval) clearInterval(this.pollInterval);
        },

        async loadTables() {
            try {
                const res = await fetch('{{ route('cashier.tables.data') }}');
                const data = await res.json();
                this.tables = data;
            } catch (e) { console.error('Failed to load tables:', e); }
        },

        async selectTable(table) {
            try {
                const res = await fetch(`/cashier/tables/${table.id}/detail`);
                const data = await res.json();
                this.selectedTable = { ...table, orders: data.orders || [] };
            } catch (e) { console.error('Failed to load table detail:', e); }
        },

        groupedItems(items) {
            if (!items) return [];
            const grouped = {};
            items.forEach(item => {
                const key = item.name + (item.modifiers || '');
                if (grouped[key]) { grouped[key].qty += item.qty; grouped[key].subtotal += item.subtotal; }
                else { grouped[key] = { ...item }; }
            });
            return Object.values(grouped);
        },

        openTransfer(order) { this.transferOrder = order; this.transferTargetId = ''; this.showTransfer = true; },

        async doTransfer() {
            if (!this.transferTargetId || !this.selectedTable || !this.transferOrder) return;
            try {
                const res = await fetch('{{ route('cashier.tables.transfer') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ from_table_id: this.selectedTable.id, to_table_id: parseInt(this.transferTargetId), order_id: this.transferOrder.id }),
                });
                if (!res.ok) throw new Error('Transfer failed');
                this.showTransfer = false; this.selectedTable = null; this.loadTables();
            } catch (e) { alert('Transfer failed: ' + e.message); }
        },

        async closeTable(table) {
            if (!confirm('Close Table ' + table.table_no + ' and make it available?')) return;
            try {
                const res = await fetch('/cashier/tables/' + table.id + '/close', {
                    method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                });
                if (!res.ok) throw new Error('Close failed');
                this.loadTables();
            } catch (e) { alert('Failed to close table: ' + e.message); }
        },

        openMerge(order) { this.mergeTargetId = ''; this.showMerge = true; },

        async doMerge() {
            if (!this.mergeTargetId || !this.selectedTable) return;
            try {
                const res = await fetch('{{ route('cashier.tables.merge') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ table_ids: [this.selectedTable.id, parseInt(this.mergeTargetId)] }),
                });
                if (!res.ok) throw new Error('Merge failed');
                this.showMerge = false; this.selectedTable = null; this.loadTables();
            } catch (e) { alert('Merge failed: ' + e.message); }
        },
    };
}
</script>
@endsection
