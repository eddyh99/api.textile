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

    public function get_allfee(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->penjahit->get_fee()
        ];
        return $this->respond($response);
    }

    public function get_feebyid(){
        $jenis          = $this->request->getGet('jenis', FILTER_SANITIZE_STRING);
        $id_kategori    = $this->request->getGet('id_kategori', FILTER_SANITIZE_NUMBER_INT);
        
        $where=array(
            "jenis"         => $jenis,
            "id_kategori"   => $id_kategori,
            "is_deleted"    => "no"
        );

        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->penjahit->getfee_byid($where)
        ];
        return $this->respond($response);
    }

    public function penjahit_fee(){
        $validation = $this->validation;
        $validation->setRules([
            'jenis' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Jenis is required',
                ]
            ],
            'id_kategori' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Kategori is required',
                ]
            ],
            'fee' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Fee is required',
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'jenis'         => FILTER_SANITIZE_STRING, 
            'id_kategori'   => FILTER_SANITIZE_NUMBER_INT, 
            'fee'            => FILTER_SANITIZE_NUMBER_INT, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $mdata=array(
            "jenis"         => $data->jenis,
            "id_kategori"   => $data->id_kategori,
            "fee"           => $data->fee,
            "created_at"    => date("y-m-d H:i:s")
        );   

        $result=$this->penjahit->insert_fee($mdata);
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

    public function updatepenjahit_fee(){
        $validation = $this->validation;
        $validation->setRules([
            'fee' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Fee is required',
                ]
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'fee'            => FILTER_SANITIZE_NUMBER_INT, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        
        $jenis          = $this->request->getGet('jenis', FILTER_SANITIZE_STRING);
        $id_kategori    = $this->request->getGet('id_kategori', FILTER_SANITIZE_NUMBER_INT);
        
        $mdata=array(
            "fee"           => $data->fee,
            "updated_at"    => date("y-m-d H:i:s")
        );   

        $where=array(
            "jenis"         => $jenis,
            "id_kategori"   => $id_kategori
        );
        $result=$this->penjahit->update_fee($where, $mdata);
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

    public function deletefee_penjahit(){
        $jenis          = $this->request->getGet('jenis', FILTER_SANITIZE_STRING);
        $id_kategori    = $this->request->getGet('id_kategori', FILTER_SANITIZE_NUMBER_INT);

        $where=array(
            "jenis"         => $jenis,
            "id_kategori"   => $id_kategori
        );

        $result = $this->penjahit->hapusfee($where);
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
