<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->member  = model('App\Models\V1\Mdl_member');
        $this->user    = model('App\Models\V1\Admin\Mdl_user');

	}
	

	public function register(){
	    $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'email' => [
						'rules'  => 'required|valid_email',
						'errors' => [
							'required'      => 'Email is required',
							'valid_email'   => 'Invalid Email format'
						]
					],
					'password' => [
					    'rules'  => 'required|min_length[8]',
					    'errors' =>  [
					        'required'      => 'Password is required',
					        'min_length'    => 'Min length password is 8 character'
					    ]
					],
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User timezone is required',
					    ]
					]
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data           = $this->request->getJSON();
        
        $filters = array(
            'email'     => FILTER_VALIDATE_EMAIL, 
            'password'  => FILTER_UNSAFE_RAW, 
            'timezone'  => FILTER_SANITIZE_STRING, 
            'referral'  => FILTER_SANITIZE_STRING, 
        );
	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $mdata = array(
                "bank_id"   => $bank->id,
    	        "email"     => $data->email,
    	        "passwd"    => sha1($data->password),
    	        "location"  => $data->timezone
    	);


	    if (!empty($data->referral)) {
    	    $refmember = $this->member->getby_refcode($data->referral,$bank->id);
    	    if (@$refmember->code==5051){
    	        return $this->respond(@$refmember);
    	    }
	        $mdata["id_referral"] = $refmember->id;

	    }
	    
	    $result = $this->member->add($mdata);
	    if (@$result->code==1060){
    	   return $this->respond(@$result);
	    }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                    "token"   => $result->token,
	                    "ucode"   => $result->ucode
	                ]
	        ];
	   return $this->respond($response);
	}
	
	
	public function activate() {
	    $token = $this->request->getGet('token', FILTER_SANITIZE_STRING);
	    $member = $this->member->getby_token($token);
	    if (@$member->code==5051){
	        return $this->respond(@$member);
	    }
        if (@$member->status == 'active') {
            $response=[
                    "code"       => "5051",
                    "error"      => "05",
                    "message"    => "Member already active"
                ];
            return $this->respond($response);
	    } else if (@$member->status == 'disabled') {
            $response=[
                    "code"      => "5051",
                    "error"     => "06",
                    "message"   => "Your account is suspended. Please contact administrator"
                ];
            return $this->respond($response);
	    } 
	    
	    $result = $this->member->activate($member->ucode);
	    if (@$member->code==5051){
	        return $this->respond(@$member);
	    }
	    
	    $response=[
	            "code"      => "200",
	            "error"      => null,
	            "message"    => "Member is successfully activated"
	        ];
	    return $this->respond($response);
	}
    
    public function getmember_byrefcode(){
	    $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $refcode    = $this->request->getGet('referral', FILTER_SANITIZE_STRING);
        $refmember  = $this->member->getby_refcode($refcode,$bank->id);
	    if (@$refmember->code==5051){
	        return $this->respond(@$refmember);
	    }
        if (@$refmember->status == 'disabled') {
            $response=[
                    "code"      => "5051",
                    "error"     => "06",
                    "message"   => "Referral account is suspended. Please contact administrator"
                ];
            return $this->respond($response);
	    } 	    
	    $response=[
	            "code"      => "200",
	            "error"      => null,
	            "message"    => $refmember
	        ];
	    return $this->respond($response);
    }
    
	public function getmember_byucode() {
	    $ucode = $this->request->getGet('ucode', FILTER_SANITIZE_STRING);
	    $member = $this->member->getby_ucode($ucode);
	    if (@$member->code==5051){
	        return $this->respond(@$member);
	    }
        if (@$member->status == 'disabled') {
            $response=[
                    "code"      => "5051",
                    "error"     => "06",
                    "message"   => "Your account is suspended. Please contact administrator"
                ];
            return $this->respond($response);
	    } 
	    
	    $response=[
	            "code"      => "200",
	            "error"      => null,
	            "message"    => $member
	        ];
	    return $this->respond($response);
	}

	public function getmember_bycausal() {
	    $causal = $this->request->getGet('causal', FILTER_SANITIZE_STRING);
	    $ucode = $this->member->getby_causal($causal);
	    $response=[
	            "code"      => "200",
	            "error"      => null,
	            "message"    => $ucode
	        ];
	    return $this->respond($response);
	}

    public function getmember_byemail() {
	    $bank   = getBankId(apache_request_headers()["Authorization"]);
	    $email  = $this->request->getGet('email');
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)){
	        $response=[
                    "code"      => "5051",
                    "error"     => "06",
                    "message"   => "Invalid email format"
                ];
            return $this->respond($response);
	    }
	    $member = $this->member->getby_email($email,$bank->id);
	    if (@$member->code==5051){
	        return $this->respond(@$member);
	    }
        if (@$member->status == 'disabled') {
            $response=[
                    "code"      => "5051",
                    "error"     => "06",
                    "message"   => "Your account is suspended. Please contact administrator"
                ];
            return $this->respond($response);
	    } 
	    
	    $response=[
	            "code"      => "200",
	            "error"      => null,
	            "message"    => $member
	        ];
	    return $this->respond($response);
	}
    public function signin(){
	    $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'email' => [
						'rules'  => 'required|valid_email',
						'errors' => [
							'required'      => 'Email is required',
							'valid_email'   => 'Invalid Email format'
						]
					],
					'password' => [
					    'rules'  => 'required|min_length[8]',
					    'errors' =>  [
					        'required'      => 'Password is required',
					        'min_length'    => 'Min length password is 8 character'
					    ]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data           = $this->request->getJSON();

        $user = $this->user->getby_email($data->email,$bank->id);
        if (@$user->code==5051){
            $user=NULL;
            $member = $this->member->getby_email($data->email,$bank->id);
    	    if (@$member->code==5051){
    	        return $this->respond(@$member);
    	    }
        }   
        
        if ($user != NULL) {
            if ($data->password == $user->passwd) {
                $session_data = array(
    				'id'        => $user->id,
    				'role'      => $user->role, //"operator", //admin
    				'ucode'     => "",
    				'referral'  => "",
    				'time_location' => $user->location,
    			);
    			
        	    $response=[
        	            "code"      => "200",
        	            "error"      => null,
        	            "message"    => $session_data
        	        ];
        	    return $this->respond($response);
            }else {
                $response=[
                        "code"      => "5051",
                        "error"     => "04",
                        "message"   => "Invalid username or password"
                    ];
                return $this->respond($response);
            } 
        } elseif ($member != NULL) {
            if ($data->password == $member->passwd) {
                if ($member->status=='new'){
                    $response=[
                            "code"      => "5051",
                            "error"     => "22",
                            "message"   => "Please activate your account"
                        ];
                }elseif($member->status=='disabled') {
                    $response=[
                            "code"      => "5051",
                            "error"     => "06",
                            "message"   => "Your account is suspended. Please contact administrator"
                        ];
                }elseif($member->status=='active'){
                    $session_data = array(
        				'id'        => $member->id,
        				'role'      => "member",
        				'ucode'     => $member->ucode,
        				'refcode'   => $member->refcode,
        				'time_location' => $member->location,
        			);
        			
            	    $response=[
            	            "code"      => "200",
            	            "error"      => null,
            	            "message"    => $session_data
            	        ];
                }else{
                    $response=[
                            "code"      => "5051",
                            "error"     => "04",
                            "message"   => "Invalid username or password"
                        ];
                }
        	    return $this->respond($response);
            }else {
                $response=[
                        "code"      => "5051",
                        "error"     => "04",
                        "message"   => "Invalid username or password"
                    ];
                return $this->respond($response);
            }
        } else {
            $response=[
                    "code"      => "5051",
                    "error"     => "04",
                    "message"   => "Invalid username or password"
                ];
            return $this->respond($response);
        }

    }

    public function resetpassword(){
	    $email = $this->request->getGet('email', FILTER_SANITIZE_STRING);
        $token=$this->member->resetToken($email);

	    if (@$token->code==5051){
	        return $this->respond(@$token);
	    }

	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                    "token"   => $token
	                ]
	        ];
	   return $this->respond($response);
	}    
	
	public function updatepassword(){
	    $validation = $this->validation;
        $validation->setRules([
                    'token' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Reset token is required',
						]
					],
					'password' => [
						'rules'  => 'required|min_length[40]',
						'errors' => [
							'required'      => 'Password is required',
							'min_length'    => 'Min length password is 40 characters'
						]
					]
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }

	    $data       = $this->request->getJSON();
        $filters = array(
            'token'     => FILTER_SANITIZE_STRING, 
            'password'  => FILTER_UNSAFE_RAW, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
	    
	    $member     = $this->member->getby_token($data->token);
	    if (@$member->code==5051){
    	    return $this->respond($member);
	    }

        $where=array(
            "email"     => $member->email,
            "token"     => $data->token
            );
        $mdata=array(
            "passwd"    => $data->password,
            "token"     => NULL
            );
            
        $result=$this->member->change_password($mdata,$where);
        if (@$result->code==5051){
	        return $this->respond(@$result);
	    }
	    $response=[
	            "code"      => "200",
	            "error"      => null,
	            "message"    => "Password successfully changed"
	        ];
	    return $this->respond($response);
        
	}
}
