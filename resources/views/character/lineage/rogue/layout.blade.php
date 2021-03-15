@extends('layouts.app')

@section('title') Rogue ::@yield('profile-title')@endsection

@section('sidebar')
    @include('character.lineage.rogue._sidebar', ['lineage' => $lineage])
@endsection

@section('content')
    @yield('profile-content')
@endsection

@section('scripts')
    @parent
@endsection