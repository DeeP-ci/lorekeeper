<div class="row world-entry">
    <div class="col-12">
        <h3 class="mb-0">
            @if (!$loci->is_visible)
                <i class="fas fa-eye-slash"></i>
            @endif
            {!! $loci->displayName !!}
        </h3>
        @if($loci->chromosome && ($loci->chromosome->is_visible || (Auth::check() && Auth::user()->hasPower('view_hidden_genetics'))))
            <strong>Chromosome</strong>:
            @if (!$loci->chromosome->is_visible)
                <i class="fas fa-eye-slash"></i>
            @endif
            {!! $loci->chromosome->displayName !!}<br>
        @endif
        @if ($loci->type == "gene")
            <strong>Type</strong>: Standard<br>
            <strong>Alleles</strong>:
            @if(Auth::check() && Auth::user()->hasPower('view_hidden_genetics'))
                @foreach ($loci->alleles as $allele)
                    <div class="d-inline text-monospace {{ $allele->is_visible ? "" : "text-muted font-italic" }} px-1" data-toggle="tooltip" title="{{ $allele->summary }}">{!! $allele->displayName !!}</div>
                @endforeach
            @else
                @foreach ($loci->visibleAlleles as $allele)
                    <div class="d-inline text-monospace px-1" data-toggle="tooltip" title="{{ $allele->summary }}">{!! $allele->displayName !!}</div>
                @endforeach
            @endif
        @else
            <strong>Type</strong>: {{ ucfirst($loci->type) }}<br>
            <strong>Range</strong>: 0-{{ $loci->length }}
        @endif
        <div class="world-entry-text mt-2">
            {!! $loci->parsed_description !!}
        </div>
    </div>
</div>
