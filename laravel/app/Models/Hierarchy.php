<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hierarchy extends Model
{
    protected $table = 'hierarchy';
    protected $guarded = ['id'];
    public $timestamps = false;
    
        public function courses()
        {
            return $this->belongsToMany('App\Models\Course', 'course_department', 'department', 'course');
        }     
}
