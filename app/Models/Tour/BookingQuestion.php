<?php

namespace App\Models\Tour;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingQuestion extends Model
{
    use HasFactory;

    protected $connection = 'mysql_resource_db';
    protected $table = 'viator_booking_questions';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 
        'type', 
        'group', 
        'label',
        'required',
        'hint',
        'units',
        'allowed_answers',
        'max_length',
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
    /**
     * Import JSON destination data into db
     *
     * @param   object    $json
     * @return  this
     */
    public static function import($json)
    {
        $id = $json->id;

        $question = BookingQuestion::find($id) ?? new BookingQuestion;
        $question->id   = $id;
        $question->type = $json->type;
        $question->group= $json->group; 
        $question->label= $json->label ?? '';
        $question->required = $json->required ?? 'MANDATORY';
        $question->hint = $json->hint ?? '';
        if (isset($json->units))
            $question->units = is_array($json->units) ? implode('|', $json->units) : $json->units;
        $question->allowed_answers = $json->allowed_answers ?? '';
        $question->max_length = $json->max_length ?? 100;

        try {
            $question->save();
        } catch (\Exception $e) {
            \Log::error("Error importing booking questions: ". $e->getMessage());
            $question = null;
        }
        return $question;
    }
}
