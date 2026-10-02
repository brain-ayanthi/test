<div class="iv-filter">
 <label>Search<input name="q" value="{{ request('q') }}" maxlength="100" placeholder="Product, SKU or reference"></label>
 <label>Record type<select name="type"><option value="">All records</option>@foreach($kinds as $code=>$label)<option value="{{ $code }}" @selected(request('type')===$code)>{{ $label }}</option>@endforeach</select></label>
 <label>Direction<select name="direction"><option value="">All</option>@foreach(['in'=>'Increase','out'=>'Decrease'] as $value=>$label)<option value="{{ $value }}" @selected(request('direction')===$value)>{{ $label }}</option>@endforeach</select></label>
 <label>Recorded from<input type="date" name="from" value="{{ request('from') }}"></label><label>Recorded to<input type="date" name="to" value="{{ request('to') }}"></label>
 <label>Rows<select name="per_page">@foreach([10,25,50,100] as $n)<option value="{{ $n }}" @selected((int)request('per_page',25)===$n)>{{ $n }}</option>@endforeach</select></label>
 <button class="iv-btn iv-active" type="submit">Filter</button>
</div>
