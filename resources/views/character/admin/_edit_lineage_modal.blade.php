{!! Form::open(['url' => $isMyo ? 'admin/myo/'.$character->id.'/lineage' : 'admin/character/'.$character->slug.'/lineage']) !!}

    @if(!$character->lineage)
        <div class="alert alert-info">
            This character does not have a lineage yet. A lineage (even an empty one) is required in order to be set as a parent or child.
        </div>
    @endif

    <h5>Parents</h5>
    <p>Here you can add and remove parents.</p>

    @foreach($character->lineage->parents as $parent)
        <div class="d-flex mb-2">
            {!! $parent->parent->display_name !!} 
            <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
        </div>
    @endforeach

    <div class="feature-row hide- mb-2">
        {!! Form::select('character_id[]', $character_options, null, ['class' => 'form-control mr-2 feature-select', 'placeholder' => 'Select Trait']) !!}
        {!! Form::text('character_data[]', null, ['class' => 'form-control mr-2', 'placeholder' => 'Extra Info (Optional)']) !!}
        <a href="#" class="remove-feature btn btn-danger mb-2">×</a>
    </div>

    <h5>Children</h5>
    <p>Here you can add and remove children.</p>

    {!! Form::select('rewardable_type[]', ['Character' => 'Character', 'ExistingRogue' => 'Existing Rogue', 'NewRogue' => 'New Rogue'], "Character", ['class' => 'form-control reward-type', 'placeholder' => 'Select Lineage Type']) !!}

    <div class="text-right">
        {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $( "#datepicker" ).datetimepicker({
            dateFormat: "yy-mm-dd",
            timeFormat: 'HH:mm:ss',
        });

        //$('[data-toggle=toggle]').bootstrapToggle();

        // Resell options /////////////////////////////////////////////////////////////////////////////

        var $resellable = $('#resellable');
        var $resellOptions = $('#resellOptions');

        var resellable = $resellable.is(':checked');

        updateOptions();

        $resellable.on('change', function(e) {
            resellable = $resellable.is(':checked');

            updateOptions();
        });

        function updateOptions() {
            if(resellable) $resellOptions.removeClass('hide');
            else $resellOptions.addClass('hide');
        }
    });
</script>