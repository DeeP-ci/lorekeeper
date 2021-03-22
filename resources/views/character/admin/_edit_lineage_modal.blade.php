{!! Form::open(['url' => 'admin/'. ($isRogue ? 'rogue/'.$lineage->id : ($isMyo ? 'myo/'.$character->id : 'character/'.$character->slug)) . '/lineage']) !!}

    @include('admin.masterlist._create_edit_lineage', [
        'mode' => 'edit-modal',
        'lineage' => $lineage,
        'isMyo' => isset($isMyo) ? $isMyo : false,
        "ownerOptions" => $ownerOptions,
        "parentOptions" => $parentOptions,
        "childOptions" => $childOptions,
        "rogueOptions" => $rogueOptions,
    ])

    <div class="text-right">
        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}

@include('js._lineage_js')