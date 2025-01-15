<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExperienceCategory extends Model
{
    use HasFactory;
    protected $connection = 'mysql_resource_db';
    protected $table = 'experience_categories';
    protected $fillable = [
        'category_id',
        'name',
        'image',
        'parent_id',
    ];
    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(ExperienceCategory::class, 'parent_id', 'category_id');
    }
    public function children()
    {
        return $this->hasMany(ExperienceCategory::class, 'parent_id', 'category_id');
    }

    public function experiences()
    {
        return $this->belongsToMany(Experience::class, 'experience_categories_experiences', 'experience_id', 'category_id');
    }

}
