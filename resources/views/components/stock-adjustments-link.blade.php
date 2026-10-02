{{-- Optional: add @include('components.stock-adjustments-link') in your existing sidebar.
     The real components/sidebar.blade.php was not supplied and is NOT replaced. --}}
@if(auth()->check() && auth()->user()->is_active && auth()->user()->hasAnyRole(['Admin', 'Doctor']) && \Illuminate\Support\Facades\Route::has('stock-adjustments.index'))
<a href="{{ route('stock-adjustments.index') }}" class="flex items-center px-4 py-3 rounded-lg {{ request()->routeIs('stock-adjustments.*') ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700' }}">
    <i class="fas fa-sliders-h mr-3"></i><span>Stock Adjustments</span>
</a>
@endif
