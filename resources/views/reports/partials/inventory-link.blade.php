@if(\App\Support\InventoryAccess::allowed(auth()->user()) && \Illuminate\Support\Facades\Route::has('reports.inventory'))
<div style="margin-bottom:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <a href="{{ route('reports.inventory') }}" style="display:inline-block;background:#2563eb;color:white;padding:10px 16px;border-radius:8px;font-weight:600;text-decoration:none"><i class="fas fa-boxes"></i> Inventory Report</a>
    <span style="font-size:12px;color:#64748b">Current stock, purchase units &amp; recorded movements</span>
</div>
@endif
