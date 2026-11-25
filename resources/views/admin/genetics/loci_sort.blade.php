@extends('admin.layout')

@section('admin-title') Sort Gene Groups @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Genetics' => 'admin/genetics/genes', 'Sort Groups' => 'admin/data/genetics/sort']) !!}

<h1>
    Gene Groups
    <div class="float-right">
        <a class="btn btn-primary" href="{{ url('admin/genetics/create') }}"><i class="fas fa-plus mr-1"></i> New</a>
        <a class="btn btn-primary ml-1" href="{{ url('admin/genetics/genes') }}"><i class="fas fa-arrow-left mr-1"></i> List</a>
        <a class="btn btn-secondary ml-1" href="{{ url('admin/genetics/chromosomes') }}"><i class="fas fa-folder mr-1"></i> Chromosomes</a>
    </div>
</h1>

<p>
    This is a list of gene groups (loci) that will be used to create and sort assignable genetics.
    The sort order reflects the order in which genes will display in a character's genome, or on world pages.
</p>

@if(!count($categories))
    <p>No gene groups found.</p>
@else
    <div class="card mb-3">
        <ul  id="sortable" class="sortable list-group list-group-flush">
            @foreach($categories as $category)
                <li class="sort-item list-group-item" data-id="{{ $category->id }}">
                    <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                    {!! $category->name !!} ({{ ucfirst($category->type) }})
                </li>
            @endforeach
        </ul>
    </div>

    <div class="mb-4 text-right">
        {!! Form::open(['url' => 'admin/genetics/sort']) !!}
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
