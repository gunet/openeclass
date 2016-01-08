<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $table = 'assignment';
    /**
     * The attributes that are NOT mass assignable.
     *
     * @var array
     */        
    protected $guarded = ['id'];

    // As we do not maintain created_at and updated_at fields yet
    public $timestamps = false;

    public function getDates()
    {
        return ['deadline'];
    }
    
    public function submissions()
    {
        return $this->hasMany('App\Models\AssignmentSubmit');
    }     
}
