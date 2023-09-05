<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->user    = model('App\Models\V1\Admin\Mdl_user');

	}
	
    public function signin(){
	    $validation = $this->validation;
        $validation->setRules([
					'uname' => [
						'rules'  => 'required',
						'errors' => [
							'required'      => 'Username is required',
						]
					],
					'passwd' => [
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

        
		$response=[
				"code"      => "5051",
				"error"     => "04",
				"message"   => $data
			];
		return $this->respond($response);

    }
}
