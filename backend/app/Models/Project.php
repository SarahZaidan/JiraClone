<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function userproject(){
        return $this->HasMany(UserProject::class);
    }

    public function sections(){
        return $this->HasMany(Section::class);
    }
    public function status(){
        return $this->belongsTo(Status::class);
    }
    public function tasks(){
        return $this->hasMany(Task::class);
    }
}
