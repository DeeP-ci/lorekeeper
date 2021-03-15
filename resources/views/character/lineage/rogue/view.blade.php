@extends('character.lineage.rogue.layout')

@section('profile-title') {{ $lineage->name }}'s {{ $pageTitle }} @endsection

@section('meta-img') {{ $lineage->thumbnail }} @endsection

@section('profile-content')
    {!! breadcrumbs(['Rogue Entry' => '', $lineage->name => $lineage->url, $pageTitle => $lineage->url.'/' . $lineageType]) !!}

    <div class="character-masterlist-categories">
        Rogue Entry
    </div> 
    <h1 class="mb-0">
        <a href="{!! $lineage->url !!}">{!! $lineage->name !!}</a>
    </h1>
    <div class="mb-3"> 
        Unownable
    </div>

    @include('character.lineage._lineage', [
        'pageTitle' => $pageTitle, 
        'lineageType' => $lineageType, 
        'lineage' => $lineage,
    ])
@endsection

@section('scripts')
    @parent
@endsection
