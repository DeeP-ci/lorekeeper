{{-- relative thumbnail shown on overview page --}}
<div class="col-md-3 col-6 text-center">
    <div>
        <a href="{{ $lineage->url }}"><img src="{{ $lineage->thumbnail }}" class="img-thumbnail" /></a>
    </div>
    <div class="mt-1 mb-2">
        <a href="{{ $lineage->url }}" class="h5 mb-0"> @if($lineage->character && !$lineage->character->is_visible) <i class="fas fa-eye-slash"></i> @endif {{ $lineage->name }}</a>
    </div>
</div>