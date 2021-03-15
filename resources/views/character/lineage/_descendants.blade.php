@if($lineage)
    {{-- Parents, Grandparents and Great-Grandparents --}}
    <?php $children = $lineage->getChildren(); ?>
    @if($children != null && $children->count() > 0)
        {{-- Children --}}
        @include('character.lineage._section',
        [
            'lineage'   => $lineage,
            'parent'    => false,
            'type'      => "children",
            'relatives' => $children->take(4),
        ])

        <?php $grands = $lineage->getGrandchildren(); ?>
        @if($grands != null && $grands->count() > 0)
            {{-- Grandchildren --}}
            @include('character.lineage._section',
            [
                'lineage'   => $lineage,
                'parent'    => false,
                'type'      => "grandchildren",
                'relatives' => $grands->take(4)->get(),
            ])

            <?php $greats = $lineage->getGreatGrandchildren(); ?>
            @if($greats != null && $greats->count() > 0)
                {{-- Great-Grandchildren --}}
                @include('character.lineage._section',
                [
                    'lineage'   => $lineage,
                    'parent'    => false,
                    'type'      => "great-grandchildren",
                    'relatives' => $greats->take(4)->get(),
                ])
            @endif
        @endif
    @else
        <p>No descendants known.</p>
    @endif
@endif