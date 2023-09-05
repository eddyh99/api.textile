<?php
namespace App\Controllers\V1\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Currency extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->currency = model('App\Models\V1\Mdl_currency');
        $this->wallet   = model('App\Models\V1\Mdl_wallet');
	}
	
	public function getActiveCurrency(){
	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					]
				]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $userid   = $this->request->getJSON('userid', FILTER_SANITIZE_STRING);
	    
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->currency->get_active($userid)
	        ];
	   return $this->respond($response);
	}
	
	public function getByCurrency(){
	    $currency   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
	    $userid     = $this->request->getGet('userid', FILTER_SANITIZE_STRING);
    	$result=$this->currency->get_single($currency,$userid);
    	if (@$result->code==5052){
	        return $this->respond(@$result);
	    }
    	$response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $result
	        ];
        return $this->respond($response);
	    
	}

	public function setCurrency(){
	    $currency   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
        $status = $this->request->getGet('status', FILTER_SANITIZE_STRING);
        $userid = $this->request->getGet('userid', FILTER_SANITIZE_STRING);

        if (($status!="active") && ($status!='disabled')){
            $response=[
                    "code"       => "5052",
                    "error"      => "09",
                    "message"    => "Invalid currency status"
                ];
            return $this->respond($response);
        }

        $mdata = array(
                "id_member" => $userid,
    	        "currency"  => $currency,
    	        "status"    => $status
    	);
    	
    	$balance=$this->wallet->get_balance($userid,$currency);
    	if ($balance>0){
        	$response=[
    	            "code"     => "200",
    	            "error"    => "failed",
    	            "message"  => "Cannot disabled currency, balance available"
    	        ];
    	   return $this->respond($response);
    	}
    	
    	$result=$this->currency->set_active($mdata);
    	if (@$result->code==5052){
	        return $this->respond(@$result);
	    }
	    
	    if ($status=="active"){
        	$response=[
    	            "code"     => "200",
    	            "error"    => null,
    	            "message"  => "Currency is successfully activated"
    	        ];
	   }elseif($status=="disabled"){
        	$response=[
    	            "code"     => "200",
    	            "error"    => null,
    	            "message"  => "Currency is successfully deactivated"
    	        ];
	   }
	   return $this->respond($response);
	}
	
	
}
