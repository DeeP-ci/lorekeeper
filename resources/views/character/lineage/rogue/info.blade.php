@extends('character.lineage.rogue.layout')

@section('profile-title') {{ $lineage->name }} @endsection

@section('meta-img') {{ $lineage->thumbnail }} @endsection

@section('profile-content')
    {!! breadcrumbs(['Rogue Entry' => '', $lineage->name => $lineage->url]) !!}

    <div class="character-masterlist-categories">
        Rogue Entry
    </div> 
    <h1 class="mb-0">
        <a href="{!! $lineage->url !!}">{!! $lineage->name !!}</a>
    </h1>
    <div class="mb-3"> 
        Unownable
    </div>

    <div class="text-center mw-100">
        <img src="{{ $lineage->thumbnail }}" alt="Unknown">
    </div>

    <p class="text-center text-muted">No data available.</p>
@endsection

@section('scripts')
    @parent
@endsection
