<?php $flag = true; ?>
@if($lineage)
    {{-- Siblings & Niblings --}}
    <?php $siblings = $lineage->getSiblings(); ?>
    @if($siblings != null && $siblings->count() > 0)
        {{-- Siblings --}}
        @include('character.lineage._section',
        [
            'lineage'   => $lineage,
            'parent'    => false,
            'type'      => "siblings",
            'relatives' => $siblings->take(4)->get(),
        ])

        <?php $niblings = $lineage->getNiblings(); ?>
        @if($niblings != null && $niblings->count() > 0)
            {{-- Niblings --}}
            @include('character.lineage._section',
            [
                'lineage'   => $lineage,
                'parent'    => false,
                'type'      => "niblings",
                'relatives' => $niblings->take(4)->get(),
            ])
        @endif

        <?php $flag = false; ?>
    @endif

    {{-- Aunts, Uncles, Cousins --}}
    <?php $auncles = $lineage->getAuntsUncles(); ?>
    @if($auncles != null && $auncles->count() > 0)
        {{-- Aunts/Uncles --}}
        @include('character.lineage._section',
        [
            'lineage'   => $lineage,
            'parent'    => false,
            'type'      => "aunts-uncles",
            'relatives' => $auncles->take(4)->get(),
        ])

        <?php $cousins = $lineage->getCousins(); ?>
        @if($cousins != null && $cousins->count() > 0)
            {{-- Cousins --}}
            @include('character.lineage._section',
            [
                'lineage'   => $lineage,
                'parent'    => false,
                'type'      => "cousins",
                'relatives' => $cousins->take(4)->get(),
            ])
        @endif

        <?php $flag = false; ?>
    @endif
@endif

@if($flag)
    <p>No relatives known.</p>
@endif