@extends('admin.layout')

@section('admin-title') {{ $category->id ? 'Edit' : 'Create' }} Gene Group @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Genetics' => 'admin/genetics/genes', ($category->id ? 'Edit' : 'Create').' Gene Group' => $category->id ? 'admin/data/genetics/edit/'.$category->id : 'admin/data/genetics/create']) !!}

<h1>{{ $category->id ? 'Edit' : 'Create' }} Gene Group
    @if($category->id)
        <a href="#" class="btn btn-danger float-right delete-category-button">Delete Gene Group</a>
    @endif
</h1>
@if(!$category->id)
    <p class="alert alert-info">Alleles can only be created after the gene is made.</p>
@endif

{!! Form::open(['url' => $category->id ? 'admin/genetics/edit/'.$category->id : 'admin/genetics/create']) !!}

<h3>Basic Information</h3>

<div class="row mb-3">
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('name', "Name") !!}
            {!! Form::text('name', $category->name, ['class' => 'form-control', 'maxlength' => 20]) !!}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('type', 'Type') . add_help("Standard are normal genes (eg. Aa). Gradient is for genes like rufus factors, with a large array of  binary on/off genes (eg. +-+--+++-+), and numbers are for straight up numbers (eg. 50, 12, 251). Type cannot be changed after creation.") !!}
            {!! Form::select('type', ["gene" => "Standard", "gradient" => "Gradient", "numeric" => "Numeric"], $category->type, ['class' => 'form-control', 'disabled' => $category->id != null]) !!}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('length', 'Length') . add_help("For standard genes, this should be set to 2 (one for each allele in the pair). For gradients, it should be a multiple of 2 (one for the maternal set and one for the paternal set) and cannot be larger than 64. For numbers, it can be anything between 0 and 255.") !!}
            {!! Form::number('length', $category->id ? $category->length : 2, ['class' => 'form-control', 'min' => 0, 'max' => 255]) !!}
        </div>
    </div>
    <div class="col-6">
        <div class="form-group">
            {!! Form::label('default', 'Default Inheritance') . add_help("Used to determine what happens when one parent has this gene and the other doesn't. Can only be set after creation.") !!}
            {!! Form::select('default', $defaultOptions, $category->id ? $category->default : 0, ['class' => 'form-control', 'disabled' => $category->id == null]) !!}
        </div>
    </div>
    <div class="col-12">
        <div class="form-group">
            {!! Form::label('loci_group_id', 'Chromosome Group') . add_help("Chromomosomee") !!}
            {!! Form::select('loci_group_id', $chromosomeOptions, $category->id ? $category->loci_group_id : 0, ['class' => 'form-control', 'disabled' => $category->id == null]) !!}
        </div>
    </div>
</div>

<div class="mb-3">
    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $category->description, ['class' => 'form-control wysiwyg']) !!}
    </div>
</div>

<div class="mb-3">
    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $category->id ? $category->is_visible : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Show In Encyclopedia', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is off, the genes will still appear in revealed genomes, but this gene group will not appear in the encyclopedia.') !!}
    </div>
</div>

@if($category->id && $category->type == 'gene')
    <h3>Create Alleles</h3>
    <p>Add new alleles to this gene group here. The checkbox determines dominance. The first text area determines the identifier and the second is an optional modifier. Use the textbox next to the add allele button to set all the identifiers at once.</p>
    <div id="allele-creation"></div>
    <div class="form-inline mb-3">
        <div class="form-group mr-3 mb-3">
            <a href="#" class="add-allele-button btn btn-primary"><i class="fas fa-plus"></i> New Allele</a>
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::text('default-allele', null, ['class' => 'allele-letter-override form-control', 'maxlength' => 5]) !!}
        </div>
    </div>

    <h3>Edit Alleles</h3>
    @if(count($category->alleles) == 0)
        <p>This gene group doesn't have any alleles!</p>
    @else
        <p>
            Edit existing alleles and sort them by dominance. No matter what sort order you save these as, genes marked as dominant will always appear before ones unmarked as dominant (recessive) ones.
            {!! Form::hidden('allele_sort', '', ['id' => 'sortableOrder']) !!}
        </p>
        <div class="card mb-3">
            <ul id="sortable" class="sortable list-group list-group-flush">
                @foreach($category->alleles as $allele)
                    <li class="sort-item list-group-item" data-id="{{ $allele->id }}">
                        @include('admin.genetics._allele_entry', ['allele' => $allele])
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="form-inline float-left">
            <div class="form-group mr-3 mb-3">
                <a href="#" class="delete-allele-button btn btn-danger"><i class="fas fa-minus mr-2"></i> Delete Allele</a>
            </div>
            <div class="form-group mr-3 mb-3">
                {!! Form::text('override-allele', null, ['class' => 'allele-letter-override form-control', 'maxlength' => 5]) !!}
            </div>
        </div>
    @endif
