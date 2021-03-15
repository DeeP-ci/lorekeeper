@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title') {{ $character->fullName }}'s {{ $pageTitle }} @endsection

@section('meta-img') {{ $character->image->thumbnailUrl }} @endsection

@section('profile-content')
    {!! breadcrumbs([($character->is_myo_slot ? 'MYO Slot Masterlist' : ($character->category->masterlist_sub_id ? $character->category->sublist->name.' Masterlist' : 'Character Masterlist')) => ($character->is_myo_slot ? 'myos' : ($character->category->masterlist_sub_id ? 'sublist/'.$character->category->sublist->key : 'masterlist')), $character->fullName => $character->url, $pageTitle => $character->url.'/' . $lineageType]) !!}

    @include('character._header', ['character' => $character])

    @include('character.lineage._lineage', [
        'pageTitle' => $pageTitle, 
        'lineageType' => $lineageType, 
        'lineage' => $character->lineage,
    ])
@endsection

@section('scripts')
    @parent
@endsection
