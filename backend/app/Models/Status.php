<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $guarded =[];
    public function tasks(){
        return $this->HasMany(Task::class);
    }
    public function projects(){
        return $this->HasMany(Project::class);
    }
    public function sections(){
        return $this->HasMany(Section::class);
    }
}
