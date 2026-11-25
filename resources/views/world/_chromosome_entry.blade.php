<div class="row world-entry">
    <div class="col-12">
        <h3 class="mb-0">
            @if (!$chromosome->is_visible)
                <i class="fas fa-eye-slash"></i>
            @endif
            {!! $chromosome->display_name !!}
        </h3>
        <div class="world-entry-text mt-2">
            {!! $chromosome->parsed_description !!}
        </div>
    </div>
</div>
