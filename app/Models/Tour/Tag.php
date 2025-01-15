<?php

namespace App\Models\Tour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasFactory;

    protected $connection = 'mysql_resource_db';

    protected $table = 'viator_tags';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'parent_id', 'name'
    ];
    /**
     * The attributes that should be cast to native types.
     *   defined as object instead of json/array
     *
     * @var array
     */
    protected $casts = [
    ];
    /**
     * The attributes with default values
     *
     * @var array
     */
    protected $attributes = [
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name'
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
    ];

    public function parents() 
    {
        return $this->belongsToMany(Tag::class, 'viator_tags_tags', 'tag_id', 'parent_id');
    }
    public function children() 
    {
        return $this->belongsToMany(Tag::class, 'viator_tags_tags', 'parent_id', 'tag_id');
    }
    public function products() 
    {
        return $this->belongsToMany(Product::class, 'viator_products_tags');
    }
    /**
     * Import JSON destination data into db
     *
     * @param   object    $json
     * @return  this
     */
    public static function import($json)
    {
        $id = $json->tagId;

        $tag = Tag::find($id) ?? new Tag;
        $tag->id   = $id;
        $tag->name = $json->allNamesByLocale->en ?? $id;

        try {
            $tag->save();

            foreach ($json->parentTagIds ?? [] as $pid) {
                $tag->parents()->attach($pid);
            }
        } catch (\Exception $e) {
            \Log::error("Error importing destination: ". $e->getMessage());
        }
        return $tag;
    }
}
