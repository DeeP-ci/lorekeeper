<?php namespace App\Services;

use App\Models\Character\CharacterGenomeGene;
use App\Models\Character\CharacterGenomeGradient;
use App\Models\Character\CharacterGenomeNumeric;
use App\Services\Service;

use DB;
use Config;

use App\Models\Feature\FeatureCategory;
use App\Models\Feature\Feature;
use App\Models\Genetics\Loci;
use App\Models\Genetics\LociAllele;
use App\Models\Genetics\LociGroup;
use App\Models\Species\Species;
use App\Models\Species\Subtype;

class GeneticsService extends Service
{
    /**
     * Create a category.
     *
     * @param  array                            $data
     * @param  \App\Models\User\User            $user
     * @return \App\Models\Feature\Loci|bool
     */
    public function createLoci($data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['name']) || $data['name'] == null || $data['name'] == "") throw new \Exception("Gene groups must have a name.");
            if (!isset($data['type'])) throw new \Exception("Gene groups must have a type.");
            if ( $data['type'] != 'gene'
              && $data['type'] != 'gradient'
              && $data['type'] != 'numeric'
            ) throw new \Exception("Invalid gene type selected.");
            if (!isset($data['length']) || $data['length'] == null || $data['length'] <= 0) throw new \Exception("Must have a length.");
            if ($data['length'] > 255) throw new \Exception("Length must be less than 256.");

            if (isset($data['loci_group_id']))
                $data['loci_group_id'] = LociGroup::find($data['loci_group_id']) ? $data['loci_group_id'] : null;
            else {
                $data['loci_group_id'] = null;
            }

            if (!isset($data['default']) || $data['default'] < 0) $data['default'] = 0;

            if (isset($data['description'])) $data['parsed_description'] = parse($data['description']);
            $data['is_visible'] = isset($data['is_visible']) ? $data['is_visible'] == true : false;

            $loci = Loci::create($data);
            return $this->commitReturn($loci);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a category.
     *
     * @param  \App\Models\Feature\Loci         $category
     * @param  array                            $data
     * @param  \App\Models\User\User            $user
     * @return \App\Models\Feature\Loci|bool
     */
    public function updateLoci($category, $data, $user)
    {
        DB::beginTransaction();

        try {
            // Name
            if (!isset($data['name']) || $data['name'] == null || $data['name'] == "") throw new \Exception("Gene groups must have a name.");
            if(Loci::where('name', $data['name'])->where('id', '!=', $category->id)->exists()) throw new \Exception("The name has already been taken.");

            // Type
            if (!isset($data['type'])) $data['type'] = $category->type;
            if ($data['type'] != $category->type) throw new \Exception("Gene group type cannot be changed.");

            // Length
            if (!isset($data['length']) || $data['length'] == null || $data['length'] <= 0) throw new \Exception("Must have a length.");
            if ($data['type'] == "gradient" && $data['length'] > 64) throw new \Exception("Length must be less than 65.");
            if ($data['length'] > 255) throw new \Exception("Length must be less than 256.");

            // Chromosome
            if (isset($data['loci_group_id']))
                $data['loci_group_id'] = LociGroup::find($data['loci_group_id']) ? $data['loci_group_id'] : null;
            else {
                $data['loci_group_id'] = null;
            }

            // Default
            if (!isset($data['default']) || $data['default'] < 0) $data['default'] = 0;

            $data['parsed_description'] = isset($data['description']) ? parse($data['description']) : null;
            $data['is_visible'] = isset($data['is_visible']) ? $data['is_visible'] == true : false;

            // Alleles
            if ($category->type == "gene") {
                // Add New Alleles
                if (isset($data['allele_name'])) foreach($data['allele_name'] as $key => $alleleName) {
                    if($alleleName && $alleleName != "") {
                        $mod = isset($data['modifier'][$key]) ? $data['modifier'][$key] : "";
                        $sum = isset($data['allele_description'][$key]) ? $data['allele_description'][$key] : "";
                        $dom = $data['is_dominant'][$key] == 1;
                        $vis = $data['allele_visibility'][$key] == 1;
                        $allele = LociAllele::create([
                            'loci_id' => $category->id,
                            'is_dominant' => $dom,
                            'name' => $alleleName,
                            'modifier' => $mod == "" ? null : $mod,
                            'summary' => $sum == "" ? null : $sum,
                            'is_visible' => $vis,
                        ]);
                    }
                }

                // Sort Child Alleles
                if (isset($data['allele_sort'])) {
                    $sort = explode(',', $data['allele_sort']);
                    foreach($sort as $index => $s)
                    {
                        $key = $index; //count($sort)-$index-1;
                        $allele = LociAllele::where('id', $s)->first();
                        if (!$allele) throw new \Exception("Trying to edit an allele that does not exist.");
                        if ($allele->loci_id != $category->id) throw new \Exception("Trying to edit an allele that does not belong to this group.");

                        $isDom = $data['edit_allele_dominance'][$key] == 1;
                        $isVis = $data['edit_allele_visibility'][$key] == 1;

                        $name = $data['edit_allele_name'][$key];
                        if (!$name || $name == "") throw new \Exception("Allele names cannot be null.");
                        if (strlen($name) > 5) throw new \Exception("One of the allele names is too long.");

                        $modifier = isset($data['edit_allele_modifier'][$key]) ? $data['edit_allele_modifier'][$key] : "";
                        if (!$modifier) $modifier = "";
                        if (strlen($modifier) > 5) throw new \Exception("One of the allele modifiers is too long.");

                        $summary = $data['edit_allele_description'][$key];
                        if (!$summary) $summary = "";
                        if (strlen($summary) > 255) throw new \Exception("Allele summaries cannot exceed 255 characters in length.");

                        $allele->update([
                            'is_dominant' => $isDom,
                            'sort' => $index,
                            'name' => $name,
                            'modifier' => $modifier == "" ? null : $modifier,
                            'summary' => $summary == "" ? null : $summary,
                            'is_visible' => $isVis,
                        ]);
                    }
                }
            }

            $category->update($data);
            return $this->commitReturn($category);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Sorts category order.
     *
     * @param  array  $data
     * @return bool
     */
    public function sortLoci($data)
    {
        DB::beginTransaction();
        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));
            foreach($sort as $key => $s)
            {
                Loci::where('id', $s)->update(['sort' => $key]);
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a loci, all instances of characters having those genes, and all the gene's alleles.
     *
     * @param  array  $data
     * @return bool
     */
    public function deleteLoci($loci)
    {
        DB::beginTransaction();
        try {
            $characterGenes = null;
            if ($loci->type == "gene") $characterGenes = CharacterGenomeGene::where("loci_id", $loci->id);
            else if ($loci->type == "gradient") $characterGenes = CharacterGenomeGradient::where("loci_id", $loci->id);
            else if ($loci->type == "numeric") $characterGenes = CharacterGenomeNumeric::where("loci_id", $loci->id);
            else throw new \Exception("Something went wrong, Loci had an impossible type set.");

            if($characterGenes) $characterGenes->delete();
            if ($loci->type == "gene") $loci->alleles()->delete();
            $loci->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes an allele and replaces it with an existing allele from the same locus.
     *
     * @param  array  $data
     * @return bool
     */
    public function deleteLociAllele($data, $loci)
    {
        DB::beginTransaction();
        try {
            if(!$loci) throw new \Exception("Error with Loci.");
            if($loci->type != "gene" || !$loci->alleles->count()) throw new \Exception("This loci doesn't have alleles.");
            if($loci->alleles->count() <= 1) throw new \Exception("You can't delete alleles from a loci with only one allele.");
            $target = LociAllele::find($data['target_allele']);
            if(!$target || $target->loci_id != $loci->id) throw new \Exception("Invalid target.");
            $replacement = LociAllele::find($data['replacement_allele']);
            if(!$replacement || $replacement->loci_id != $loci->id) throw new \Exception("Invalid replacement.");
            if($target->id == $replacement->id) throw new \Exception("Can't replace an allele with itself.");

            CharacterGenomeGene::where('loci_allele_id', $target->id)->update(['loci_allele_id' => $replacement->id]);
            $target->delete();

            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Create a loci group / chromosome.
     *
     * @param  array                            $data
     * @param  \App\Models\User\User            $user
     * @return \App\Models\Feature\LociGroup|bool
     */
    public function createLociGroup($data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['name']) || $data['name'] == null || $data['name'] == "") throw new \Exception("Chromosomes must have a name.");
            if (isset($data['description'])) $data['parsed_description'] = parse($data['description']);
            $data['is_visible'] = isset($data['is_visible']) ? $data['is_visible'] == true : false;

            $chromosome = LociGroup::create($data);
            return $this->commitReturn($chromosome);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Update a loci group / chromosome.
     *
     * @param  \App\Models\Feature\LociGroup    $chromosome
     * @param  array                            $data
     * @param  \App\Models\User\User            $user
     * @return \App\Models\Feature\LociGroup|bool
     */
    public function updateLociGroup($chromosome, $data, $user)
    {
        DB::beginTransaction();

        try {
            if (!isset($data['name']) || $data['name'] == null || $data['name'] == "") throw new \Exception("Chromosomes must have a name.");
            if (isset($data['description']))
            {
                $data['parsed_description'] = parse($data['description']);
            }
            else
            {
                $data['description'] = null;
                $data['parsed_description'] = null;
            }
            $data['is_visible'] = isset($data['is_visible']) ? $data['is_visible'] == true : false;

            $chromosome->update($data);
            return $this->commitReturn($chromosome);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
    /**
     * Updates loci group / chromosome sort order.
     *
     * @param  array    $data
     * @return bool
     */
    public function sortLociGroup($data)
    {
        DB::beginTransaction();
        try {
            // explode the sort array and reverse it since the order is inverted
            $sort = array_reverse(explode(',', $data));
            foreach($sort as $key => $s)
            {
                LociGroup::where('id', $s)->update(['sort' => $key]);
            }
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }

    /**
     * Deletes a loci group / chromosome, and remove associations between the group and its grouped loci.
     *
     * @param  \App\Models\Feature\LociGroup    $chromosome
     * @return bool
     */
    public function deleteLociGroup($chromosome)
    {
        DB::beginTransaction();
        try {
            foreach ($chromosome->locis as $loci)
            {
                $loci->update([
                    'loci_group_id' => null,
                ]);
            }
            $chromosome->delete();
            return $this->commitReturn(true);
        } catch(\Exception $e) {
            $this->setError('error', $e->getMessage());
        }
        return $this->rollbackReturn(false);
    }
}
