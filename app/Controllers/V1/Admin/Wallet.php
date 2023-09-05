<?php
namespace App\Controllers\V1\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Admin Wallet
    Desc        : Modul ini di gunakan untuk aktifitas history wallet sisi admin
    Sub fungsi  : 
        - getAll_Balance        : mengambil semua balance
        - balance_ByCurrency    : mengambil balance berdasar currency
        - gethistory_bycurrency : mengambil history transaksi berdasar currency
        - gethistory_topup      : mengambil history topup wallet 
        - gethistory_tobank     : mengambil history wallet ke bank
        - gethistory_tocard     : mengambil history wallet ke card
------------------------------------------------------------*/


class Wallet extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->wallet       = model('App\Models\V1\Admin\Mdl_wallet');
        $this->currency     = model('App\Models\V1\Trackless\Mdl_currency');
	}
	
	public function getAll_Balance(){
	    $bank  = getBankId(apache_request_headers()["Authorization"]);
	    $result=$this->wallet->get_all($bank->id);
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
	
	public function balance_ByCurrency(){
	    $cur    = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
	    $bank   = getBankId(apache_request_headers()["Authorization"]);
	   
	    $result=$this->wallet->balance_bycurrency($bank->id,$cur);
	    if (@$result->code==5052){
	            return $this->respond(@$result);
    	    }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                "bank"      => $bank->id,
	                "detail"    => $this->currency->get_single($cur),
	                "balance"   => $result
	                ]
	        ];
	   return $this->respond($response);
	}
	
	public function gethistory_bycurrency(){
	    $bank   = getBankId(apache_request_headers()["Authorization"]);

	    $validation = $this->validation;
        $validation->setRules([
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'date_start' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Start Date is required',
					    ]
					],
					'date_end' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'End Date is required',
					    ]
					],
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Timezone is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'      => FILTER_SANITIZE_STRING, 
            'date_start'    => FILTER_SANITIZE_STRING, 
            'date_end'      => FILTER_SANITIZE_STRING, 
            'timezone'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->wallet->get_historybycurrency($data->currency,$data->date_start,$data->date_end, $data->timezone, $bank->id);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}

	public function gethistory_topup(){
	    $bank   = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'date_start' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Start Date is required',
					    ]
					],
					'date_end' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'End Date is required',
					    ]
					],
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Timezone is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'      => FILTER_SANITIZE_STRING, 
            'date_start'    => FILTER_SANITIZE_STRING, 
            'date_end'      => FILTER_SANITIZE_STRING, 
            'timezone'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->wallet->history_topup($data->currency,$data->date_start,$data->date_end, $data->timezone,$bank->id);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}

	public function gethistory_wallet(){
	    $bank   = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'date_start' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Start Date is required',
					    ]
					],
					'date_end' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'End Date is required',
					    ]
					],
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Timezone is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'      => FILTER_SANITIZE_STRING, 
            'date_start'    => FILTER_SANITIZE_STRING, 
            'date_end'      => FILTER_SANITIZE_STRING, 
            'timezone'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->wallet->history_wallet($data->currency,$data->date_start,$data->date_end, $data->timezone,$bank->id);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}

	public function gethistory_tobank(){
	    $bank   = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'date_start' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Start Date is required',
					    ]
					],
					'date_end' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'End Date is required',
					    ]
					],
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Timezone is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'      => FILTER_SANITIZE_STRING, 
            'date_start'    => FILTER_SANITIZE_STRING, 
            'date_end'      => FILTER_SANITIZE_STRING, 
            'timezone'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->wallet->history_tobank($data->currency,$data->date_start,$data->date_end, $data->timezone,$bank->id);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}
    
	public function gethistory_tocard(){
	    $bank   = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'date_start' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Start Date is required',
					    ]
					],
					'date_end' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'End Date is required',
					    ]
					],
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Timezone is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'      => FILTER_SANITIZE_STRING, 
            'date_start'    => FILTER_SANITIZE_STRING, 
            'date_end'      => FILTER_SANITIZE_STRING, 
            'timezone'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->wallet->history_tocard($data->currency,$data->date_start,$data->date_end, $data->timezone,$bank->id);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}
    
}
