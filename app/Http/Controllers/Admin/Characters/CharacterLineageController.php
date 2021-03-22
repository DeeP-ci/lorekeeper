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
     * Shows the edit character lineage admin control panel page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLineage($id)
    {
        $lineage = CharacterLineage::where('id', $id)->first();
        if (!$lineage) return abort(404);

        $characters = Character::where('is_myo_slot', false)->orderBy('slug')->get()->pluck('full_name', 'id')->toArray();
        $lineages = CharacterLineage::where('character_id', null)->pluck('character_name', 'id')->toArray();
        return view('admin.masterlist.create_edit_lineage', [
            'lineage' => $lineage,
            'characterOptions' => $characters,
            'rogueOptions' => $lineages,
        ]);
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

        $characters = Character::where('id', '!=', isset($this->character) ? $this->character->id : 0)->where('is_myo_slot', false)->orderBy('slug')->get()->pluck('full_name', 'id')->toArray();
        $lineages = CharacterLineage::where('character_id', null)->pluck('character_name', 'id')->toArray();
        return view('character.admin._edit_lineage_modal', [
            'character' => $isRogue ? null : $this->character,
            'lineage' => $isRogue ? $this->lineage : $this->character->lineage,
            'isMyo' => $isMyo,
            'isRogue' => $isRogue,
            'character_options' => $characters,
            'rogue_options' => $lineages,
        ]);
    }
}
