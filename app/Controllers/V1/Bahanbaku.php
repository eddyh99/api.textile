<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_bahanbaku;

class Bahanbaku extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->bahan    = new Mdl_bahanbaku();

	}

    public function getBahanbaku(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->bahan->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_bahanid(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->bahan->getby_bahanid($id)
        ];
        return $this->respond($response);
    }

    public function add_bahanbaku(){
        $validation = $this->validation;
        $validation->setRules([					
            'namabahan' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama Bahan is required',
                ]
            ],
            'satuan' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Satuan is required',
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
            'namabahan'     => FILTER_SANITIZE_STRING, 
            'satuan'        => FILTER_SANITIZE_STRING, 
            'harga'         => FILTER_SANITIZE_NUMBER_INT
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "namabahan"     => $data->namabahan,
            "satuan"        => $data->satuan,
            "created_at"    => date("Y-m-d H:i:s")
        );

        $harga=array(
            "tanggal"   => date("Y-m-d H:i:s"),
            "harga"     => $data->harga
        );

        $result=$this->bahan->add($mdata,$harga);
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

    public function update_bahan(){
        $validation = $this->validation;
        $validation->setRules([					
					'namabahan' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama Bahan is required',
					    ]
					],
                    'satuan' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama Bahan is required',
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
            'namabahan'     => FILTER_SANITIZE_STRING, 
            'satuan'        => FILTER_SANITIZE_STRING, 
            'harga'         => FILTER_SANITIZE_NUMBER_INT
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $mdata=array(
            "namabahan"     => $data->namabahan,
            "satuan"        => $data->satuan,
            "created_at"    => date("Y-m-d H:i:s")
        );

        $harga=array(
            "tanggal"   => date("Y-m-d H:i:s"),
            "harga"     => $data->harga
        );
        $result=$this->bahan->updatedata($mdata,$harga, $id);
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

    public function delete_bahan(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $result = $this->bahan->hapus($id);
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

    public function addstok_bahanbaku(){
        $validation = $this->validation;
        $validation->setRules([					
					'jumlah' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Jumlah is required',
					    ]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();

        $id     = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $mdata  = array(
            "id_bahanbaku"  => $id,
            "jumlah"        => $data->jumlah,
            "tanggal"       => date("Y-m-d H:i:s"),
            "keterangan"    => "stok awal",
            "updated_at"    => date("Y-m-d H:i:s")
        );
        $result = $this->bahan->insertStok($mdata);
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

}
