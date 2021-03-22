{{-- Parents and Parent Data, appears on ALL variants. --}}
{!! Form::label('parent', "Parents") . add_help('Here you can add or remove parents.') !!}
<div class="form-group">
    <div id="parentList">
        {{-- If there's a $lineage, check for existing parents. --}}
        @if(isset($lineage) && $lineage->id)
            @foreach($lineage->getParents() as $link)
                <div class="row">
                    <div class="lineage-type mb-1 col-sm-5">
                        {!! Form::select('parent_type[]', ['Character' => "Character", 'Rogue' => "Characterless Lineage", 'New' => "New Characterless"], (!$link->parent->character) ? "Rogue" : "Character", ['class' => 'lineage-type-select form-control mr-2', 'placeholder' => 'Select Parent Type']) !!}
                    </div>
                    <div class="lineage-data-select mb-2 col-10 col-sm-6">
                        @if(!$link->parent->character)
                            {!! Form::select('parent_data[]', $rogueOptions, $link->parent->id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Lineage']) !!}
                        @else
                            {!! Form::select('parent_data[]', $parentOptions, $link->parent->character_id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
                        @endif
                    </div>
                    <div class="mb-2 col-2 col-sm-1 text-right">
                        <a href="#" class="remove-parent btn btn-danger w-100 mb-2">×</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
    <div><a href="#" class="btn btn-primary" id="add-parent">Add Parent</a></div>
</div>

{{-- Child and Children Data, does NOT appear on MYOs. --}}
@if(!(isset($isMyo) && $isMyo == true))
    {!! Form::label('child', "Children") . add_help('Here you can add or remove children.') !!}
    <div class="form-group">
        <div id="childList">
            {{-- If there's a $lineage, check for existing children. --}}
            @if(isset($lineage) && $lineage->id)
                @foreach($lineage->getChildren() as $link)
                    <div class="row">
                        <div class="lineage-type mb-1 col-sm-5">
                            {!! Form::select('child_type[]', ['Character' => "Character", 'Rogue' => "Characterless Lineage", 'New' => "New Characterless"], (!$link->child->character) ? "Rogue" : "Character", ['class' => 'lineage-type-select form-control mr-2', 'placeholder' => 'Select Child Type']) !!}
                        </div>
                        <div class="lineage-data-select mb-2 col-10 col-sm-6">
                            @if(!$link->child->character)
                                {!! Form::select('child_data[]', $rogueOptions, $link->child->id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Lineage']) !!}
                            @else
                                {!! Form::select('child_data[]', $childOptions, $link->child->character_id, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
                            @endif
                        </div>
                        <div class="mb-2 col-2 col-sm-1 text-right">
                            <a href="#" class="remove-child btn btn-danger w-100 mb-2">×</a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div><a href="#" class="btn btn-primary" id="add-child">Add Child</a></div>
    </div>
@endif

{{-- Hidden helper fields. --}}
<div id="lineageHelperData" class="hide">
    <div class="row parent-row">
        <div class="lineage-type mb-1 col-sm-5">
            {!! Form::select('parent_type[]', ['Character' => "Character", 'Rogue' => "Characterless Lineage", 'New' => "New Characterless"], "Character", ['class' => 'lineage-type-select form-control mr-2', 'placeholder' => 'Select Parent Type']) !!}
        </div>
        <div class="lineage-data-select mb-2 col-10 col-sm-6">
        {!! Form::select('parent_data[]', $parentOptions, null, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
        </div>
        <div class="mb-2 col-2 col-sm-1 text-right">
            <a href="#" class="remove-parent btn btn-danger w-100 mb-2">×</a>
        </div>
    </div>
    {!! Form::select('parent_data[]', $parentOptions, null, ['class' => 'parent-data lineage-data character-select form-control mr-2', 'placeholder' => 'Select Character']) !!}
    {!! Form::select('parent_data[]', $rogueOptions,  null, ['class' => 'parent-data lineage-data rogue-select form-control mr-2', 'placeholder' => 'Select Lineage']) !!}
    {!! Form::text(  'parent_data[]', null,                 ['class' => 'parent-data lineage-data rogue-new form-control mr-2', 'placeholder' => 'Rogue\'s Name']) !!}
    {{-- if there is no $isMyo set, or if $isMyo == false, add child options --}}
    @if(!(isset($isMyo) && $isMyo == true))
        <div class="row child-row">
            <div class="lineage-type mb-1 col-sm-5">
                {!! Form::select('child_type[]', ['Character' => "Character", 'Rogue' => "Characterless Lineage", 'New' => "New Characterless"], "Character", ['class' => 'lineage-type-select form-control mr-2', 'placeholder' => 'Select Child Type']) !!}
            </div>
            <div class="lineage-data-select mb-2 col-10 col-sm-6">
            {!! Form::select('child_data[]', $childOptions, null, ['class' => 'lineage-data form-control mr-2', 'placeholder' => 'Select Character']) !!}
            </div>
            <div class="mb-2 col-2 col-sm-1 text-right">
                <a href="#" class="remove-child btn btn-danger w-100 mb-2">×</a>
            </div>
        </div>
        {!! Form::select('child_data[]', $childOptions, null, ['class' => 'child-data lineage-data character-select form-control mr-2', 'placeholder' => 'Select Character']) !!}
        {!! Form::select('child_data[]', $rogueOptions, null, ['class' => 'child-data lineage-data rogue-select form-control mr-2', 'placeholder' => 'Select Lineage']) !!}
        {!! Form::text(  'child_data[]', null,                ['class' => 'child-data lineage-data rogue-new form-control mr-2', 'placeholder' => 'Rogue\'s Name']) !!}
    @endif
</div>