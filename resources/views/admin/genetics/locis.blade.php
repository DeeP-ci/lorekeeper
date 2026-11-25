@extends('admin.layout')

@section('admin-title') Gene Groups @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Genetics' => 'admin/genetics']) !!}

<h1>
    Gene Groups
    <div class="float-right">
        <a class="btn btn-primary" href="{{ url('admin/genetics/create') }}"><i class="fas fa-plus mr-1"></i> New</a>
        <a class="btn btn-primary ml-1" href="{{ url('admin/genetics/sort') }}"><i class="fas fa-bars mr-1"></i> Sort</a>
        <a class="btn btn-secondary ml-1" href="{{ url('admin/genetics/chromosomes') }}"><i class="fas fa-folder mr-1"></i> Chromosomes</a>
    </div>
</h1>

<p>
    This is a list of gene groups (loci) that will be used to create and sort assignable genetics.
    The sort order reflects the order in which genes will display in a character's genome, or on world pages.
</p>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('type', ["Any Type", "Standard", "Gradient", "Numeric"], Request::get('type'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($locis))
    <p class="text-center">No gene groups found.</p>
@else
    {!! $locis->render() !!}
    <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-3 font-weight-bold">Name</div>
            <div class="col-4 font-weight-bold">Chromosome</div>
            <div class="col-5 font-weight-bold">Type</div>
        </div>
        @foreach($locis as $loci)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-3">
                    {{ $loci->name }}
                    @if(!$loci->is_visible) <i class="fas fa-eye-slash"></i> @endif
                </div>
                <div class="col-4">
                    @if ($loci->chromosome)
                        <a href="{{ url('admin/genetics/chromosome/edit/'.$loci->chromosome->id) }}" data-toggle="tooltip" title="Edit {{ $loci->chromosome->name }}">{{ $loci->chromosome->name }}</a>
                        @if(!$loci->chromosome->is_visible) <i class="fas fa-eye-slash"></i> @endif
                    @else
                        -
                    @endif
                </div>
                <div class="col-5">{{ ucfirst($loci->type) }} ({{ $loci->length }})
                    <a href="{{ url('admin/genetics/edit/'.$loci->id) }}" class="btn btn-primary py-0 px-2 float-right">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
    {!! $locis->render() !!}
@endif
@endsection

@section('scripts')
@parent
@endsection
