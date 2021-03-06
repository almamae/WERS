<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    //
      //
    /**
	* Field to be mass-assigned.
	*
	* @var array
	*/
	protected $fillable = ['owner_id', 'incident_id', 'lat', 'lng','body','status','address','contact_number', 'is_validate', 'is_resolved'];

	public function ticket()
    {
        return $this->hasOne('App\Ticket', 'report_id');
    }
}
