<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Currency extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->currency = model('App\Models\V1\Trackless\Mdl_currency');
	}
	
	public function getAllCurrency(){
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->currency->get_all()
	        ];
	   return $this->respond($response);
	}
	
	public function currencyStatus(){
	    $data   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
        $status = $this->request->getGet('status', FILTER_SANITIZE_STRING);

        if (($status!="active") && ($status!='disabled')){
            $response=[
                    "code"       => "5052",
                    "error"      => "09",
                    "message"    => "Invalid currency status"
                ];
            return $this->respond($response);
        }

    	if ($status=='active'){
        	$result=$this->currency->enable($data);
        	if (@$result->code==5052){
	            return $this->respond(@$result);
    	    }
        	$response=[
    	            "code"     => "200",
    	            "error"    => null,
    	            "message"  => "Currency is successfully activated"
    	        ];
        }elseif ($status=='disabled'){
            $cekwise=apiwise(urlapi()->checkbalance,NULL,urlapi()->token,"GET");
            foreach ($cekwise as $dt){
                if ($dt->currency==$data){
                    if ($dt->amount->value>0){
                    	$response=[
                	            "code"     => "200",
                	            "error"    => "failed",
                	            "message"  => "Cannot disabled currency, balance available"
                	        ];
                	   return $this->respond($response);
                	   break;
                    }
                }
            }

        	$result=$this->currency->disable($data);
            if (@$result->code==5052){
    	        return $this->respond(@$result);
    	    }
            $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Currency is successfully disabled"
	        ];
        }
    	   return $this->respond($response);
	}
	
	public function getsymbol(){
	    $currency   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
    	$result=$this->currency->get_single($currency);
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
}
