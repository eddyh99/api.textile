<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_sales;

class Sales extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->sales    = new Mdl_sales();

	}

    public function get_sales(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->sales->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_id(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->sales->get_byid($id)
        ];
        return $this->respond($response);
    }
    public function add_sales(){
        $validation = $this->validation;
        $validation->setRules([
					'nama' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama sales is required',
					    ]
					],
					'telp' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Wa/Telp is required',
					    ]
					],
					'area' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Area Sales is required',
					    ]
					],
					'komisi' => [
					    'rules'  => 'required|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Komisi is required',
					        'greater_than'  => 'Komisi must greater than 0',
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
            'kota'      => FILTER_SANITIZE_STRING, 
            'tgllahir'  => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
            'area'      => FILTER_SANITIZE_STRING, 
            'komisi'    => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "nama"      => $data->nama,
            "alamat"    => $data->alamat,
            "kota"      => $data->kota,
            "tgllahir"  => $data->tgllahir,
            "telp"      => $data->telp,
            "area"      => $data->area,
            "komisi"    => $data->komisi,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->sales->add($mdata);
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

    public function update_sales(){
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
					'area' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Area Sales is required',
					    ]
					],
					'komisi' => [
					    'rules'  => 'required|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Komisi is required',
					        'greater_than'  => 'Komisi must greater than 0',
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
            'kota'      => FILTER_SANITIZE_STRING, 
            'tgllahir'  => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
            'area'      => FILTER_SANITIZE_STRING, 
            'komisi'    => FILTER_SANITIZE_STRING, 
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
            "kota"      => $data->kota,
            "tgllahir"  => $data->tgllahir,
            "telp"      => $data->telp,
            "area"      => $data->area,
            "komisi"    => $data->komisi,
            "updated_at"=> date("y-m-d H:i:s")
        );    

        $result=$this->sales->updatedata($mdata,$id);
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

    public function delete_sales(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_EMAIL);
        $result = $this->sales->hapus($id);
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
