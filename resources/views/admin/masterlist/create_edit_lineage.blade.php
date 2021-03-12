@extends('admin.layout')

@section('admin-title') Loot Tables @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Lineages' => 'admin/masterlist/lineages', ($lineage->id ? 'Edit' : 'Create').' Loot Table' => $lineage->id ? 'admin/data/loot-tables/edit/'.$lineage->id : 'admin/data/loot-tables/create']) !!}

<h1>{{ $lineage->id ? 'Edit' : 'Create' }} Lineage
    @if($lineage->id)
        <a href="#" class="btn btn-danger float-right delete-lineage-button">Delete Lineage</a>
    @endif
</h1>

{!! Form::open(['url' => 'admin/masterlist/edit']) !!}

<p>TBA blah blah</p>

<h3>Parents</h3>

<div class="form-group">
    <div id="parentList">
        @if($lineage->id)
            @foreach($lineage->parents as $parent)
                <div class="row">
                    <div class="lineage-type mb-1 col-sm-5">
                        {!! Form::select('parent_type[]', ['Character' => "Character", 'Rogue' => "Characterless Lineage", 'New' => "New Characterless"], (!$parent->parent->character) ? "Rogue" : "Character", ['class' => 'lineage-type-select form-control mr-2', 'placeholder' => 'Select Parent Type']) !!}
                    </div>
                    <div class="lineage-data-select mb-2 col-10 col-sm-6">
                        @if(!$parent->parent->character)
                            {!! Form::select('parent_data[]', $rogueOptions, $parent->parent->id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Lineage']) !!}
                        @else
                            {!! Form::select('parent_data[]', $characterOptions, $parent->parent->character_id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
                        @endif
                    </div>
                    <div class="mb-2 col-2 col-sm-1 text-right">
                        <a href="#" class="remove-parent btn btn-danger mb-2">×</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div><a href="#" class="btn btn-primary" id="add-parent">Add Parent</a></div>
</div>
<div id="lineageHelperData" class="hide">
    <div class="row parent-row">
        <div class="lineage-type mb-1 col-sm-5">
            {!! Form::select('parent_type[]', ['Character' => "Character", 'Rogue' => "Characterless Lineage", 'New' => "New Characterless"], "Character", ['class' => 'lineage-type-select form-control mr-2', 'placeholder' => 'Select Parent Type']) !!}
        </div>
        <div class="lineage-data-select mb-2 col-10 col-sm-6">
        {!! Form::select('parent_data[]', $characterOptions, null, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
        </div>
        <div class="mb-2 col-2 col-sm-1 text-right">
            <a href="#" class="remove-parent btn btn-danger mb-2">×</a>
        </div>
    </div>
    {!! Form::select('parent_data[]', $characterOptions, null, ['class' => 'character-select lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
    {!! Form::select('parent_data[]', $rogueOptions, null, ['class' => 'rogue-select lineage-data form-control mr-2', 'placeholder' => 'Select Lineage']) !!}
    {!! Form::text('parent_data[]', null, ['class' => 'rogue-new form-control lineage-data mr-2', 'placeholder' => 'Rogue\'s Name']) !!}
</div>

<h3>Children</h3>

<div class="form-group">
    <div id="childList">
        @if($lineage->id)
            @foreach($lineage->children as $child)
                <div class="row">
                    <div class="lineage-type mb-1 col-sm-5">
                        {!! Form::select('child_type[]', ['Character' => "Character", 'Rogue' => "Characterless Lineage", 'New' => "New Characterless"], (!$child->child->character) ? "Rogue" : "Character", ['class' => 'lineage-type-select form-control mr-2', 'placeholder' => 'Select Parent Type']) !!}
                    </div>
                    <div class="lineage-data-select mb-2 col-10 col-sm-6">
                        @if(!$child->child->character)
                            {!! Form::select('child_data[]', $rogueOptions, $child->child->id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Lineage']) !!}
                        @else
                            {!! Form::select('child_data[]', $characterOptions, $child->child->character_id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
                        @endif
                    </div>
                    <div class="mb-2 col-2 col-sm-1 text-right">
                        <a href="#" class="remove-parent btn btn-danger mb-2">×</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div><a href="#" class="btn btn-primary" id="add-child">Add Child</a></div>
</div>

<div class="text-right">
    {!! Form::submit(($lineage->id ? 'Edit' : 'Create').' Lineage', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@endsection

@section('scripts')
@parent

<script>

    $(document).ready(function() {
        $('#parentList').find('.lineage-data').selectize();
    });

    // Lineage /////////////////////////////////////////////////////////////////////////////////////

    var $characterSelect = $('#lineageHelperData').find('.character-select');
    var $rogueSelect = $('#lineageHelperData').find('.rogue-select');
    var $newRogue = $('#lineageHelperData').find('.rogue-new');

    $('#add-parent').on('click', function(e) {
        e.preventDefault();
        addParentRow();
    });
    $('.remove-parent').on('click', function(e) {
        e.preventDefault();
        removeParentRow($(this));
    })
    function addParentRow() {
        var $clone = $('.parent-row').clone();
        $('#parentList').append($clone);
        $clone.removeClass('hide parent-row');
        $clone.addClass('d-flex');
        $clone.find('.remove-parent').on('click', function(e) {
            e.preventDefault();
            removeParentRow($(this));
        })
        $clone.find('.lineage-data').selectize();
        attachLineageTypeListener($clone.find('.lineage-type-select'));
    }
    function removeParentRow($trigger) {
        $trigger.parent().parent().remove();
    }
    function attachLineageTypeListener($node) {
        $node.on('change', function(e) {
            var val = $(this).val();
            var $cell = $(this).parent().parent().find('.lineage-data-select');

            var $clone = null; var flag = true;
            if(val == 'Character') $clone = $characterSelect.clone();
            else if (val == 'Rogue') $clone = $rogueSelect.clone();
            else if (val == 'New') $clone = $newRogue.clone();
            else flag = false;

            $cell.html('');
            $cell.append($clone);
            if (flag && val != "New") $clone.selectize();
        });
    }
</script>

@endsection
