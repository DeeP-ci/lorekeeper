@extends('world.layout')

@section('title') Genetics @endsection

@section('content')
{!! breadcrumbs(['World' => 'world', 'Genetics' => 'world/genetics']) !!}
<h1>Genetics</h1>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select('variant', $options, Request::get('variant'), ['class' => 'form-control']) !!}
        </div>
        @if(count($chromosomeOptions)>1)
            <div class="form-group mr-3 mb-3">
                {!! Form::select('chromosome', $chromosomeOptions, Request::get('chromosome'), ['class' => 'form-control']) !!}
            </div>
        @endif
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if ($chromosome)
    <h3>Chromosome</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._chromosome_entry', [ 'chromosome' => $chromosome ])
        </div>
    </div>

    <h3>Genes</h3>
@endif

{!! $genetics->render() !!}
@foreach($genetics as $loci)
    <div class="card mb-3">
        <div class="card-body">
        @include('world._gene_entry', ['loci' => $loci])
        </div>
    </div>
@endforeach
@if(count($genetics) < 1)
    <p>No genes found.</p>
@endif
{!! $genetics->render() !!}

<div class="text-center mt-4 small text-muted">{{ $genetics->total() }} result{{ $genetics->total() == 1 ? '' : 's' }} found.</div>

@endsection
