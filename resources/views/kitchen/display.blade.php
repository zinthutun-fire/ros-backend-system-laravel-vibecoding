<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kitchen Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #0f0f1a; color: #e2e8f0; font-family: system-ui, -apple-system, sans-serif; overflow: hidden; height: 100vh; }
        .container { height: 100vh; overflow-y: auto; padding: 1rem; scroll-behavior: smooth; }
        .container::-webkit-scrollbar { display: none; }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 5px rgba(250,204,21,0.3); border-color: rgba(250,204,21,0.5); }
            50% { box-shadow: 0 0 20px rgba(250,204,21,0.6); border-color: rgba(250,204,21,0.9); }
        }
        .order-card-new { animation: pulse-glow 1.5s ease-in-out 3; }
        .header-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #1e293b; margin-bottom: 0.5rem; }
        .time-display { font-size: 1.2rem; font-weight: 300; color: #64748b; font-variant-numeric: tabular-nums; }
    </style>
</head>
<body>
<div class="container" id="orders-container">
    <div class="header-row">
        <div style="font-size:1.5rem;font-weight:700;color:#f1f5f9">
            {{ auth()->user()->kitchen?->name ?? 'Kitchen' }}
            <span style="font-size:0.9rem;color:#64748b;font-weight:400;margin-left:0.75rem">{{ now()->format('D, M j') }}</span>
        </div>
        <div class="time-display" id="clock">00:00</div>
    </div>

    <div id="orders-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($orders as $group)
        <div class="order-card bg-gray-800/80 rounded-xl border border-gray-700 p-4 {{ $group['items']->contains('status', 'pending') ? 'order-card-new' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <span class="text-2xl font-bold text-white">#{{ $group['table_no'] }}</span>
                    @if($group['area_name'])
                    <span class="text-xs text-gray-500 ml-2">{{ $group['area_name'] }}</span>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">{{ $group['order_no'] }}</div>
                    <div class="text-xs text-gray-600">{{ $group['elapsed'] }}m</div>
                </div>
            </div>
            <div class="space-y-2">
                @foreach($group['items'] as $item)
                <div class="flex items-start justify-between py-1 border-b border-gray-700/50 last:border-0">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2">
                            <span class="text-lg font-medium text-gray-100">{{ $item->qty }}x</span>
                            <span class="text-lg text-gray-100">{{ $item->menuItem?->name ?? 'Deleted' }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                @switch($item->status)
                                    @case('pending') bg-yellow-900 text-yellow-300 @break
                                    @case('accepted') bg-blue-900 text-blue-300 @break
                                    @case('started') bg-orange-900 text-orange-300 @break
                                    @case('completed') bg-green-900 text-green-300 @break
                                    @default bg-gray-700 text-gray-400
                                @endswitch">{{ $item->status }}</span>
                        </div>
                        @if($item->modifiers->count() > 0)
                        <div class="text-sm text-gray-400 ml-8 mt-0.5">
                            @foreach($item->modifiers as $mod)
                            <span>+ {{ $mod->name }}</span>{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </div>
                        @endif
                        @if($item->note)
                        <div class="text-sm text-red-400 ml-8 mt-0.5 italic">Note: {{ $item->note }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="col-span-full flex items-center justify-center" style="height:60vh">
            <div class="text-center">
                <div style="font-size:4rem;margin-bottom:1rem">🍽️</div>
                <p class="text-gray-500 text-xl">No pending orders</p>
                <p class="text-gray-600 text-sm mt-2">Waiting for incoming orders...</p>
            </div>
        </div>
        @endforelse
    </div>
</div>

<script>
function updateClock() {
    const now = new Date();
    document.getElementById('clock').textContent = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
}
updateClock();
setInterval(updateClock, 1000);

let displayAudioCtx = null;
document.addEventListener('click', () => {
    if (!displayAudioCtx) {
        displayAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
}, { once: true });

function playDisplayAlert() {
    try {
        const ctx = displayAudioCtx;
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

let knownOrderNos = new Set();
document.querySelectorAll('.order-card').forEach(card => {
    const t = card.textContent || '';
    const m = t.match(/ORD-[\w-]+/);
    if (m) knownOrderNos.add(m[0]);
});
function reloadOrders() {
    fetch(window.location.href, { headers: { 'Accept': 'text/html' } })
        .then(r => r.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newGrid = doc.getElementById('orders-grid');
            const oldGrid = document.getElementById('orders-grid');
            if (newGrid && oldGrid) {
                oldGrid.innerHTML = newGrid.innerHTML;
            }
            const cards = doc.querySelectorAll('.order-card');
            cards.forEach(card => {
                const text = card.textContent || '';
                const match = text.match(/ORD-[\w-]+/);
                if (match && !knownOrderNos.has(match[0])) {
                    playDisplayAlert();
                    knownOrderNos.add(match[0]);
                }
            });
        })
        .catch(() => {});
}
setInterval(reloadOrders, 15000);
</script>
</body>
</html>
