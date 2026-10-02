@once
<style>.inv-pagination{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;margin-top:18px;font-size:13px}.inv-pagination .pages{display:flex;gap:5px;flex-wrap:wrap}.inv-pagination a,.inv-pagination span.page{display:inline-block;padding:7px 11px;border:1px solid #cbd5e1;border-radius:6px;background:white;text-decoration:none;color:#334155}.inv-pagination .current{background:#2563eb!important;color:#fff!important;border-color:#2563eb!important}.inv-pagination .disabled{opacity:.45}.inv-pagination .dots{padding:7px 3px}</style>
@endonce
<nav class="inv-pagination" aria-label="Pagination">
 <div>{{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }} records</div>
 <div class="pages">
 @if($paginator->onFirstPage())<span class="page disabled" aria-disabled="true">Previous</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>@endif
 @foreach($elements as $element)
  @if(is_string($element))<span class="dots">{{ $element }}</span>@endif
  @if(is_array($element))@foreach($element as $page=>$url)
   @if($page==$paginator->currentPage())<span class="page current" aria-current="page">{{ $page }}</span>@else<a href="{{ $url }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>@endif
  @endforeach
@endif
 @endforeach
 @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>@else<span class="page disabled" aria-disabled="true">Next</span>@endif
 </div>
</nav>
