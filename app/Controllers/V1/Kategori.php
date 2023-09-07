<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Kategori extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->kategori    = model('App\Models\V1\Mdl_kategori');

	}

    public function getkategori(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->kategori->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_kategori(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->kategori->get_bykategori($id)
        ];
        return $this->respond($response);
    }

    public function add_kategori(){
        $validation = $this->validation;
        $validation->setRules([					
					'namakategori' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama kategori is required',
					    ]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'namakategori'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "namakategori"      => $data->namakategori,
            "created_at"        => date("y-m-d H:i:s")
        );

        $result=$this->kategori->add($mdata);
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

    public function update_kategori(){
        $validation = $this->validation;
        $validation->setRules([					
					'namakategori' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama kategori is required',
					    ]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'namakategori'    => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $mdata=array(
            "namakategori"  => $data->namakategori            
        );
        $result=$this->kategori->updatedata($mdata,$id);
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

    public function delete_kategori(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $result = $this->kategori->hapus($id);
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
