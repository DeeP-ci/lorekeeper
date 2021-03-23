@extends('admin.layout')

@section('admin-title') Lineages @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Lineages' => 'admin/masterlist/lineages', ($lineage->id ? 'Edit' : 'Create').' Lineage' => $lineage->id ? 'admin/masterlist/lineages/edit/'.$lineage->id : 'admin/masterlist/lineages/create']) !!}

<h1>{{ $lineage->id ? 'Edit' : 'Create' }} Lineage
    @if($lineage->id)
        <a href="#" class="btn btn-danger float-right delete-lineage-button">Delete Lineage</a>
    @endif
</h1>

{{-- Form needs to be opened and closed OUTSIDE of the form data inclusion. --}}
{!! Form::open(['url' => 'admin/masterlist/lineages/'. ($lineage->id ? 'edit/'.$lineage->id : 'create') ]) !!}

    @include('admin.masterlist._create_edit_lineage', [
        "mode" => $lineage->id ? 'acp-edit' : 'acp-create',
        "isMyo" => $lineage->id ? ($lineage->character ? $lineage->character->is_myo_slot : false) : true,
        "lineage" => $lineage,
        "ownerOptions" => $ownerOptions,
        "parentOptions" => $parentOptions,
        "childOptions" => $childOptions,
        "rogueOptions" => $rogueOptions,
    ])

<div class="text-right">
    {!! Form::submit(($lineage->id ? 'Edit' : 'Create').' Lineage', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@endsection

@section('scripts')
@parent
@include('js._lineage_js')
@if($lineage->id)
    <script>
        $(document).ready(function() {
            $('.delete-lineage-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/masterlist/lineages/delete/'.$lineage->id) }}", 'Delete Lineage');
            });
        });
    </script>
@endif
@endsection
