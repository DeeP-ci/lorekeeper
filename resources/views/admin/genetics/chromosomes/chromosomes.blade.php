@extends('admin.layout')

@section('admin-title') Chromosomes @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Genetics' => 'admin/genetics']) !!}

<h1>
    Chromosomes
    <div class="float-right">
        <a class="btn btn-primary" href="{{ url('admin/genetics/chromosome/create') }}"><i class="fas fa-plus mr-1"></i> New</a>
        <a class="btn btn-primary ml-1" href="{{ url('admin/genetics/chromosome/sort') }}"><i class="fas fa-bars mr-1"></i> Sort</a>
        <a class="btn btn-secondary ml-1" href="{{ url('admin/genetics/genes') }}"><i class="fas fa-folder-open mr-1"></i> Locis</a>
    </div>
</h1>

<p>
    This is a list of chromosomes, which can group loci together for inheritance purposes.
</p>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($chromosomes))
    <p class="text-center">No chromosome groups found.</p>
@else
    {!! $chromosomes->render() !!}
    <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
            <div class="col-12 font-weight-bold">Name</div>
        </div>
        @foreach($chromosomes as $chromosome)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-6">
                    {{ $chromosome->name }}
                    @if(!$chromosome->is_visible) <i class="fas fa-eye-slash"></i> @endif
                </div>
                <div class="col-6">
                    <a href="{{ url('admin/genetics/chromosome/edit/'.$chromosome->id) }}" class="btn btn-primary py-0 px-2 float-right">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
    {!! $chromosomes->render() !!}
@endif

@endsection

@section('scripts')
@parent
@endsection
