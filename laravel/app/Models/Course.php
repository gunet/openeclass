<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'course';
        protected $guarded = ['id'];
        public $timestamps = false;
        
        public function departments()
        {
            return $this->belongsToMany('App\Models\Hierarchy', 'course_department', 'course', 'department');
        }   
}
