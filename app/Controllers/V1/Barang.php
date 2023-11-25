<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_barang;

class Barang extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->barang    = new Mdl_barang();

	}

    public function getbarang(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->barang->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_barangid(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->barang->getby_barangid($id)
        ];
        return $this->respond($response);
    }

    public function add_barang(){
        $validation = $this->validation;
        $validation->setRules([
            'kategori' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Kategori is required',
                ]
            ],
            'namabarang' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama barang is required',
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
        $mdata=array(
            "namabarang"    => $data->namabarang,
            "kategori_id"   => $data->kategori,
            "design"        => $data->design,
            "minorder"      => $data->minorder,
            "pjg"           => $data->panjang,
            "lbr"           => $data->lebar,
            "tgi"           => $data->tinggi,
            "gsm"           => $data->gsm,
            "gr"            => $data->gr,
            "quality"       => $data->quality,
            "color"         => $data->color,
            "keterangan"    => $data->keterangan,
            "created_at"    => date("Y-m-d H:i:s")
        );

        $addon=$data->addon;

        $harga=array(
            "tanggal"   => date("Y-m-d H:i:s"),
            "harga"     => $data->harga
        );

        $result=$this->barang->add($mdata,$addon,$harga);
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

    public function update_barang(){
        $validation = $this->validation;
        $validation->setRules([
            'kategori' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Kategori is required',
                ]
            ],
            'namabarang' => [
                'rules'  => 'required',
                'errors' =>  [
                    'required'      => 'Nama barang is required',
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
        

        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $mdata=array(
            "namabarang"    => $data->namabarang,
            "kategori_id"   => $data->kategori,
            "design"        => $data->design,
            "minorder"      => $data->minorder,
            "pjg"           => $data->panjang,
            "lbr"           => $data->lebar,
            "tgi"           => $data->tinggi,
            "gsm"           => $data->gsm,
            "gr"            => $data->gr,
            "quality"       => $data->quality,
            "color"         => $data->color,
            "keterangan"    => $data->keterangan,
            "updated_at"    => date("Y-m-d H:i:s")
        );
        
        $addon=$data->addon;

        $harga=array(
            "tanggal"   => date("Y-m-d H:i:s"),
            "harga"     => $data->harga
        );
        $result=$this->barang->updatedata($mdata,$addon, $harga, $id);
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

    public function delete_barang(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $result = $this->barang->hapus($id);
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
