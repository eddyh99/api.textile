<?php
namespace App\Controllers\V1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

use App\Models\V1\Mdl_area;

class Area extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->area    = new Mdl_area();

	}

    public function getArea(){
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->area->get_all()
        ];
        return $this->respond($response);
    }

    public function getby_areaid(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $response=[
            "code"      => "200",
            "error"     => NULL,
            "message"   => $this->area->get_byareaid($id)
        ];
        return $this->respond($response);
    }

    public function add_area(){
        $validation = $this->validation;
        $validation->setRules([					
					'area' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama area is required',
					    ]
					],

            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'area'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $mdata=array(
            "area"      => $data->area,
            "created_at"=> date("y-m-d H:i:s")
        );

        $result=$this->area->add($mdata);
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

    public function update_area(){
        $validation = $this->validation;
        $validation->setRules([					
					'area' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Nama Area is required',
					    ]
					],
            ]);
        
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
        
	    $data   = $this->request->getJSON();
        $filters = array(
            'area'    => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $mdata=array(
            "area"  => $data->area            
        );
        $result=$this->area->updatedata($mdata,$id);
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

    public function delete_area(){
        $id  = $this->request->getGet('id', FILTER_SANITIZE_NUMBER_INT);
        $result = $this->area->hapus($id);
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
