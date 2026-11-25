{!! Form::open(['url' => 'admin/'.($isMyo ? "myo" : "character").'/'. ($isMyo ? $character->id : $character->slug).'/genome/'.($genome->id ? $genome->id : 'create')]) !!}
    <div class="form-group">
        @php $dVis = Settings::get('genome_default_visibility'); @endphp
        {!! Form::label('genome_visibility', 'Visibility') !!}
        {!! Form::select('genome_visibility', [0 => "Completely Hidden", 1 => "Half-Hidden", 2 => "Completely Visible"], $genome->id ? $genome->visibility_level : $dVis, ['class' => "form-control"]) !!}
        <span class="form-text text-muted">
            The default site setting for genome visibility is <strong class="text-dark">{{ $dVis < 1 ? "Completely Hidden" : ($dVis == 1 ? "Half-Hidden" : "Fully Visible") }}</strong>.
        </span>
    </div>

    <div class="form-group">
        {!! Form::label('Genes') !!}
        <div id="geneList">
            @if($genome->id)
                @foreach ($genome->getLoci() as $loci)
                    <div class="mb-2 d-flex">
                        {!! Form::select('gene_id[]', $genes, $loci->id, ['class' => 'form-control gene-select', 'placeholder' => 'Select Gene Group']) !!}
                        <div class="mx-2 gene-select-options input-group">
                            @if ($loci->type == "gene")
                                @php $i = 0; $alleles = $loci->getAlleles(); @endphp
                                @foreach ($genome->genes->where('loci_id', $loci->id) as $item)
                                    @if($i != 0 && $i % $loci->length == 0)
                            </div>
                            <a href="#" class="btn btn-danger mb-2 delete-genetics-row"><i class="fas fa-times"></i></a>
                        </div>
                        <div class="mb-2 d-flex">
                            {!! Form::select('gene_id[]', $genes, $loci->id, ['class' => 'form-control gene-select', 'placeholder' => 'Select Gene Group']) !!}
                            <div class="mx-2 gene-select-options input-group">
                                    @endif
                                    {!! Form::select('gene_allele_id[]', $alleles, $item->allele->id, ['class' => 'form-control allele-select']) !!}
                                    @php $i++; @endphp
                                @endforeach
                                @while($i % $loci->length != 0)
                                    {!! Form::select('gene_allele_id[]', $alleles, null, ['class' => 'form-control allele-select']) !!}
                                    @php $i++; @endphp
                                @endwhile
                            @elseif ($loci->type == "gradient")
                                @php $i = 0; @endphp
                                @foreach ($genome->gradients->where('loci_id', $loci->id) as $item)
                                    @if($i > 0)
                        </div>
                        <a href="#" class="btn btn-danger mb-2 delete-genetics-row"><i class="fas fa-times"></i></a>
                    </div>
                    <div class="mb-2 d-flex">
                        {!! Form::select('gene_id[]', $genes, $loci->id, ['class' => 'form-control gene-select', 'placeholder' => 'Select Gene Group']) !!}
                        <div class="mx-2 gene-select-options input-group">
                                    @endif
                                    {!! Form::text('gene_gradient_data[]', $item->displayGenome, ['class' => 'form-control gradient-gene-input', 'maxlength' => $loci->length]) !!}
                                    @php $i++; @endphp
                                @endforeach
                            @elseif ($loci->type == "numeric")
                                @php $i = 0; @endphp
                                @foreach ($genome->numerics->where('loci_id', $loci->id) as $item)
                                    @if($i > 0)
                        </div>
                        <a href="#" class="btn btn-danger mb-2 delete-genetics-row"><i class="fas fa-times"></i></a>
                    </div>
                    <div class="mb-2 d-flex">
                        {!! Form::select('gene_id[]', $genes, $loci->id, ['class' => 'form-control gene-select', 'placeholder' => 'Select Gene Group']) !!}
                        <div class="mx-2 gene-select-options input-group">
                                    @endif
                                    {!! Form::number('gene_numeric_data[]', $item->value, ['class' => 'form-control', 'min' => 0, 'max' => $loci->length]) !!}
                                    @php $i++; @endphp
                                @endforeach
                            @endif
                        </div>
                        <a href="#" class="btn btn-danger mb-2 delete-genetics-row"><i class="fas fa-times"></i></a>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="form-group inline">
            <a href="#" class="add-genetics-row btn btn-primary">Add Gene</a>
        </div>
    </div>

    <p class="alert alert-info">For chromosomally linked genes, note that the breeding roller cares about which order the genes are placed in. Chromosomally linked genes in the same column (left for matrilineal inheritance, right for patrilineal inheritance) will always pass together!</p>

    <div class="text-right">
        {!! Form::submit($genome->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}

{{-- Genetics Helper Fields --}}
<div class="genetics-row hide mb-2 d-flex">
    {!! Form::select('gene_id[]', $genes, null, ['class' => 'form-control gene-select', 'placeholder' => 'Select Gene Group']) !!}
    <div class="mx-2 gene-select-options input-group"></div>
    <a href="#" class="btn btn-danger mb-2 delete-genetics-row"><i class="fas fa-times"></i></a>
</div>

{{-- JS --}}
@include("widgets._genome_create_edit_js")
