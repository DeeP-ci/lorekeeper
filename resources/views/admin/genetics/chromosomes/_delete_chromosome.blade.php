@if($chromosome)
    {!! Form::open(['url' => 'admin/genetics/chromosome/delete/'.$chromosome->id]) !!}

    <p>You are about to delete the chromosome <strong>{{ $chromosome->name }}</strong>. This is not reversible. All loci in this chromosome will lose their links to one another, but will otherwise be unaffected.</p>
    <p>Are you sure you want to delete <strong>{{ $chromosome->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Chromosome', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    That chromosome does not exist.
@endif