@elseif($category->id)
    <h3>Gene Details</h3>
    <div class="card mb-4">
        @if ($category->type == "gradient")
            <div class="card-body">
                <p>Gradient of length {{ $category->length }}, meaning it can be any whole number between 0 and {{ $category->length }}.</p>
                @php
                    $val = 0; $eg = "";
                    for ($i = 0; $i < $category->length; $i++)
                    {
                        $rand = mt_rand(0, 1);
                        $val += $rand;
                        $eg .= $rand == 1 ? "+" : "-";
                    }
                @endphp
                <strong>Example</strong>: <code>{{ $eg }}</code> ({{ $val }})
            </div>
        @elseif ($category->type == "numeric")
            <div class="card-body">
                <p>Number of length {{ $category->length }}, meaning it can be any whole number between 0 and {{ $category->length }}.</p>
                <strong>Example</strong>: {{ mt_rand(0, $category->length) }}
            </div>
        @endif
    </div>
@endif

<div class="text-right">
    {!! Form::submit($category->id ? 'Save Changes' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

{{-- Form Template for Allele creation. --}}
@if ($category->id && $category->type == 'gene')
    <div class="allele-creation-row hide mb-3">
        @include('admin.genetics._allele_entry', ['allele' => null])
    </div>
@endif

@if($category->id)
    <h3 class="mt-3">Preview</h3>
    <div class="card mb-3">
        <div class="card-body">
            @include('world._gene_entry', [ 'loci' => $category ])
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
        loadModal("{{ url('admin/genetics/delete') }}/{{ $category->id }}", 'Delete Category');
    });

    @if($category->id && $category->type == 'gene')
        $alleleCloneTarget = $('.allele-creation-row');
        addAlleleListeners($('#sortable'));
        $('.add-allele-button').on('click', function(e) {
            e.preventDefault();
            $clone = $alleleCloneTarget.clone();
            $clone.removeClass('.allele-creation-row hide');
            addAlleleListeners($clone);
            $clone.find('.form-check-input').on('click', function(e) {
                updateAllelePreview($(this).parent().parent().parent());
            });
            $clone.find('.form-control').on('change', function(e) {
                updateAllelePreview($(this).parent().parent());
            });
            $('#allele-creation').append($clone);
        });
        $('.delete-allele-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('admin/genetics/delete-allele/'.$category->id) }}", 'Delete Allele');
        });
        function addAlleleListeners($clone)
        {
            $button = $clone.find('.delete-allele-row');
            $button.on('click', function(e) {
                $(this).parent().parent().parent().remove();
            });

            $inputs = $clone.find('input');
            $inputs.on('change', function(e) {
                updateAllelePreview($(this).parent().parent());
            });

            $checks = $clone.find('.hidden-check-toggle');
            $checks.on('click', function(e) {
                e.preventDefault();
                // this=button, parent=input-group-prepend
                $hidden = $(this).parent().find('input[type=hidden]');

                // If the hidden value is 1 (True aka Dominant/Visible, set to False aka Recessive/Hidden)
                $newVal = $hidden.val() == 1 ? 0 : 1;
                $hidden.val($newVal);

                // Update appearances.
                $(this).removeClass("btn-primary btn-dark");
                $(this).addClass($newVal == 1 ? "btn-dark" : "btn-primary");
                $(this).text($(this).data($newVal == 1 ? "true-label" : "false-label"));
                $hidden.trigger("change");
            });
        }
        function updateAllelePreview($clone)
        {
            $preview = $clone.find('.preview');
            $isDominant = $clone.find('input[type=hidden]').val() == 1;

            $gene = $clone.find('.allele-letter-id').val();
            $mod = $clone.find('.allele-modifier').val();

            $preview.children().remove();
            $preview.text($isDominant ? $gene : $gene.toLowerCase());
            $preview.append($("<sup></sup>").text($isDominant ? $mod : $mod.toLowerCase()));
        }
        $(".allele-letter-override").change(function(e) {
            $alleles = $(document).find(".allele-letter-id");
            $alleles.val($(this).val());
            $alleles.trigger("change");
        });

        // Sortable
        $('.handle').on('click', function(e) {
            e.preventDefault();
        });
        $('#sortable').sortable({
            items: '.sort-item',
            handle: ".handle",
            placeholder: "sortable-placeholder",
            stop: function( event, ui ) {
                $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
            },
            create: function() {
                $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
            }
        });
        $('#sortable').disableSelection();
    @endif
});
</script>
@endsection
