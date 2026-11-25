<?php

namespace App\Models\Genetics;

use App\Models\Model;

class LociGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'sort',
        'description',
        'parsed_description',
        'is_visible',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'loci_groups';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the loci of this chromosome, sorted by sort order.
     */
    public function locis()
    {
        return $this->hasMany('App\Models\Genetics\Loci', 'loci_group_id')->orderBy('sort', 'desc');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include visible.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', 1);
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the model's name, linked to its encyclopedia page.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return '<a href="'.$this->url.'">'.$this->name.'</a>';
    }

    /**
     * Gets the URL of the model's encyclopedia page.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return url('world/genetics?chromosome='.$this->id);
    }

}
