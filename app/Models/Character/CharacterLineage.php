<?php

namespace App\Models\Character;

use Auth;

use App\Models\Model;

use App\Models\Character\Character;

class CharacterLineage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_id', 'character_name',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_lineages';

    # -------------------------------------------------------------------------------------
    #   MODEL LINKS
    # -------------------------------------------------------------------------------------

    /**
     * Gets the character this lineage is linked to.
     * @return App\Models\Character\Character
     */
    public function character()
    {
        return $this->belongsTo('App\Models\Character\Character', "character_id", "id");
    }

    /**
     * Gets the lineage links where this character is the child.
     * @return App\Models\Character\CharacterLineageLink
     */
    public function parents()
    {
        return $this->hasMany('App\Models\Character\CharacterLineageLink', "lineage_id", "id");
    }

    /**
     * Gets the lineage links where this character is the parent.
     * WARNING: Will show hidden characters, use getChildren() instead.
     * 
     * @return App\Models\Character\CharacterLineageLink
     */
    public function children()
    {
        return $this->hasMany('App\Models\Character\CharacterLineageLink', "parent_lineage_id", "id");
    }

    # -------------------------------------------------------------------------------------
    #   RELATIVES
    # -------------------------------------------------------------------------------------

    /**
     * Gets the lineage links where the child character (if there is one) is visible to the user.
     * 
     * @return App\Models\Character\CharacterLineageLink
     */
    public function getChildren()
    {
        return $this->getFiltered($this->children);
    }

    /**
     * Gets the lineage links where the parent character (if there is one) is visible to the user.
     * 
     * @return App\Models\Character\CharacterLineageLink
     */
    public function getParents()
    {
        return $this->getFiltered($this->parents, true);
    }

    /**
     * Finds visible siblings of this character lineage.
     * 
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getSiblings()
    {
        // Are there parents?
        $parents = $this->getParents()->pluck('parent_lineage_id')->toArray();
        if (!$this->getParents()->count()) return null;

        // Get the siblings.
        $sibs = CharacterLineageLink::where('lineage_id', "!=", $this->id)->whereIn('parent_lineage_id', $parents);
        return $sibs->select('lineage_id')->groupBy('lineage_id');
        return $this->getFiltered($sibs)->get();
    }

    # -------------------------------------------------------------------------------------
    #   HELPERS
    # -------------------------------------------------------------------------------------

    /**
     * Filters LineageLinks to get only visible ones.
     * 
     * @param   Illuminate\Database\Eloquent\Collection
     * @param   bool
     * @return  Illuminate\Database\Eloquent\Collection
     */
    public static function getFiltered($collection, $parent = false)
    {
        $col = ($parent ? 'parent_' : '').'lineage_id';
        $ids = CharacterLineage::getInvisiblesFromIds($collection->pluck($col)->toArray());
        return $collection->whereNotIn($col, $ids);
    }

    /**
     * Filters a list of CharacterLineage ids to find ones the user isn't supposed to see.
     * 
     * @param   array
     * @return  array
     */
    public static function getInvisiblesFromIds($ids)
    {
        if (!is_array($ids)) return [];

        // Hide invisible children, if the User shouldn't be able to see them.
        if(!Auth::check() || !(Auth::check() && Auth::user()->hasPower('manage_characters'))) {
            return CharacterLineage::where('character_id', "!=", null)
                ->whereIn('character_lineages.id', $ids)
                ->join('characters', 'character_lineages.character_id', '=', 'characters.id')
                ->where('characters.is_visible', false)->pluck('character_lineages.id')->toArray();
        }

        // User has auth to see everything.
        return [];
    }

    # -------------------------------------------------------------------------------------
    #   ATTRIBUTES
    # -------------------------------------------------------------------------------------

    /**
     * Gets the name of this lineage character.
     * @return string
     */
    public function getNameAttribute()
    {
        if($this->character) return $this->character->full_name;
        return (!$this->character_name) ? "Unknown" : $this->character_name;
    }

    /**
     * Gets the character's page's URL, or the URL of a rogue entry.
     * @return string
     */
    public function getUrlAttribute()
    {
        if($this->character) return $this->character->url;
        return url('rogue/'.$this->id);
    }

    /**
     * Gets the URL of this lineage character.
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        if($this->character) return $this->character->display_name;
        return "<a href='".$this->url."'>".$this->name."</a>";
    }

    /**
     * Gets the thumbnail image of this lineage character.
     * @return string
     */
    public function getThumbnailAttribute()
    {
        if($this->character) return $this->character->image->thumbnailUrl;
        return url('images/rogue.png');
    }
}