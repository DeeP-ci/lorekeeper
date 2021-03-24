@if($lineage)
    {!! Form::open(['url' => 'admin/masterlist/lineages/delete/'.$lineage->id]) !!}

    <p>You are about to delete the lineage of {!! $lineage->display_name !!}. <strong>This is permanent and irreversible.</strong></p>
    <p>All children will no longer have this lineage as a parent. All parents will no longer have this lineage as a child.</p>
    @if($lineage->character) 
        <p>Don't do this if you want to maintain links between this lineage's parents and children!</p>
    @endif
    <p>Are you sure you want to delete <strong>{!! $lineage->character ? $lineage->display_name.'\'s Lineage' : $lineage->display_name !!}</strong>?</p>

    <div class="text-right">
        {!! Form::hidden('lineage_id', $lineage->id) !!}
        {!! Form::submit('Delete Lineage', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid lineage selected.
@endif