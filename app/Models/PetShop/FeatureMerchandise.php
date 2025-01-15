<?php
namespace App\Models\PetShop;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureMerchandise extends Model
{
    use HasFactory;
    protected $connection = 'mysql_pet_shop';
    protected $table = 'feature_merchandises';
    protected $fillable = ['merchandise_id'];
}
