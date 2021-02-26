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
        'lineage_id', 'parent_lineage_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'character_lineage_links';

    /**
     * Gets this character's (the child) lineage.
     * @return App\Models\Character\CharacterLineage
     */
    public function child()
    {
        return $this->belongsTo('App\Models\Character\CharacterLineage', "id", "lineage_id");
    }

    /**
     * Gets the parent character's lineage.
     * @return App\Models\Character\CharacterLineage
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\Character\CharacterLineage', "id", "parent_lineage_id");
    }
}