<?php

namespace App\Http\Controllers\Admin\Data;

use Auth;
use App\Http\Controllers\Controller;
use App\Models\Character\Character;
use App\Models\Character\CharacterBreedingLog;
use App\Models\Character\CharacterGenome;
use App\Models\Feature\Feature;
use App\Models\Feature\FeatureCategory;
use App\Models\Genetics\Loci;
use App\Models\Genetics\LociAllele;
use App\Models\Genetics\LociGroup;
use App\Models\Rarity;
use App\Models\Species\Species;
use App\Models\User\User;
use App\Services\CharacterManager;
use App\Services\GeneticsService;
use Illuminate\Http\Request;

class GeneticsController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Genetics Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of character genetics alleles and loci and such.
    |
    */

    /** Controller Middleware */
    public function __construct() {
        $this->middleware("power:view_hidden_genetics");
    }

    /**
     * Shows the gene group as a sorted index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $query = Loci::query();
        $data = $request->only(['name', 'type']);
        if(isset($data['name'])) $query->where('name', 'LIKE', '%'.$data['name'].'%');
        if(isset($data['type'])) {
            switch ($data['type']) {
                case 1:
                    $query->where('type', 'gene');
                    break;
                case 2:
                    $query->where('type', 'gradient');
                    break;
                case 3:
                    $query->where('type', 'numeric');
                    break;
                default: break;
            }
        }
        return view('admin.genetics.locis', [
            'locis' => $query->orderBy('sort', 'desc')->paginate(20)->appends($request->query()),
            'categories' => ['none' => 'Any Category'] + FeatureCategory::orderBy('sort', 'desc')->pluck('name', 'id')->toArray()
        ]);
    }

    /**
     * Shows the gene group as a sortable list.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSortIndex()
    {
        return view('admin.genetics.loci_sort', [
            'categories' => loci::orderBy('sort', 'desc')->get()
        ]);
    }

    /**
     * Sorts the gene groups.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortLoci(Request $request, GeneticsService $service)
    {
        if($service->sortLoci($request->get('sort'))) {
            flash('Gene group order updated successfully.')->success();
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Shows the create gene group page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLoci()
    {
        return view('admin.genetics.create_edit_loci', [
            'category' => new Loci,
            'defaultOptions' => ["Cannot Set"],
            'chromosomeOptions' => [0 => "No Group"] + LociGroup::orderBy('sort', 'desc')->pluck('id', 'name')->toArray(),
        ]);
    }

    /**
     * Shows the edit gene group page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLoci($id)
    {
        $category = Loci::find($id);
        if(!$category) abort(404);
        return view('admin.genetics.create_edit_loci', [
            'category' => $category,
            'defaultOptions' => $category->getDefaultOptions(),
            'chromosomeOptions' => [0 => "No Group"] + LociGroup::orderBy('sort', 'desc')->pluck('name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a gene group.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLoci(Request $request, GeneticsService $service, $id = null)
    {
        $data = $request->only([
            'name', 'type', 'length',
            'loci_group_id',
            'default',
            'is_dominant', 'allele_name', 'modifier', 'allele_sort',
            'edit_allele_dominance', 'edit_allele_name', 'edit_allele_modifier',
            'description', 'parsed_description', 'is_visible',
            'allele_visibility', 'allele_description',
            'edit_allele_visibility', 'edit_allele_description',
        ]);
        if ($id && $service->updateLoci(Loci::find($id), $data, Auth::user())) {
            flash('Category updated successfully.')->success();
        } else if (!$id && $category = $service->createLoci($data, Auth::user())) {
            flash('Category created successfully.')->success();
            return redirect()->to('admin/genetics/edit/'.$category->id);
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the gene group deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLoci($id)
    {
        $category = Loci::find($id);
        return view('admin.genetics._delete_loci', [
            'loci' => $category,
        ]);
    }

    /**
     * Deletes a gene group.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLoci(Request $request, GeneticsService $service, $id)
    {
        if($id && $service->deleteLoci(Loci::find($id))) {
            flash('Loci deleted successfully.')->success();
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/genetics/genes');
    }

    /**
     * Gets the gene group deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteAllele($id)
    {
        $loci = Loci::find($id);
        return view('admin.genetics._delete_loci_allele', [
            'loci' => $loci,
            'alleles' => $loci->alleles->pluck('full_name', 'id')->toArray(),
        ]);
    }

    /**
     * Creates or edits a feature category.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteAllele(Request $request, GeneticsService $service, $id)
    {
        $loci = Loci::find($id);
        if (!$loci) abort(404);
        $data = $request->only(['target_allele', 'replacement_allele']);
        if($id && $service->deleteLociAllele($data, $loci)) {
            flash('Allele deleted successfully.')->success();
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/genetics/edit/'.$loci->id);
    }

    /**
     * Shows a breeding log page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBreedingLog(Request $request, $id)
    {
        $log = CharacterBreedingLog::where('id', $id)->first();
        if (!$log) abort(404);

        return view('admin.genetics.breeding_log', [
            'log' => $log,
        ]);
    }

    /**
     * Shows the breeding log index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBreedingLogs(Request $request)
   {
       $query = CharacterBreedingLog::query();
       $data = $request->only(['name', 'type']);
       if(isset($data['name'])) $query->where('name', 'LIKE', '%'.$data['name'].'%');

       return view('admin.genetics.logs', [
           'logs' => $query->orderBy('rolled_at', isset($data['type']) ? $data['type'] : 'desc')->paginate(20)->appends($request->query()),
       ]);
   }

    /**
     * Shows the breeding roller index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBreedingRoller(Request $request)
    {
        $ids = CharacterGenome::select('character_id')->distinct()->pluck('character_id')->toArray();
        $characters = Character::selectRaw("id, if(name is not null, concat(slug, ': ', name), slug) as select_name")->where('is_myo_slot', false)->whereIn('id', $ids)->pluck('select_name', 'id')->toArray();
        return view('admin.genetics.roller', [
            'characters' => $characters,
            'userOptions' => User::where("is_banned", false)->orderBy('name')->pluck('name', 'id')->toArray(),
            'rarities' => ['0' => 'Select Rarity'] + Rarity::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'speciesOptions' => ['0' => 'Select Species'] + Species::orderBy('sort', 'DESC')->pluck('name', 'id')->toArray(),
            'subtypes' => ['0' => 'Pick a Species First'],
            'features' => Feature::orderBy('name')->pluck('name', 'id')->toArray(),
            'lineageDetected' => class_exists("App\Models\Character\CharacterLineage", false),
        ]);
    }

    /**
     * Posts a breeding roll.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postBreedingRoll(Request $request, CharacterManager $service)
    {
        $request->validate(Character::$myoRules);
        $data = $request->only([
            'parents', 'min_offspring', 'max_offspring', 'twin_chance', 'twin_depth', 'chimera_chance', 'chimera_depth', 'litter_limit',
            'name', 'user_id', 'genome_visibility', 'species_id', 'subtype_id', 'rarity_id',
            'is_visible', 'is_giftable', 'is_tradeable', 'is_sellable', 'sale_value', 'transferrable_at',
            'description', 'image', 'use_cropper', 'x0', 'x1', 'y0', 'y1', 'thumbnail',
            'designer_id', 'designer_url', 'artist_id', 'artist_url',
            'generate_lineage',
        ]);
        if ($litter = $service->createMyoLitter($data, Auth::user())) {
            flash('Breeding rolled successfully.')->success();
            return redirect()->to('admin/genetics/logs/breeding/'.$litter->id);
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back()->withInput();
    }

    /**
     * Grabs the list of a character's genomes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCharacterGenomes(Request $request)
    {
        $character = Character::where('id', $request->input('id'))->where('is_myo_slot', false)->first();
        if (!$character) abort(404);
        if (!$character->genomes) abort(404);
        return view('admin.genetics._fetch_genomes', [
            'genomes' => $character->genomes,
        ]);
    }

    /**
     * Grabs a list of possible child genomes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getPossibleChildGenomes(Request $request)
    {
        $sire = Character::where('id', $request->input('sire'))->where('is_myo_slot', false)->first();
        if (!$sire) abort(404);
        if (!$sire->genomes) abort(404);

        $dam = Character::where('id', $request->input('dam'))->where('is_myo_slot', false)->first();
        if (!$dam) abort(404);
        if (!$dam->genomes) abort(404);

        $children = $this->testCombineGenes(
            $sire, $dam,
            max(0, $request->input('min')), max(0, $request->input('max')),
            max(0, min(100, $request->input('twin'))), max(1, $request->input('depth')),
            max(0, min(100, $request->input('chimera'))), max(1, $request->input('genomes')),
            max(1, $request->input('limit')),
        );

        return view('admin.genetics._fetch_genomes', [
            'genomes' => $children,
            'preview' => true,
        ]);
    }

    private function testCombineGenes($sire, $dam, $min = 0, $max = 3, $twin = 0, $depth = 1, $chimerism = 0, $genomes = 1, $limit = 99999)
    {
        $children = [];
        $count = 0;
        for ($i = 0; $i < mt_rand($min, $max); $i++) {
            // gets random genomes from parents, allows for children to be from different genomes.
            $mother = $dam->genomes->random();
            $father = $sire->genomes->random();

            // a function inside CharacterGenome that will cross mother's genes with father's.
            // called from the mother's genome to ensure the matrilineal genes go first.
            $child = [ $mother->crossWith($father) ];
            $count++;

            $g = 1; // Has one genome.
            while (mt_rand(1, 100) <= $chimerism && $g <= $genomes-1) {
                // Add another one, this child's a chimera!
                array_push($child, $mother->crossWith($father));
                $g++;
            }

            $d = 0; // Twin depth.
            $twins = [];
            while (mt_rand(1, 100) <= $twin && $d < $depth && $count < $limit) {
                // Grab a random genome from the original child...
                $twin = $child[mt_rand(0, count($child)-1)];
                // Or maybe even another twin!
                if (count($twins) > 0) {
                    $randomTwin = $twins[mt_rand(0, count($twins)-1)];
                    $twin = $randomTwin[mt_rand(0, count($randomTwin)-1)];
                }
                $twin = [ $twin ];
                $count++;

                // Now, does this twin have chimerism?
                $g = 1; // Has one genome.
                while (mt_rand(1, 100) <= $chimerism && $g <= $genomes-1) {
                    // Not anymore, now it's a chimera!
                    array_push($twin, $mother->crossWith($father));
                    $g++;
                }

                array_push($twins, $twin);
                $d++;
            }

            // Add the child to the children array.
            if(count($child) > 1) array_push($child, ["<span class='mx-1 text-monospace'>Chimera!</span>"]);
            array_push($children, $child);

            // Add the twins to the children array.
            foreach ($twins as $id => $twin) {
                array_push($twin, ["<span class='mx-1 text-monospace'>Twin".($id > 0 ? " x".($id + 1) : "")."!".(count($twin) > 1 ? " Chimera!" : "")."</span>"]);
                array_push($children, $twin);
            }

            // Return if we've met the hard limit.
            if ($count >= $limit) return $children;
        }
        return $children;
    }

    /**********************************************************************************************

        CHROMOSOMES / LOCI GROUPS

    **********************************************************************************************/

    /**
     * Shows loci groups / chromosomes as a sorted index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getLociGroups(Request $request)
    {
        $query = LociGroup::query();
        $data = $request->only(['name']);
        if(isset($data['name'])) $query->where('name', 'LIKE', '%'.$data['name'].'%');
        return view('admin.genetics.chromosomes.chromosomes', [
            'chromosomes' => $query->orderBy('sort', 'desc')->paginate(20)->appends($request->query())
        ]);
    }

    /**
     * Shows the sortable list of loci groups / chromosomes.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getSortLociGroups()
    {
        return view('admin.genetics.chromosomes.chromosome_sort', [
            'chromosomes' => LociGroup::orderBy('sort', 'desc')->get()
        ]);
    }

    /**
     * Shows the create loci group / chromosome page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLociGroup()
    {
        return view('admin.genetics.chromosomes.create_edit_chromosome', [
            'chromosome' => new LociGroup,
        ]);
    }

    /**
     * Shows the edit loci group / chromosome page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLociGroup($id)
    {
        $chromosome = LociGroup::find($id);
        if(!$chromosome) abort(404);
        return view('admin.genetics.chromosomes.create_edit_chromosome', [
            'chromosome' => $chromosome,
        ]);
    }

    /**
     * Gets the loci group / chromosome deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLociGroup($id)
    {
        $chromosome = LociGroup::find($id);
        return view('admin.genetics.chromosomes._delete_chromosome', [
            'chromosome' => $chromosome,
        ]);
    }

    /**
     * Sorts loci groups / chromosomes.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postSortLociGroups(Request $request, GeneticsService $service)
    {
        if($service->sortLociGroup($request->get('sort'))) {
            flash('Chromosome order updated successfully.')->success();
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Creates or edits a loci group / chromosome.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditLociGroup(Request $request, GeneticsService $service, $id = null)
    {
        $data = $request->only([
            'name',
            'description',
            'parsed_description',
            'is_visible',
        ]);
        if ($id && $service->updateLociGroup(LociGroup::find($id), $data, Auth::user())) {
            flash('Chromosome updated successfully.')->success();
        } else if (!$id && $chromosome = $service->createLociGroup($data, Auth::user())) {
            flash('Chromosome created successfully.')->success();
            return redirect()->to('admin/genetics/chromosome/edit/'.$chromosome->id);
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Deletes a loci group / chromosome.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\FeatureService  $service
     * @param  int|null                     $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteLociGroup(Request $request, GeneticsService $service, $id)
    {
        if($id && $service->deleteLociGroup(LociGroup::find($id))) {
            flash('Chromosome deleted successfully.')->success();
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/genetics/chromosomes');
    }
}
