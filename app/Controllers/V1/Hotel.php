<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Hotel extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->hotel    = model('App\Models\V1\Mdl_hotel');

	}

    public function get_cphotel(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->hotel->get_allcphotel()
        ];
        return $this->respond($response);
    }

    public function getby_cpid(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->hotel->get_bycpid($id)
        ];
        return $this->respond($response);
    }
    
    public function add_cphotel(){
        $validation = $this->validation;
        $validation->setRules([
					'nama' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama CPhotel is required',
					    ]
					],
					'telp' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Wa/Telp is required',
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
            'tgllahir'  => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
            'komisi'    => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "nama"      => $data->nama,
            "tgllahir"  => $data->tgllahir,
            "telp"      => $data->telp,
            "komisi"    => $data->komisi,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->hotel->addcphotel($mdata);
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

    public function update_cphotel(){
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
            'tgllahir'  => FILTER_SANITIZE_STRING, 
            'telp'      => FILTER_SANITIZE_STRING, 
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
            "tgllahir"  => $data->tgllahir,
            "telp"      => $data->telp,
            "komisi"    => $data->komisi,
            "updated_at"=> date("y-m-d H:i:s")
        );    

        $result=$this->hotel->updatedatacphotel($mdata,$id);
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

    public function delete_cphotel(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_EMAIL);
        $result = $this->hotel->hapuscphotel($id);
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

    public function get_hotel(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->hotel->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_hotelid(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->hotel->get_byhotelid($id)
        ];
        return $this->respond($response);
    }

    public function add_hotel(){
        $validation = $this->validation;
        $validation->setRules([
            'nama' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama Hotel is required',
                ]
            ],
            'alamat' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Alamat Hotel is required',
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
                    'required'      => 'Area is required',
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
            'telp'      => FILTER_SANITIZE_STRING, 
            'area'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="cphotel"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        
        $data=(object) $filtered;

        $mdata=array(
            "nama"      => $data->nama,
            "alamat"    => $data->alamat,
            "kota"      => $data->kota,
            "telp"      => $data->telp,
            "area"      => $data->area,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->hotel->add($mdata,$data->cphotel);
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

    public function update_hotel(){
        $validation = $this->validation;
        $validation->setRules([					
            'nama' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama is required',
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
                    'required'      => 'Wa/Telp is required',
                ]
            ],
            'area' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Area is required',
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
            'telp'      => FILTER_SANITIZE_STRING, 
            'area'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="cphotel"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        
        $data=(object) $filtered;

        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $mdata=array(
                "nama"      => $data->nama,
                "alamat"    => $data->alamat,
                "kota"      => $data->kota,
                "telp"      => $data->telp,
                "area"      => $data->area,
                "updated_at"=> date("y-m-d H:i:s")
        );    

        $result=$this->hotel->updatedata($mdata,$data->cphotel,$id);
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

    public function delete_hotel(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $result = $this->hotel->hapus($id);
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
