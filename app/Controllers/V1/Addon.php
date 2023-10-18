<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_addon;

class Addon extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->addon    = new Mdl_addon();

	}

    public function getaddon(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->addon->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_addonid(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->addon->getby_addonid($id)
        ];
        return $this->respond($response);
    }

    public function add_addon(){
        $validation = $this->validation;
        $validation->setRules([					
            'namaaddon' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama addon is required',
                ]
            ],
            'harga' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Harga is required',
                ]
            ],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'namaaddon'     => FILTER_SANITIZE_STRING, 
            'harga'         => FILTER_SANITIZE_NUMBER_INT
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "namaaddon"     => $data->namaaddon,
            "created_at"    => date("Y-m-d H:i:s")
        );

        $harga=array(
            "tanggal"   => date("Y-m-d H:i:s"),
            "harga"     => $data->harga
        );

        $result=$this->addon->add($mdata,$harga);
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

    public function update_addon(){
        $validation = $this->validation;
        $validation->setRules([					
					'namaaddon' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama addon is required',
					    ]
					],
                    'harga' => [
                        'rules'  => 'required',
                        'errors' =>  [
                            'required'      => 'Harga is required',
                        ]
                    ],                    
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'namaaddon'     => FILTER_SANITIZE_STRING, 
            'harga'         => FILTER_SANITIZE_NUMBER_INT
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $mdata=array(
            "namaaddon"     => $data->namaaddon,
            "updated_at"    => date("Y-m-d H:i:s")
        );

        $harga=array(
            "tanggal"   => date("Y-m-d H:i:s"),
            "harga"     => $data->harga
        );
        $result=$this->addon->updatedata($mdata,$harga, $id);
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

    public function delete_addon(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $result = $this->addon->hapus($id);
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
