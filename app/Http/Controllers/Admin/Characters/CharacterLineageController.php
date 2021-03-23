<?php

namespace App\Http\Controllers\Admin\Characters;

use Illuminate\Http\Request;

use Auth;

use App\Models\Character\Character;
use App\Models\Character\CharacterImage;
use App\Models\Character\CharacterLineage;
use App\Models\Character\CharacterCategory;
use App\Models\Rarity;
use App\Models\User\User;
use App\Models\Species\Species;
use App\Models\Species\Subtype;
use App\Models\Feature\Feature;

use App\Services\CharacterManager;
use App\Services\LineageManager;

use App\Http\Controllers\Controller;

class CharacterLineageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Character Lineage Controller
    |--------------------------------------------------------------------------
    |
    | Handles admin creation/editing of character lineages.
    |
    */

    /**
     * Shows the lineage index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        // TODO refine this search function further?

        $query = CharacterLineage::query();
        $data = $request->only([
            'name', 'filter',
        ]);
        $type = 0;

        if(isset($data['filter']))
        {
            $filter = $data['filter'];
            switch ($filter) {
                case 1:
                    $query->where('character_id', '!=', null);
                    $type = 1;
                    break;
                case 2:
                    $query->where('character_id', null);
                    $type = 2;
                    break;
            }
        }

        if(isset($data['name']))
        {
            $name = $data['name'];
            switch ($type) {
                case 1:
                    $query->whereHas('character', function($q) use ($name) {
                        $q->where('name', 'like', '%'.$name.'%')
                        ->orWhere('slug', 'like', '%'.$name.'%');
                    });
                    break;
                case 2:
                    $query->where('character_name', 'LIKE', '%'.$name.'%');
                    break;
                default:
                    $query->where('character_name', 'LIKE', '%'.$name.'%')
                        ->orWhereHas('character', function($q) use ($name) {
                            $q->where('name', 'like', '%'.$name.'%')
                            ->orWhere('slug', 'like', '%'.$name.'%');
                        });
                    break;
            }
        }

        return view('admin.masterlist.lineages', [
            'lineages' => $query->paginate(20)->appends($request->query()),
        ]);
    }

    /**
     * Shows the create lineage admin control panel page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateLineage()
    {
        return $this->getCreateEditLineage(new CharacterLineage);
    }

    /**
     * Shows the edit character lineage admin control panel page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLineage($id)
    {
        $lineage = CharacterLineage::where('id', $id)->first();
        if (!$lineage) return abort(404);

        return $this->getCreateEditLineage($lineage);
    }

    /**
     * Shows the edit character lineage admin control panel page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function getCreateEditLineage($lineage)
    {
        if (!$lineage) return abort(404);
        $isMyo = $lineage->id ? ($lineage->character ? $lineage->character->is_myo_slot : false) : true;

        // If there is an ID, this is an EXISTING lineage.
        if ($lineage->id) {
            $ids = CharacterLineage::where('character_id', '!=', null)->where('id', '!=', $lineage->id)->pluck('character_id')->toArray();
            $childOptions = $isMyo ?
                null : 
                Character::selectRaw('id, IF(is_myo_slot, CONCAT(\'#\', id, \' - \', name), IF(name IS NOT NULL, CONCAT(slug, \': \', name), slug)) as identifiable_name')
                    ->where('deleted_at', null)
                    ->orderBy('is_myo_slot', 'asc')->orderBy('slug')
                    ->pluck('identifiable_name', 'id')->toArray();
        } else {
            $ids = CharacterLineage::where('character_id', '!=', null)->pluck('character_id')->toArray();
            $childOptions = null;
        }

        if(!$isMyo) {
            // This is a lineage belonging to a character or rogue, it cannot be a MYO and can have kids.
            $ownerOptions = Character::selectRaw('id, IF(is_myo_slot, CONCAT(\'#\', id, \' - \', name), IF(name IS NOT NULL, CONCAT(slug, \': \', name), slug)) as identifiable_name')
                ->where('deleted_at', null)->where('is_myo_slot', false)->whereNotIn('id', $ids)
                ->orderBy('is_myo_slot', 'asc')->orderBy('slug')
                ->pluck('identifiable_name', 'id')->toArray();
        } else {
            // This is a new lineage (don't know if it's allowed children yet) or a MYO lineage (cannot have kids)
            $ownerOptions = Character::selectRaw('id, IF(is_myo_slot, CONCAT(\'#\', id, \' - \', name), IF(name IS NOT NULL, CONCAT(slug, \': \', name), slug)) as identifiable_name')
                ->where('deleted_at', null)->whereNotIn('id', $ids)
                ->orderBy('is_myo_slot', 'asc')->orderBy('slug')
                ->pluck('identifiable_name', 'id')->toArray();
        }

        $parentOptions = Character::selectRaw('id, IF(is_myo_slot, CONCAT(\'#\', id, \' - \', name), IF(name IS NOT NULL, CONCAT(slug, \': \', name), slug)) as identifiable_name')
            ->where('deleted_at', null)->where('is_myo_slot', false)
            ->orderBy('is_myo_slot', 'asc')->orderBy('slug')
            ->pluck('identifiable_name', 'id')->toArray();
        $rogueOptions = CharacterLineage::where('character_id', null)->pluck('character_name', 'id')->toArray();

        return view('admin.masterlist.create_edit_lineage', [
            'lineage' => $lineage,
            'ownerOptions' => $ownerOptions,
            'parentOptions' => $parentOptions,
            'childOptions' => $childOptions,
            'rogueOptions' => $rogueOptions,
        ]);
    }

    /**
     * Create a lineage in the ACP.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\LineageManager  $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateLineage(Request $request, LineageManager $service)
    {
        $data = $request->only([
            'owner_id', 'owner_name',
            'parent_type', 'parent_data',
        ]);
        if ($lineage = $service->createLineage($data, Auth::user())) {
            flash('Lineage created successfully.')->success();
            return redirect()->to('admin/masterlist/lineages/edit/'.$lineage->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back()->withInput();
    }

    /**
     * Edit a character's lineage.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\LineageManager  $service
     * @param  string                       $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditCharacterLineage(Request $request, LineageManager $service, $slug)
    {
        $this->character = Character::where('slug', $slug)->first();
        if (!$this->character) abort(404);

        $this->lineage = $this->character->lineage;
        return $this->postEditLineage($request, $service);
    }

    /**
     * Edit lineage.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\LineageManager  $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function postEditLineage(Request $request, LineageManager $service)
    {
        if (!isset($this->character) && !isset($this->lineage)) abort(404);
        $data = $request->only([
            'parent_type', 'parent_data',
            'child_type', 'child_data',
        ]);
        if ($lineage = $service->editLineage($data, Auth::user())) {
            flash('Lineage edited successfully.')->success();
            return redirect()->to($lineage->url);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back()->withInput();
    }

    /**
     * Gets the edit character lineage modal.
     *
     * @param  string  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditCharacterLineage($slug)
    {
        $this->character = Character::where('slug', $slug)->first();
        if (!$this->character) abort(404);
        return $this->getEditLineageModal();
    }

    /**
     * Gets the edit myo lineage modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditMyoLineage($id)
    {
        $this->character = Character::where('id', $id)->first();
        if (!$this->character) abort(404);
        return $this->getEditLineageModal();
    }

    /**
     * Gets the edit rogue lineage modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditRogueLineage($id)
    {
        $this->lineage = CharacterLineage::where('id', $id)->where('character_id', null)->first();
        if (!$this->lineage) abort(404);
        return $this->getEditLineageModal();
    }

    /**
     * Shows the edit character lineage modal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    private function getEditLineageModal()
    {
        if (!isset($this->character) && !isset($this->lineage)) abort(404);
        $isMyo = isset($this->character) ? $this->character->is_myo_slot : false;
        $isRogue = !isset($this->character);
        $lineage = $isRogue ? $this->lineage : $this->character->lineage;

        // Get a list of characters without lineages, and the character that owns this lineage (if any).
        $ids = CharacterLineage::where('character_id', '!=', null)->where('character_id', '!=', $lineage ? $lineage->character_id : null)->pluck('character_id')->toArray();
        $ownerOptions = Character::selectRaw('id, IF(is_myo_slot, CONCAT(\'#\', id, \' - \', name), IF(name IS NOT NULL, CONCAT(slug, \': \', name), slug)) as identifiable_name');
        if (!$isMyo) $ownerOptions = $ownerOptions->where('is_myo_slot', false);
        $ownerOptions = $ownerOptions
            ->where('deleted_at', null)->whereNotIn('id', $ids)
            ->orderBy('is_myo_slot', 'asc')->orderBy('slug')
            ->pluck('identifiable_name', 'id')->toArray();
        $parentOptions = Character::selectRaw('id, IF(is_myo_slot, CONCAT(\'#\', id, \' - \', name), IF(name IS NOT NULL, CONCAT(slug, \': \', name), slug)) as identifiable_name')
            ->where('deleted_at', null)->where('is_myo_slot', false)
            ->orderBy('is_myo_slot', 'asc')->orderBy('slug')
            ->pluck('identifiable_name', 'id')->toArray();
        if (!$isMyo) $childOptions = Character::selectRaw('id, IF(is_myo_slot, CONCAT(\'#\', id, \' - \', name), IF(name IS NOT NULL, CONCAT(slug, \': \', name), slug)) as identifiable_name')
                ->where('deleted_at', null)
                ->orderBy('is_myo_slot', 'asc')->orderBy('slug')
                ->pluck('identifiable_name', 'id')->toArray();
        $rogueOptions = CharacterLineage::where('character_id', null)->pluck('character_name', 'id')->toArray();

        return view('character.admin._edit_lineage_modal', [
            'character' => $isRogue ? null : $this->character,
            'lineage' => $lineage,
            'isMyo' => $isMyo,
            'isRogue' => $isRogue,
            'ownerOptions' => $ownerOptions,
            'parentOptions' => $parentOptions,
            'childOptions' => isset($childOptions) ? $childOptions : [],
            'rogueOptions' => $rogueOptions,
        ]);
    }

    /**
     * Shows the delete lineage modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteLineageModal($id)
    {
        $this->lineage = CharacterLineage::where('id', $id)->first();
        if (!$this->lineage) abort(404);

        return view('admin.masterlist._delete_lineage', [
            'lineage' => $this->lineage,
        ]);
    }

    /**
     * Deletes a lineage.
     *
     * @param  \Illuminate\Http\Request     $request
     * @param  App\Services\LineageManager  $service
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postDeleteLineage(Request $request, LineageManager $service, $id)
    {
        $this->lineage = CharacterLineage::where('id', $id)->first();
        if (!$this->lineage) abort(404);

        $data = $request->only(['lineage_id']);
        $url = $this->lineage->character ? $this->lineage->character->url : 'admin/masterlist/lineages';

        if ($service->deleteLineage($this->lineage, $data, Auth::user())) {
            flash('Lineage deleted successfully.')->success();
            return redirect()->to($url);
        } else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back()->withInput();
    }
}
