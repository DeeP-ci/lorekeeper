@if($lineage)
    {{-- Parents, Grandparents and Great-Grandparents --}}
    <?php $parents = $lineage->getParents(); ?>
    @if($parents != null && $parents->count() > 0)
        {{-- Parents --}}
        @include('character.lineage._section',
        [
            'lineage'   => $lineage,
            'parent'    => true,
            'type'      => "parents",
            'relatives' => $parents->take(4),
        ])

        <?php $gps = $lineage->getGrandparents(); ?>
        @if($gps != null && $gps->count() > 0)
            {{-- Grandparents --}}
            @include('character.lineage._section',
            [
                'lineage'   => $lineage,
                'parent'    => true,
                'type'      => "grandparents",
                'relatives' => $gps->take(4)->get(),
            ])

            <?php $greats = $lineage->getGreatGrandparents(); ?>
            @if($greats != null && $greats->count() > 0)
                {{-- Great-Grandparents --}}
                @include('character.lineage._section',
                [
                    'lineage'   => $lineage,
                    'parent'    => true,
                    'type'      => "great-grandparents",
                    'relatives' => $greats->take(4)->get(),
                ])
            @endif
        @endif
    @else
        <p>No ancestors known.</p>
    @endif
@endif