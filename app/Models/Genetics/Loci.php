<?php

namespace App\Models\Genetics;

use Config;
use DB;
use App\Models\Model;
use App\Models\Feature\FeatureCategory;
use App\Models\Species\Species;
use App\Models\Rarity;
use Illuminate\Validation\Rule;

class Loci extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'length',
        'loci_group_id',
        'sort',
        'default',
        'description',
        'parsed_description',
        'is_visible',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'locis';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the alleles of this feature, sorted from MOST to LEAST dominant.
     */
    public function alleles()
    {
        return $this->hasMany('App\Models\Genetics\LociAllele')->orderBy('is_dominant', 'desc')->orderBy('sort', 'asc');
    }

    /**
     * Gets the alleles of this feature, sorted by LEAST to MOST dominant.
     */
    public function allelesReversed()
    {
        return $this->hasMany('App\Models\Genetics\LociAllele')->orderBy('is_dominant', 'asc')->orderBy('sort', 'desc');
    }

    /**
     * Get the alleles of this feature, sorted from MOST to LEAST dominant.
     */
    public function visibleAlleles()
    {
        return $this->hasMany('App\Models\Genetics\LociAllele')->where('is_visible', 1)->orderBy('is_dominant', 'desc')->orderBy('sort', 'asc');
    }

    /**
     * Get the alleles of this feature, sorted from MOST to LEAST dominant.
     */
    public function hiddenAlleles()
    {
        return $this->hasMany('App\Models\Genetics\LociAllele')->where('is_visible', 0)->orderBy('is_dominant', 'desc')->orderBy('sort', 'asc');
    }

    /**
     * Gets the chromosome.
     */
    public function chromosome()
    {
        return $this->belongsTo('App\Models\Genetics\LociGroup', 'loci_group_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to sort by sort order.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSortBySortOrder($query)
    {
        return $query->orderBy('sort');
    }

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
        return url('world/genetics?name='.$this->name);
    }

    /**
     * Gets a list of alleles in this loci (as plain array), if there are any.
     *
     * @return array
     */
    public function getAlleles()
    {
        if($this->type != "gene") return [];
        return LociAllele::selectRaw('id, if(modifier is not null and modifier != \'\', if(is_dominant is true, concat(name, \'(\', modifier, \')\'), lower(concat(name, \'(\', modifier, \')\'))), if(is_dominant is true, name, lower(name))) as name')->where('loci_id', $this->id)
                ->orderBy('is_dominant', 'desc')->orderBy('sort', 'asc')
                ->pluck('name', 'id')->toArray();
    }

    /**
     * Gets the default options for this loci.
     *
     * @return array
     */
    public function getDefaultOptions()
    {
        if ($this->type == "gene") return $this->getAlleles();
        if ($this->type == "gradient") return ["Inherit as if this were all \"-\".", "Inherit as if this were all \"+\".", "Inherit as if this were alternating \"-\" and \"+\"."];
        if ($this->type == "numeric") return ["Always inherit from other parent.", "Inherits from other parent half the time.", "Inherit as if the value was zero.", "Inherit as if the value was ".$this->length, "Inherit as if the value was ".round($this->length / 2)];
        return [];
    }

    /**
     * Gets the default option for this loci.
     *
     * @return int|string|\App\Models\Genetics\LociAllele
     */
    public function getDefault()
    {
        if ($this->type == "gene") {
            $allele = $this->alleles->where('id', $this->default)->first();
            return $allele ? $allele : $this->allelesReversed->first();
        }
        if ($this->type == "gradient") {
            $gene = "";
            while(strlen($gene) < $this->length) $gene .= $this->default == 1 ? "+" : ($this->default == 2 ? (strlen($gene) % 2 ? "-" : "+") : "-");
            return $gene;
        }
        if ($this->type == "numeric") {
            if($this->default == 2) return 0;
            if($this->default == 3) return $this->length;
            if($this->default == 4) return round($this->length / 2);
            return null;
        }
        return null;
    }
}
