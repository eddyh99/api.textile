<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_findme extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function countrylist(){
        $sql="SELECT * FROM tbl_country ORDER BY name ASC";
        $query=$this->db->query($sql);
        return $query->getResult();
    }
    
    public function statelist($country){
        $sql="SELECT * FROM tbl_state WHERE country_code=? ORDER BY state_name ASC";
        $query=$this->db->query($sql,[$country]);
        return $query->getResult();
    }

    public function citylist($country,$state){
        $sql="SELECT * FROM tbl_city WHERE country_code=? AND state_code=? ORDER BY city_name ASC";
        $query=$this->db->query($sql,[$country,$state]);
        return $query->getResult();
    }
    
    public function categorylist(){
        $sql="SELECT * FROM tbl_category";
        $query=$this->db->query($sql);
        return $query->getResult();
    }

    public function add($data,$category) {
        $tblbusiness=$this->db->table("tbl_findme");
        $tblfind=$this->db->table("tbl_findcategory");
        $this->db->transStart();
            try{
                if (!$tblbusiness->insert($data)){
                    throw new Exception("business already registered");
                }
                
                $id = $this->db->insertID();
                foreach ($category as $dt){
                    $mdata["id_findme"]=$id;
                    $mdata["id_category"]=$dt;
                    $tblfind->insert($mdata);
                }
            }catch(Exception $e) {
              $error=$e->getMessage();
            }
        $this->db->transComplete();
        
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            $error=[
                "code"      => "5055",
	            "error"     => "1060",
	            "message"   => $error
	        ];
            return (object)$error;
        }else {
            $this->db->transCommit();
            return TRUE;
        }
    }
    
    public function searchme($city,$category){
        $sql="SELECT business_name,googlemap, logo FROM tbl_findme a INNER JOIN tbl_findcategory b ON a.id=b.id_findme WHERE a.city_code=? AND b.id_category=? AND is_approve='yes'";
        $query=$this->db->query($sql,[$city,$category]);
        return $query->getResult();
    }
}