<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Pengguna extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->pengguna    = model('App\Models\V1\Mdl_user');

	}

    public function get_pengguna(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->pengguna->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_uname(){
        $uname  = $this->request->getGet('uname', FILTER_SANITIZE_STRING);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->pengguna->get_byuname($uname)
        ];
        return $this->respond($response);
    }

    public function add_pengguna(){
        $validation = $this->validation;
        $validation->setRules([
            'uname' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Username is required',
                ]
            ],
            'passwd' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Password is required',
                ]
            ],
            'role' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Role pengguna is required',
                ]
            ],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'uname'     => FILTER_SANITIZE_STRING, 
            'passwd'    => FILTER_SANITIZE_STRING, 
            'nama'      => FILTER_SANITIZE_STRING, 
            'role'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "uname"     => $data->uname,
            "passwd"    => $data->passwd,
            "nama"      => $data->nama,
            "role"      => $data->role,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->pengguna->add($mdata);
        if (@$result->code==5055){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => $result->message
	        ];
            return $this->respond($response);
        }

        $response=[
            "code"     => "200",
            "error"    => NULL,
            "message"  => "Data successfully inserted"
        ];
        return $this->respond($response);

    }

    public function update_pengguna(){
        $validation = $this->validation;
        $validation->setRules([
					'nama' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama is required',
					    ]
					],
					'role' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Wa/Telp is required',
					    ]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'nama'      => FILTER_SANITIZE_STRING, 
            'passwd'    => FILTER_SANITIZE_STRING, 
            'role'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $uname  = $this->request->getGet('uname', FILTER_SANITIZE_STRING);
        if (empty($data->passwd)){
            $mdata=array(
                "nama"      => $data->nama,
                "role"      => $data->role,
                "updated_at"=> date("y-m-d H:i:s")
            );    
        }else{
            $mdata=array(
                "passwd"    => $data->passwd,
                "nama"      => $data->nama,
                "role"      => $data->role,
                "updated_at"=> date("y-m-d H:i:s")
            );    
        }

        $result=$this->pengguna->updatedata($mdata,$uname);
        if (@$result->code==5055){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => $result->message
	        ];
            return $this->respond($response);
        }

        $response=[
            "code"     => "200",
            "error"    => NULL,
            "message"  => "Data successfully updated"
        ];
        return $this->respond($response);

    }

    public function delete_pengguna(){
        $uname  = $this->request->getGet('uname', FILTER_SANITIZE_STRING);
        if ($uname=='admin'){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => "username 'Admin' can't be deleted"
	        ];
            return $this->respond($response);
        }
        $result = $this->pengguna->hapus($uname);
        if (@$result->code==5055){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => $result->message
	        ];
            return $this->respond($response);
        }

        $response=[
            "code"     => "200",
            "error"    => NULL,
            "message"  => "Data successfully deleted"
        ];
        return $this->respond($response);
    }

}
