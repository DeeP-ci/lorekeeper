{{-- This shows on the FULL list of relatives pages. --}}
@if(!$lineage)
    <h2>
        <a href="{{ $character->url . '/lineage/' . $lineageType }}">{{ $pageTitle }}</a>
    </h2>
    <p>This character has no {{ $pageTitle }}.</p>
@else
    <h2>
        <a href="{{ $lineage->url . '/lineage/' . $lineageType }}">{{ $pageTitle }}</a>
    </h2>
    @if ($relatives != null && $relatives->count() > 0)
        <div class="row">
            @foreach ($relatives as $relative)
                @include('character.lineage._thumb', ['lineage' => $parent ? $relative->parent : $relative->child])
            @endforeach
        </div>
    @else
        <p>This character has no {{ $pageTitle }}.</p>
    @endif
@endif