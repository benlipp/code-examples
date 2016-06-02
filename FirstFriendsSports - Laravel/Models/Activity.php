<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Activity extends Model
{
  protected $table = 'activities';


  public function programs()
  {
    return $this->hasMany('\App\Models\Program');
  }

  public function seshions()
  {
    return $this->hasManyThrough('\App\Models\Seshion', '\App\Models\Program');
  }

  public function setNameAttribute($value)
  {
    $this->attributes['name'] = $value;
    $this->attributes['slug'] = Str::slug($value);
  }
}
