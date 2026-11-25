<?php

namespace App\Models\Character;

use Config;
use DB;
use App\Models\Model;
use App\Models\Character\CharacterCategory;
use App\Models\Genetics\Loci;

class CharacterGenome extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id',
        'visibility_level',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_genomes';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the image associated with this record.
     */
    public function character()
    {
        return $this->belongsTo('App\Models\Character\Character');
    }

    /**
     * Get the image associated with this record.
     */
    public function genes()
    {
        return $this->hasMany('App\Models\Character\CharacterGenomeGene');//->groupBy('loci_id');
    }

    /**
     * Get the image associated with this record.
     */
    public function gradients()
    {
        return $this->hasMany('App\Models\Character\CharacterGenomeGradient');//->groupBy('loci_id');
    }

    /**
     * Get the image associated with this record.
     */
    public function numerics()
    {
        return $this->hasMany('App\Models\Character\CharacterGenomeNumeric');//->groupBy('loci_id');
    }

    /**
     * Gets all the loci present in this character's genome.
     */
    public function getLoci()
    {
        $array = array_values(array_unique($this->genes->pluck('loci_id')->toArray()));
        foreach(array_unique($this->gradients->pluck('loci_id')->toArray()) as $value) array_push($array, $value);
        foreach(array_unique($this->numerics->pluck('loci_id')->toArray()) as $value) array_push($array, $value);
        $loci = Loci::whereIn('id', $array)->orderBy('sort', 'desc')->get();
        return $loci;
    }

    /**
     * Gets the genes from a loci.
     *
     * @return  Illuminate\Database\Eloquent\Collection
     */
    public function getGenes($loci)
    {
        $genes = $this->getGenesOfType($loci->type);
        if ($genes == null) return null;
        $genes = $genes->where('loci_id', $loci->id);
        return ($genes->count() > 0) ? $genes : null;
    }

    /**
     * Gets the genes collection by type.
     *
     * @return  Illuminate\Database\Eloquent\Collection
     */
    public function getGenesOfType($type)
    {
        if ($type == "gene") return $this->genes;
        if ($type == "gradient") return $this->gradients;
        if ($type == "numeric") return $this->numerics;
        return null;
    }

    /**
     * Checks if this genome contains a particular locus.
     */
    public function hasLocus($loci)
    {
        $genes = $this->getGenesOfType($loci->type);
        if ($genes == null) return false;
        return $genes->where('loci_id', $loci->id)->count() > 0;
    }

    /**
     * Cross this with another genome. Generates actual data.
     */
    public function breedWith($sire)
    {
        $genodata = [
            'gene_id' => [],
            'gene_allele_id' => [],
            'gene_gradient_data' => [],
            'gene_numeric_data' => [],
        ];
        $chromosomes = [];
        foreach (Loci::query()->orderBy('sort', 'desc')->get() as $loci)
        {
            if ($this->hasLocus($loci) || $sire->hasLocus($loci))
            {
                $damGenes = $this->getGenes($loci);
                $sireGenes = $sire->getGenes($loci);
                $flag = true;

                if ($loci->type == "gene")
                {
                    $seed = null;
                    if ($loci->chromosome != null)
                    {
                        if(!isset($chromosomes[$loci->chromosome->id])) $chromosomes[$loci->chromosome->id] = [ mt_rand(0, 1), mt_rand(0, 1) ];
                        $seed = $chromosomes[$loci->chromosome->id];
                    }
                    if ($seed == null) $seed = [ mt_rand(0, 1), mt_rand(0, 1) ];
                    array_push($genodata['gene_allele_id'], ($damGenes == null) ? $loci->getDefault()->id : ($seed[0] == 0 ? $damGenes->first() : $damGenes->last())->allele->id);
                    array_push($genodata['gene_allele_id'], ($sireGenes == null) ? $loci->getDefault()->id : ($seed[1] == 0 ? $sireGenes->first() : $sireGenes->last())->allele->id);
                }
                else if ($loci->type == "gradient")
                {
                    $dam = ($damGenes == null) ? $loci->getDefault() : $damGenes->random()->display_genome;
                    $pop = ($sireGenes == null) ? $loci->getDefault() : $sireGenes->random()->display_genome;
                    $rufus = "";
                    for ($i = 0; $i < $loci->length/2; $i++) $rufus .= substr($dam, ($i*2)+mt_rand(0,1), 1).substr($pop, ($i*2)+mt_rand(0,1), 1);
                    array_push($genodata['gene_gradient_data'], $rufus);
                }
                else if ($loci->type == "numeric")
                {
                    $flag = false;
                    $genes[0] = ($damGenes == null) ? $loci->getDefault() : $damGenes->random()->value;
                    $genes[1] = ($sireGenes == null) ? $loci->getDefault() : $sireGenes->random()->value;
                    $num = $genes[mt_rand(0, 1)];
                    if ($genes[0] === null && $loci->default != 1) $num = $genes[1];
                    if ($genes[1] === null && $loci->default != 1) $num = $genes[0];
                    if ($num)
                    {
                        array_push($genodata['gene_numeric_data'], $num);
                        $flag = true;
                    }
                }

                if ($flag) array_push($genodata['gene_id'], $loci->id);
            }
        }
        return $genodata;
    }

    /**
     * Cross this with another genome. Used for previews.
     */
    public function crossWith($sire)
    {
        $genes = [];
        $chromosomes = [];

        // Go through each loci
        foreach (Loci::query()->orderBy('sort', 'desc')->get() as $loci)
        {
            if ($this->hasLocus($loci) || $sire->hasLocus($loci))
            {
                $side = [0,0];
                if($loci->type == 'gene' && $loci->chromosome != null)
                {
                    if (!isset($chromosomes[$loci->chromosome->id]))
                    {
                        $chromosomes[$loci->chromosome->id] = [ mt_rand(0, 1), mt_rand(0, 1) ];
                    }
                    $side = $chromosomes[$loci->chromosome->id];
                }
                else
                {
                    $side = [ mt_rand(0, 1), mt_rand(0, 1) ];
                }
                $gene = $this->inheritGene($loci, $this->getGenes($loci), $sire->getGenes($loci), $side);
                if($gene !== null) array_push($genes, "<span class='text-monospace text-nowrap mx-1' data-toggle='tooltip' title='". $loci->name ."'>". $gene ."</span>");
            }
        }

        return $genes;
    }

    /**
     * Figures out how to inherit a loci. Used for previews.
     *
     * @param   \App\Models\Genetics\Loci                $loci
     * @param   Illuminate\Database\Eloquent\Collection  $damGenes
     * @param   Illuminate\Database\Eloquent\Collection  $sireGenes
     * @return  array
     */
    private function inheritGene($loci, $damGenes, $sireGenes, $side = [0,0])
    {
        if ($loci->type == "gene") {
            $alleles = "";

            // Matrilineal gene
            if ($damGenes == null) $alleles.= $loci->getDefault()->display_name;
            else $alleles.= (
                $side[0] == 0 ? $damGenes->first() : $damGenes->last()
            )->allele->display_name;

            // Patrilineal gene
            if ($sireGenes == null) $alleles.= $loci->getDefault()->display_name;
            else $alleles.= (
                $side[1] == 0 ? $sireGenes->first() : $sireGenes->last()
            )->allele->display_name;

            return $alleles;
        } elseif ($loci->type == "gradient") {
            $rufus = "";
            $dam = ""; $sire = "";

            // Matrilineal gene
            if ($damGenes == null) $dam = $loci->getDefault();
            else $dam = $damGenes->random()->display_genome;

            // Patrilineal gene
            if ($sireGenes == null) $sire = $loci->getDefault();
            else $sire = $sireGenes->random()->display_genome;

            // Let's see if I remember how to do this...
            for ($i = 0; $i < $loci->length/2; $i++) {
                $rufus .= substr($dam, ($i * 2) + mt_rand(0, 1), 1)
                        . substr($sire, ($i * 2) + mt_rand(0, 1), 1);
            }

            return $rufus;
        } elseif ($loci->type == "numeric") {
            $genes = [];
            $genes[0] = $damGenes == null ? $loci->getDefault() : $damGenes->random()->value;
            $genes[1] = $sireGenes == null ? $loci->getDefault() : $sireGenes->random()->value;
            if ($genes[0] === null && $loci->default != 1) return $genes[1];
            if ($genes[1] === null && $loci->default != 1) return $genes[0];
            return $genes[mt_rand(0, 1)];
        }
    }
}
