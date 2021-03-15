<?php

namespace App\Http\Controllers\Characters;

use Illuminate\Http\Request;

use Auth;
use Route;

use App\Models\User\User;
use App\Models\Character\Character;
use App\Models\Character\CharacterLineage;

use App\Http\Controllers\Controller;

class LineageController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Lineage Controller
    |--------------------------------------------------------------------------
    |
    | Handles displaying character/myo/rogue lineages.
    |
    */

    /**
     * Create a new controller instance. 
     * Does all the processing of finding the active lineage.
     *
     * @return  void
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $query = Character::query();
            $this->isRogue = false;

            // This route does not have a slug, is it a MYO or rogue?
            if (!Route::current()->parameter('slug')) {
                // No id, so not a MYO. Is it a rogue?
                if (!Route::current()->parameter('id')) {
                    // If its not a rogue, myo or character, we shouldn't be here.
                    if (!Route::current()->parameter('rogue')) abort(404);

                    // It is a rogue, so search for them.
                    $this->isRogue = true;
                    $this->lineage = CharacterLineage::where('id', Route::current()->parameter('rogue'))->where('character_id', null)->first();

                    // If we can't find a rogue lineage with that id, abort.
                    if (!$this->lineage) abort(404);

                // Has an id, so is a MYO. Search characters by ID.
                } else {
                    $query->where('id', Route::current()->parameter('id'));
                }
            // Has a slug, so is a Character. Search characters for slug.
            } else {
                $query->where('slug', Route::current()->parameter('slug'));
            }

            // If this is a character we need to actually find them.
            if (!$this->isRogue) {
                // Does the user have permission to see invisible characters?
                if(!(Auth::check() && Auth::user()->hasPower('manage_characters'))) $query->where('is_visible', 1);
                $this->character = $query->first();

                // If we can't find them, abort.
                if(!$this->character) abort(404);

                // If we did find them, continue.
                $this->lineage = $this->character->lineage;
                $this->character->updateOwner();
            }

            // And we're done.
            return $next($request);
        });
    }

    /**
     * Gets the page that shows ALL the character/lineages of a specified type.
     *
     * @return  \Illuminate\Contracts\Support\Renderable
     */
    public function getList($relation)
    {
        if (Route::current()->parameter('relation')) $relation = Route::current()->parameter('relation');

        // Format the title of the page.
        $title = $relation == "aunts-uncles"        ? "Aunts & Uncles"      : 
               ( $relation == "great-grandparents"  ? "Great-Grandparents"  : 
               ( $relation == "great-grandchildren" ? "Great-Grandchildren" : ucfirst($relation) ));

        // Rogue lineage view.
        if ($this->isRogue) {
            return view('character.lineage.rogue.view', [
                'pageTitle' => $title,
                'lineage' => $this->lineage,
                'lineageType' => $relation,
            ]);
        }
        // Character lineage view.
        return view('character.lineage.view', [
            'pageTitle' => $title,
            'character' => $this->character,
            'lineageType' => $relation,
        ]);
    }

    /**
     * Gets the ancestor index.
     *
     * @return  \Illuminate\Contracts\Support\Renderable
     */
    public function getAncestors()
    {
        return $this->getList('ancestors');
    }

    /**
     * Gets the descendant index.
     *
     * @return  \Illuminate\Contracts\Support\Renderable
     */
    public function getDescendants()
    {
        return $this->getList('descendants');
    }

    /**
     * Gets the cousins index.
     *
     * @return  \Illuminate\Contracts\Support\Renderable
     */
    public function getCousins()
    {
        return $this->getList('relatives');
    }

    /**
     * Gets rogue.
     *
     * @return  \Illuminate\Contracts\Support\Renderable
     */
    public function getRogue()
    {
        if (!$this->isRogue) abort(404);

        return view('character.lineage.rogue.info', [
            'lineage' => $this->lineage,
        ]);
    }
}
