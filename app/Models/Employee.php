<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory;

    // protected $fillable= [
    //     'country_id',
    //     'state_id',
    //     'city_id',
    //     'department_id',
    //     'first_name',
    //     'last_name',
    //     'middle_name'
 
   // ]

   protected $guarded =['id'];

   public function department() {
    return $this->belongsTo(Department::class);
   }

   public function city() {
    return $this->belongsTo(City::class);
   }


   public function state() {
    return $this->belongsTo(State::class);
   }

   public function country() {
    return $this->belongsTo(Country::class);
   }

   public function team(): BelongsTo{
    return $this->belongsTo(Team::class);
   }
}
