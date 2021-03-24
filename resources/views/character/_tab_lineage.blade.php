@include('character.lineage._tab_lineage', ['lineage' => $character->lineage])

@if(Auth::check() && Auth::user()->hasPower('manage_characters'))
    <div class="mt-3 text-right">
        @if($character->lineage)
            <span class="badge badge-primary">Lineage #{{ $character->lineage->id }}</span>
        @else
            <span class="badge badge-warning">No Lineage</span>
        @endif
        <a href="#" class="ml-2 btn btn-outline-info btn-sm edit-lineage" data-{{ $character->is_myo_slot ? 'id' : 'slug' }}="{{ $character->is_myo_slot ? $character->id : $character->slug }}"><i class="fas fa-cog"></i> Edit</a>
        @if($character->lineage)
            <a href="#" class="ml-1 btn btn-outline-danger btn-sm delete-lineage-button">Delete</a>
        @endif
    </div>
@endif

@if($character->lineage)
    <script>
        $(document).ready(function() {
            $('.delete-lineage-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/masterlist/lineages/delete/'.$character->lineage->id) }}", 'Delete Lineage');
            });
        });
    </script>
@endif