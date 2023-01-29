<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $guarded =[];

    public function section(){
        return $this->belongsTo(Section::class);
    }
    public function project(){
        return $this->belongsTo(Project::class);
    }
    public function status(){
        return $this->belongsTo(Status::class);
    }

    public function assignment(){
        return $this->hasMnay(Assignment::class);
    }
}
