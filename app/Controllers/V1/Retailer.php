<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_retailer;

class Retailer extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->retailer    = new Mdl_retailer();

	}

    public function get_retailer(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->retailer->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_email(){
        $email  = $this->request->getGet('email', FILTER_SANITIZE_EMAIL);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->retailer->get_byemail($email)
        ];
        return $this->respond($response);
    }

    public function add_retailer(){
        $validation = $this->validation;
        $validation->setRules([
					'email' => [
						'rules'  => 'required|valid_email',
						'errors' => [
							'required'      => 'Email is required',
							'valid_email'      => 'Email shoud be valid',
						]
					],
					'passwd' => [
					    'rules'  => 'required|min_length[8]',
					    'errors' =>  [
					        'required'      => 'Password is required',
					        'min_length'    => 'Min length password is 8 character'
					    ]
					],
					'nama' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama is required',
					    ]
					],
					'telp' => [
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
            'email'     => FILTER_SANITIZE_EMAIL, 
            'passwd'    => FILTER_SANITIZE_STRING, 
            'nama'      => FILTER_SANITIZE_STRING, 
            'alamat'    => FILTER_SANITIZE_STRING, 
            'kota'      => FILTER_SANITIZE_STRING, 
            'tgllahir'  => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "email"     => $data->email,
            "passwd"    => $data->passwd,
            "nama"      => $data->nama,
            "alamat"    => $data->alamat,
            "kota"      => $data->kota,
            "tgllahir"  => $data->tgllahir,
            "telp"      => $data->telp,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->retailer->add($mdata);
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

    public function update_retailer(){
        $validation = $this->validation;
        $validation->setRules([					
					'nama' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama is required',
					    ]
					],
					'telp' => [
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
            'passwd'    => FILTER_SANITIZE_STRING, 
            'nama'      => FILTER_SANITIZE_STRING, 
            'alamat'    => FILTER_SANITIZE_STRING, 
            'kota'      => FILTER_SANITIZE_STRING, 
            'tgllahir'  => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $email  = $this->request->getGet('email', FILTER_SANITIZE_EMAIL);
        if (empty($data->passwd)){
            $mdata=array(
                "nama"      => $data->nama,
                "alamat"    => $data->alamat,
                "kota"      => $data->kota,
                "tgllahir"  => $data->tgllahir,
                "telp"      => $data->telp,
                "updated_at"=> date("y-m-d H:i:s")
            );    
        }else{
            $mdata=array(
                "passwd"    => $data->passwd,
                "nama"      => $data->nama,
                "alamat"    => $data->alamat,
                "kota"      => $data->kota,
                "tgllahir"  => $data->tgllahir,
                "telp"      => $data->telp,
                "updated_at"=> date("y-m-d H:i:s")
            );    
        }

        $result=$this->retailer->updatedata($mdata,$email);
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

    public function delete_retailer(){
        $email  = $this->request->getGet('email', FILTER_SANITIZE_EMAIL);
        $result = $this->retailer->hapus($email);
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
