<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Config extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'config';
        protected $primaryKey = 'key';
        public $incrementing = false;
        public $timestamps = false;
         
}
