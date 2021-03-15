{{-- Groups of relatives shown on overview pages --}}
<h2>
    <a href="{{ $lineage->url . '/lineage/' . $type }}">{{ $type == 'aunts-uncles' ? "Aunts & Uncles" : ucfirst($type) }}</a>
</h2>
<div class="row mb-4">
    @foreach ($relatives as $relative)
        @include('character.lineage._thumb', ['lineage' => $parent ? $relative->parent : $relative->child])
    @endforeach
</div>
<div class="text-right"><a href="{{ $lineage->url.'/lineage/' . $type }}">View all...</a></div>
<hr>