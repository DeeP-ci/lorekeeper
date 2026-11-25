@extends('admin.layout')

@section('admin-title') {{ $chromosome->id ? "Edit" : "Create" }} Chromosome @endsection

@section('admin-content')
{!! breadcrumbs([
    'Admin Panel' => 'admin',
    'Genetics' => 'admin/genetics/chromosomes',
    ($chromosome->id ? 'Edit' : 'Create').' Chromosome' => $chromosome->id ? 'admin/data/genetics/chromosome/edit/'.$chromosome->id : 'admin/data/genetics/chromosome/create'
]) !!}

<h1>
    {{ $chromosome->id ? 'Edit' : 'Create' }} Chromosome
    @if($chromosome->id)
        <a href="#" class="btn btn-danger float-right delete-category-button">Delete Chromosome</a>
    @endif
</h1>

<p class="alert alert-info">A chromosome groups loci that should have linked inheritance together. It shouldn't be used for loci that want to inherit independently of one another.</p>

{!! Form::open(['url' => $chromosome->id ? 'admin/genetics/chromosome/edit/'.$chromosome->id : 'admin/genetics/chromosome/create']) !!}

<h3>Basic Information</h3>

<div class="row mb-3">
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('name', "Name") !!}
            {!! Form::text('name', $chromosome->name, ['class' => 'form-control', 'maxlength' => 100]) !!}
        </div>
    </div>
    <div class="col-6">
        {!! Form::label('type', "Type") !!}
        <p class="alert alert-warning py-2 mb-0">Locked inheritance, no recombination allowed.</p>
    </div>
</div>

<div class="mb-3">
    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $chromosome->description, ['class' => 'form-control wysiwyg']) !!}
    </div>
</div>

<div class="mb-3">
    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $chromosome->id ? $chromosome->is_visible : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Show In Encyclopedia', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Allows this chromosome to be seen in the encyclopedia by regular users. Will be hidden even in the description of public gene groups if this is toggled off.') !!}
    </div>
</div>

<div class="text-right">
    {!! Form::submit($chromosome->id ? 'Save Changes' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

@if($chromosome->id)
    <h3>Locis</h3>
    <p>
        List of locis (gene groups) that have their inheritance controlled by this chromosome.
    </p>
    @if(count($chromosome->locis))
        <dl class="row mb-0">
            @foreach($chromosome->locis as $loci)
                <dt class="col-sm-4 text-truncate">
                    <span class="d-inline-block text-monospace badge badge-light mr-1" style="width:6em;">
                        @switch($loci->type)
                            @case('gene')
                                Standard
                                @break
                            @case('gradient')
                                Gradient
                                @break
                            @case('numeric')
                                Numeric
                                @break
                            @default
                                Custom
                        @endswitch
                    </span>
                    <a href="{{ url('admin/genetics/edit/'.$loci->id) }}" class="font-weight-bold mr-3" data-toggle="tooltip" title="Edit {{ $loci->name }}">
                        {{ $loci->name }}
                    </a>
                </dt>
                <dd class="col-sm-8">
                    @if ($loci->type != 'gene')
                        Not allelic.
                    @elseif (!count($loci->alleles))
                        Has no alleles.
                    @else
                        @foreach ($loci->alleles as $allele)
                            <div class="d-inline text-monospace {{ $allele->is_visible ? "" : "text-muted font-italic" }} px-1" data-toggle="tooltip" title="{{ $allele->summary }}">{!! $allele->displayName !!}</div>
                        @endforeach
                    @endif
                </dd>
            @endforeach
        </dl>
    @else
        <p>This chromosome doesn't have any loci assigned!</p>
    @endif
@endif

@if($chromosome->id)
    <h3 class="mt-3">Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._chromosome_entry', [ 'chromosome' => $chromosome ])
        </div>
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-category-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/genetics/chromosome/delete') }}/{{ $chromosome->id }}", 'Delete Chromosome');
    });
});
</script>
@endsection
