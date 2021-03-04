<?php

namespace App\Models\Character;

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
     * @return App\Models\Character\CharacterLineageLink
     */
    public function children()
    {
        return $this->hasMany('App\Models\Character\CharacterLineageLink', "parent_lineage_id", "id");
    }

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
}