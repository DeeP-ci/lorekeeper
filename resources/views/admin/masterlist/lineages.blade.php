@extends('admin.layout')

@section('admin-title') Lineages @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Lineages' => 'admin/masterlist/lineages']) !!}

<div class="float-right">
    <a class="btn btn-primary" href="{{ url('admin/masterlist/lineages/create') }}"><i class="fas fa-plus"></i> Create New Lineage</a>
</div>
<h1>Lineages</h1>

<p>A masterlist of all lineages in the game.</p>

<div>
    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::select('filter', array('All Lineages', 'Character Lineages', 'Characterless Lineages'), Request::get('filter'), ['class' => 'form-control']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    {!! Form::close() !!}
</div>

@if(!count($lineages))
    <p>No lineages found.</p>
@else
    {!! $lineages->render() !!}
      <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
          <div class="col-6 font-weight-bold">Link</div>
          <div class="col-4 font-weight-bold">Type</div>
          <div class="col-2 text-center font-weight-bold">Actions</div>
        </div>
        @foreach($lineages as $lineage)
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
          <div class="col-6">{!! $lineage->display_name !!}</div>
          <div class="col-4">{!! $lineage->character ? ($lineage->character->is_myo_slot ? "MYO " : "")."Character" : "Rogue" !!}</div>
          <div class="col-2 text-center"><a href="{{ url('admin/masterlist/lineages/edit/'.$lineage->id) }}" class="btn btn-primary py-0 px-1 w-100">Edit</a></div>
        </div>
        @endforeach
      </div>
    {!! $lineages->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $lineages->total() }} result{{ $lineages->total() == 1 ? '' : 's' }} found.</div>
@endif

@endsection

@section('scripts')
@parent
@endsection
