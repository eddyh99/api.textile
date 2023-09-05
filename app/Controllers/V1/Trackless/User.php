<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class User extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->member = model('App\Models\V1\Trackless\Mdl_user');
        $this->wallet = model('App\Models\V1\Trackless\Mdl_wallet');
	}
	
	public function getAll(){
        $validation = $this->validation;
        $validation->setRules([
					'timezone' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Timezone is required',
						]
					],
					'currency' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Currency is required',
						]
					],
				]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
	    if (@empty($data->bank_id)){
            $all    = $this->member->get_all(NULL,$data->timezone,$data->currency);
	    }else{
            $all    = $this->member->get_all($data->bank_id,$data->timezone,$data->currency);
	    }


	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $all
	        ];
	   return $this->respond($response);
	}

    public function setMember(){
	    $userid   = $this->request->getGet('userid', FILTER_SANITIZE_STRING);
        $status = $this->request->getGet('status', FILTER_SANITIZE_STRING);

        if (($status!="enabled") && ($status!='disabled') && ($status!='activate')){
            $response=[
                    "code"       => "5053",
                    "error"      => "13",
                    "message"    => "Invalid member status"
                ];
            return $this->respond($response);
        }

    	if ($status=='enabled'){
        	$result=$this->member->enable_member($userid);
        	if (@$result->code==5053){
	            return $this->respond(@$result);
    	    }
        	$response=[
    	            "code"     => "200",
    	            "error"    => null,
    	            "message"  => "Member is successfully activated"
    	        ];
        }elseif ($status=='disabled'){
        	$result=$this->member->disable_member($userid);
            if (@$result->code==5053){
    	        return $this->respond(@$result);
    	    }
            $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Member is successfully disabled"
	        ];
        }elseif ($status=='activate'){
        	$result=$this->member->activate($userid);
            if (@$result->code==5053){
    	        return $this->respond(@$result);
    	    }
            $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Member is successfully activate"
	        ];
        }
	   return $this->respond($response);
	}
	
	public function updatepassword(){
	    $validation = $this->validation;
        $validation->setRules([
					'password' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Password is required',
						]
					],
				]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
    	$result=$this->member->change_password($data->userid,$data->password);
        if (@$result->code==5053){
	        return $this->respond(@$result);
	    }
        $response=[
            "code"     => "200",
            "error"    => null,
            "message"  => "Password is successfully changed"
        ];
	    return $this->respond($response);
    }
    

	public function getHistory(){
	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
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
            'userid'        => FILTER_SANITIZE_STRING, 
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

	    $result     = $this->wallet->getHistory_byid($data->userid,$data->currency,$data->date_start,$data->date_end, $data->timezone);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
	}
	
	public function bulk_activate(){
	    $data   = $this->request->getJSON();
	    foreach ($data as $dt){
    	    $this->member->enable_member($dt);
	    }
    	$response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "COMPLETED"
	        ];
        return $this->respond($response);
	}
    
}
