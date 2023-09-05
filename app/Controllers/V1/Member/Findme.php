<?php
namespace App\Controllers\V1\Member;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Member Findme
    Desc        : Modul ini di gunakan untuk aktifitas findme di sisi Member
    Sub fungsi  : 
        - get_category      : berfungsi mendapatkan kategori
        - get_countrylist   : berfungsi mendapatkan country list
        - get_citylist      : berfungsi mendapatkan city list
        - set_business      : berfungsi menyimpan bisnis find me
        - getlocation       : berfungsi melakukan pencarian lokasi berdasar kota & kategori
------------------------------------------------------------*/


class Findme extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->findme   = model('App\Models\V1\Mdl_findme');
        $this->member   = model('App\Models\V1\Mdl_member');
	}
	
	public function get_countrylist(){
        $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->findme->countrylist()
	        ];
        return $this->respond($response);
	}

	public function get_statelist(){
	    $country   = $this->request->getGet('country', FILTER_SANITIZE_STRING);
        $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->findme->statelist($country)
	        ];
        return $this->respond($response);
	}

	public function get_citylist(){
	    $country   = $this->request->getGet('country', FILTER_SANITIZE_STRING);
	    $state      = $this->request->getGet('state', FILTER_SANITIZE_STRING);
        $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->findme->citylist($country,$state)
	        ];
        return $this->respond($response);
	}
	
	public function get_category(){
	   $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->findme->categorylist()
	        ];
        return $this->respond($response);
	}
	
	public function set_business(){
	    
	    $validation = $this->validation;
        $validation->setRules([
					'ucode' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Unique Code is required',
					    ]
					],
					'city_code' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'City Code is required',
					    ]
					],
					'business_name' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Business name is required',
					    ]
					],
					'googlemap' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Google map link is required',
					    ]
					],
					'logo' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Logo is required',
					    ]
					],
                    'category' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Category is required',
					    ]
					]
			]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'ucode'         => FILTER_SANITIZE_STRING, 
            'city_code'     => FILTER_SANITIZE_STRING, 
            'business_name' => FILTER_SANITIZE_STRING, 
            'googlemap'     => FILTER_SANITIZE_STRING, 
            'logo'          => FILTER_SANITIZE_STRING, 
            'category'      => FILTER_DEFAULT, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="category"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        
        $data=(object) $filtered; 
        
        $recipient_id=$this->member->getby_ucode($data->ucode);
        if (@$recipient_id->code=="5051"){
    	    return $this->respond($recipient_id);
        }
        
        $mdata=array(
                "id_member"     => $recipient_id->id,
                "city_code"     => $data->city_code,
                "business_name" => $data->business_name,
                "googlemap"     => $data->googlemap,
                "logo"          => $data->logo
            );

        $result=$this->findme->add($mdata,$data->category);
        if (@$result->code==5055){
    	   return $this->respond($result);
        }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "COMPLETED"
	        ];
	   return $this->respond($response);       
	}
	
	public function getlocation(){
	 	$category   = $this->request->getGet('category', FILTER_SANITIZE_STRING);
	    $city       = $this->request->getGet('city', FILTER_SANITIZE_STRING);
    
        
        $result=$this->findme->searchme($city,$category);
        if (@$result->code==5055){
    	   return $this->respond($result);
        }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $result
	        ];
	   return $this->respond($response);       

	}
}
