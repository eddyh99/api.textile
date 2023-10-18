<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_penjahit;

class Penjahit extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->penjahit    = new Mdl_penjahit();

	}

    public function get_penjahit(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->penjahit->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_id(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->penjahit->get_byid($id)
        ];
        return $this->respond($response);
    }
    public function add_penjahit(){
        $validation = $this->validation;
        $validation->setRules([
					'nama' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama penjahit is required',
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
					        'required'      => 'Wa/Telp penjahit is required',
					    ]
					],
					'jenis' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Jenis is required',
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
            'jenis'      => FILTER_SANITIZE_STRING, 
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
            "jenis"     => $data->jenis,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->penjahit->add($mdata);
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

    public function update_penjahit(){
        $validation = $this->validation;
        $validation->setRules([
            'nama' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama penjahit is required',
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
                    'required'      => 'Wa/Telp penjahit is required',
                ]
            ],
            'jenis' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Jenis is required',
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
            'jenis'      => FILTER_SANITIZE_STRING, 
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
            "jenis"     => $data->jenis,
            "updated_at"=> date("y-m-d H:i:s")
        );   

        $result=$this->penjahit->updatedata($mdata,$id);
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

    public function delete_penjahit(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_EMAIL);
        $result = $this->penjahit->hapus($id);
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
