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
        return $this->getEditLineageModal();
    }

    /**
     * Shows the edit character lineage modal.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditLineageModal()
    {
        if(!$this->character) abort(404);

        $characters = Character::where('id', '!=', $this->character->id)->where('is_myo_slot', false)->orderBy('slug')->get()->pluck('full_name', 'id')->toArray();
        $lineages = CharacterLineage::where('character_id', null)->pluck('character_name', 'id')->toArray();
        return view('character.admin._edit_lineage_modal', [
            'character' => $this->character,
            'isMyo' => $this->character->is_myo_slot,
            'character_options' => $characters,
            'rogue_options' => $lineages,
        ]);
    }
}
