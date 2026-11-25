@extends('admin.layout')

@section('admin-title') Sort Chromosomes @endsection

@section('admin-content')
{!! breadcrumbs([
    'Admin Panel' => 'admin',
    'Genetics' => 'admin/genetics/chromosomes',
    'Sort Chromosomes' => 'admin/data/genetics/chromosome/sort'
]) !!}

<h1>
    Chromosomes
    <div class="float-right">
        <a class="btn btn-primary" href="{{ url('admin/genetics/chromosome/create') }}"><i class="fas fa-plus mr-1"></i> New</a>
        <a class="btn btn-primary ml-1" href="{{ url('admin/genetics/chromosomes') }}"><i class="fas fa-arrow-left mr-1"></i> List</a>
        <a class="btn btn-secondary ml-1" href="{{ url('admin/genetics/genes') }}"><i class="fas fa-folder-open mr-1"></i> Locis</a>
    </div>
</h1>

<p>
    This is a list of chromosomes, which can group loci together for inheritance purposes.
</p>

@if(!count($chromosomes))
    <p>No gene groups found.</p>
@else
    <div class="card mb-3">
        <ul  id="sortable" class="sortable list-group list-group-flush">
            @foreach($chromosomes as $chromosome)
                <li class="sort-item list-group-item" data-id="{{ $chromosome->id }}">
                    <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                    {!! $chromosome->name !!}
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mb-4 text-right">
        {!! Form::open(['url' => 'admin/genetics/chromosome/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
    $( document ).ready(function() {
        $('.handle').on('click', function(e) {
            e.preventDefault();
        });
        $('#sortable').sortable({
            items: '.sort-item',
            handle: ".handle",
            placeholder: "sortable-placeholder",
            stop: function( event, ui ) {
                $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
            },
            create: function() {
                $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
            }
        });
        $('#sortable').disableSelection();
    });
</script>
@endsection
