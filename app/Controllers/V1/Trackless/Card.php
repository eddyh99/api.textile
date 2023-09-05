<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Card extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->card     = model('App\Models\V1\Trackless\Mdl_card');
	}
	
	public function setcardBank(){
	    $validation = $this->validation;
        $validation->setRules([
					'registered_name' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Registered name is required',
					    ]
					],
					'iban' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Iban is required',
					    ]
					],
					'causal' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Causal is required',
					    ]
					],
					'currency' => [
					    'rules'  => 'required|min_length[3]|max_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
            ]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	   	$data   = $this->request->getJSON();
        $filters = array(
            'currency'          => FILTER_SANITIZE_STRING, 
            'registered_name'   => FILTER_SANITIZE_STRING, 
            'iban'              => FILTER_SANITIZE_STRING, 
            'causal'            => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $mdata = array(
            "currency"           => $data->currency,
            "registered_name"    => $data->registered_name,
            "iban"               => $data->iban,
            "causal"             => $data->causal,
        );
    	
    	$result=$this->card->setcardBank($mdata);
    	if (@$result->code==5054){
            return $this->respond(@$result);
	    }
        
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Card Bank successfully saved"
	        ];
	    return $this->respond($response);
	}
	
	public function getBank(){
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->card->getcardbank()
	        ];
	   return $this->respond($response);
	    
	}
	
	public function gethistory_cardtopup(){
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
        if (@empty($data->bank_id)){
    	    $all    = $this->card->history_cardtopup(NULL,$data->currency,$data->date_start,$data->date_end, $data->timezone);
	    }else{
    	    $all    = $this->card->history_cardtopup($data->bank_id,$data->currency,$data->date_start,$data->date_end, $data->timezone);
	    }
	    
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $all
            ];
        return $this->respond($response);
	}
	
	public function process_cardtopup(){
	    $validation = $this->validation;
        $validation->setRules([
					'transaction_id' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Transaction ID is required',
					    ]
					]
					]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'transaction_id'       => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->card->update_cardtopup($data->transaction_id);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);					
	}
	
	public function get_physicalcard(){
	    $result     = $this->card->get_inactivecard();
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}
	
	public function link_physicalcard(){
	    $validation = $this->validation;
        $validation->setRules([
					'id' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'ID is required',
					    ]
					]
					]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'id'       => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->card->link_card($data->id);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}
}
