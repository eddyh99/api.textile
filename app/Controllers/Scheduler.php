<?php
namespace App\Controllers;

class Scheduler extends BaseController
{
    public function __construct()
    {   
        $this->member  = model('App\Models\V1\Trackless\Mdl_wallet');

	}
	

	public function remove_topup(){
	    $this->member->delete_topup();
	}
}