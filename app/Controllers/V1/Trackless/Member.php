<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Member extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->member = model('App\Models\V1\Trackless\Mdl_member');
	}
	
	public function getAll_bank(){
        $all    = $this->member->get_all();
	    $mdata = array();
	    foreach($all as $bank) {
	       $m = array(
	           "id"             =>  $bank->id,
	           "email"          =>  $bank->email,
	           "bank_name"      =>  $bank->bank_name,
	           "masterwallet"   =>  $bank->ucode_mwallet,
	           "status"         =>  $bank->status,
	           );
	       $mdata[] = $m;
	    }
	    
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $mdata
	        ];
	   return $this->respond($response);
	}

    public function setMember(){
	    $userid   = $this->request->getGet('userid', FILTER_SANITIZE_STRING);
        $status = $this->request->getGet('status', FILTER_SANITIZE_STRING);

        if (($status!="enabled") && ($status!='disabled')){
            $response=[
                    "code"       => "5053",
                    "error"      => "13",
                    "message"    => "Invalid member status"
                ];
            return $this->respond($response);
        }

    	if ($status=='enabled'){
        	$result=$this->member->enable_bank($userid);
        	if (@$result->code==5053){
	            return $this->respond(@$result);
    	    }
        	$response=[
    	            "code"     => "200",
    	            "error"    => null,
    	            "message"  => "Bank is successfully enabled"
    	        ];
        }elseif ($status=='disabled'){
        	$result=$this->member->disable_bank($userid);
            if (@$result->code==5053){
    	        return $this->respond(@$result);
    	    }
            $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Bank is successfully disabled"
	        ];
        }
	   return $this->respond($response);
	}
	
}
