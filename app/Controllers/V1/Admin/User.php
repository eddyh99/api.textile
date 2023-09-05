<?php
namespace App\Controllers\V1\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class User extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   

	}
	
	public function getMasterwallet(){
	    $bank  = getBankId(apache_request_headers()["Authorization"]);
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $bank
	        ];
	   return $this->respond($response);
	}
}
