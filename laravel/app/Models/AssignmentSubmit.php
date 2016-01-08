<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentSubmit extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'assignment_submit';

	protected $guarded = ['id'];

        public function assignment()
        {
            return $this->belongsTo('App\Models\Assignment');
        }        

}
