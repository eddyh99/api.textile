<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_supplier;

class Supplier extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->supplier    = new Mdl_supplier();

	}

    public function get_supplier(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->supplier->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_id(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->supplier->get_byid($id)
        ];
        return $this->respond($response);
    }
    public function add_supplier(){
        $validation = $this->validation;
        $validation->setRules([
					'nama' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama supplier is required',
					    ]
					],
					'alamat' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Alamat is required',
					    ]
					],
					'telp' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Wa/Telp supplier is required',
					    ]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'nama'      => FILTER_SANITIZE_STRING, 
            'alamat'    => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "nama"      => $data->nama,
            "alamat"    => $data->alamat,
            "telp"      => $data->telp,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->supplier->add($mdata);
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

    public function update_supplier(){
        $validation = $this->validation;
        $validation->setRules([
            'nama' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama supplier is required',
                ]
            ],
            'alamat' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Alamat is required',
                ]
            ],
            'telp' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Wa/Telp supplier is required',
                ]
            ],

    ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'nama'      => FILTER_SANITIZE_STRING, 
            'alamat'    => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $id  = $this->request->getGet('id', FILTER_SANITIZE_EMAIL);
        $mdata=array(
            "nama"      => $data->nama,
            "alamat"    => $data->alamat,
            "telp"      => $data->telp,
            "updated_at"=> date("y-m-d H:i:s")
        );   

        $result=$this->supplier->updatedata($mdata,$id);
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

    public function delete_supplier(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_EMAIL);
        $result = $this->supplier->hapus($id);
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
