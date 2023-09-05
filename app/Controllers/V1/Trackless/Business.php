<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Business extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->business     = model('App\Models\V1\Trackless\Mdl_business');
	}
	
	public function getBusiness(){
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->business->get_all()
	        ];
	   return $this->respond($response);	    
	}
	
	public function setBusiness(){
	    $business_id   = $this->request->getGet('business_id', FILTER_SANITIZE_STRING);
        $status = base64_decode($this->request->getGet('status', FILTER_SANITIZE_STRING));

        if (($status!='delete') && ($status!='approve')){
    	    $response=[
                "code"      => "5055",
                "error"     => "Invalid Status",
                "message"   => "Invalid Status"
            ];
            return $this->respond($response);
        }
        
        if ($status=="approve"){
    	    $result     = $this->business->approve_bisnis($business_id);
        }elseif ($status=="delete"){
    	    $result     = $this->business->delete_bisnis($business_id);
        }
        
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => "COMPLETED"
            ];
        return $this->respond($response);	    
	}

    public function getCategory(){
        $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->business->get_category()
	        ];
	   return $this->respond($response);	    

    }	
    
    public function setCategory(){
        $validation = $this->validation;
        $validation->setRules([
					'category' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Category name is required',
					    ]
					],
            ]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	   	$data   = $this->request->getJSON();

        $filters = array(
            'category'          => FILTER_SANITIZE_STRING, 
        );
	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata = array(
            "category"  => $data->category,
        );
    	
    	$result=$this->business->setCategory($mdata);
    	if (@$result->code==5054){
            return $this->respond(@$result);
	    }
        
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Category successfully saved"
	        ];
	    return $this->respond($response);  
	}

    public function updateCategory(){
        $validation = $this->validation;
        $validation->setRules([
					'id' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Category ID is required',
					    ]
					],
					'category' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Category name is required',
					    ]
					],
            ]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	   	$data   = $this->request->getJSON();

        $filters = array(
            'id'          => FILTER_SANITIZE_STRING, 
            'category'    => FILTER_SANITIZE_STRING, 
        );
	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata = array(
            "category"  => $data->category,
        );
    	
    	$result=$this->business->updateCategory($data->id,$mdata);
    	if (@$result->code==5054){
            return $this->respond(@$result);
	    }
        
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Category successfully saved"
	        ];
	    return $this->respond($response);  
	}
}
