<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Wallet extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->wallet       = model('App\Models\V1\Trackless\Mdl_wallet');
        $this->currency     = model('App\Models\V1\Trackless\Mdl_currency');
	}
	
	public function getAll_Balance(){
	    $bank_id    = $this->request->getGet('bank_id', FILTER_SANITIZE_STRING);
	    $result=$this->wallet->get_all($bank_id);
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
	    $cur        = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
	    $bank_id    = $this->request->getGet('bank_id', FILTER_SANITIZE_STRING);
	    $result=$this->wallet->balance_bycurrency($cur,$bank_id);
	    if (@$result->code==5052){
	            return $this->respond(@$result);
    	    }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                "detail"    => $this->currency->get_single($cur),
	                "balance"   => $result
	                ]
	        ];
	   return $this->respond($response);
	}
	
	public function gethistory_bycurrency(){
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

	    $result     = $this->wallet->get_historybycurrency($data->currency,$data->date_start,$data->date_end, $data->timezone);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}

	public function gethistory_banktopup(){
	    $validation = $this->validation;
        $validation->setRules([
					'bank_id' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Bank ID is required',
					    ]
					],
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
            'bank_id'       => FILTER_SANITIZE_STRING, 
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

	    $result     = $this->wallet->history_topup($data->bank_id,$data->currency,$data->date_start,$data->date_end, $data->timezone);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}

	public function gethistory_bankwallet(){
	    $validation = $this->validation;
        $validation->setRules([
					'bank_id' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Bank ID is required',
					    ]
					],
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
            'bank_id'       => FILTER_SANITIZE_STRING, 
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

	    $result     = $this->wallet->history_wallet($data->bank_id,$data->currency,$data->date_start,$data->date_end, $data->timezone);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}

	public function gethistory_banktobank(){
	    $validation = $this->validation;
        $validation->setRules([
					'bank_id' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Bank ID is required',
					    ]
					],
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
            'bank_id'       => FILTER_SANITIZE_STRING, 
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

	    $result     = $this->wallet->history_tobank($data->bank_id,$data->currency,$data->date_start,$data->date_end, $data->timezone);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}
    
    
}
