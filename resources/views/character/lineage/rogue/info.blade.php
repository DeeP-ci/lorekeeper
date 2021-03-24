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

    <p class="text-center text-muted mb-4">No data available.</p>

    <div class="card character-bio">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <a class="nav-link active" id="lineageTab" data-toggle="tab" href="#lineage" role="tab">Lineage</a>
                </li>
            </ul>
        </div>
        <div class="card-body tab-content">
            <div class="tab-pane fade show active" id="lineage">
                @include('character.lineage._tab_lineage', ['lineage' => $lineage])

                @if(Auth::check() && Auth::user()->hasPower('manage_characters'))
                    <div class="mt-3 text-right">
                        <span class="badge badge-primary">Lineage #{{ $lineage->id }}</span>
                        <a href="#" class="ml-2 btn btn-outline-info btn-sm edit-lineage" data-id="{{ $lineage->id }}"><i class="fas fa-cog"></i> Edit</a>
                        <a href="#" class="ml-1 btn btn-outline-danger btn-sm delete-lineage-button">Delete</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $('.edit-lineage').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('admin/rogue/') }}/"+$(this).data('id')+"/lineage", 'Edit Rogue Lineage');
        });
        $('.delete-lineage-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('admin/masterlist/lineages/delete/'.$lineage->id) }}", 'Delete Lineage');
        });
    </script>
@endsection
