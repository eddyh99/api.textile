<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_card extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_single($userid) {
        $sql="SELECT * FROM tbl_card WHERE id_member=? AND card_id IS NOT NULL AND (status='active' OR status='new')";
        $query=$this->db->query($sql,[$userid]);
        if ($query->getNumRows()>0){
            $result=$query->getRow();
            return (object) array(
                    "card_id"       => $result->card_id,
                    "account_id"    => $result->account_id,
                    "status"        => $result->status
                );
        }else{
            return (object) array("card"=>"unavailable");
        }
    }
    
    public function check_account($userid){
        $sql="SELECT * FROM tbl_card WHERE id_member=? AND card_id IS NULL AND status='active'";
        $query=$this->db->query($sql,[$userid]);
        if ($query->getNumRows()>0){
            return (object) array(
                    "account_id"    => $query->getRow()->account_id,
                    "insert_id"     => $query->getRow()->id
                );
        }else{
            return false;
        }
    }
    
    public function getBank($currency){
        $sql="SELECT * FROM tbl_cardbank WHERE currency=?";
        $query=$this->db->query($sql,[$currency]);
        return $query->getRow();
    }
    
    public function add($data=array()){
        $tblcard=$this->db->table("tbl_card");
        if (!$tblcard->insert($data)) {
             $error=[
    	            "code"       => "5055",
    	            "error"      => "10",
    	            "message"    => $this->db->error()
    	        ];
             return (object) $error;
        }
        return $this->db->insertID();
    }
    
    public function updatecard($mcard,$id){
        $tblcard=$this->db->table("tbl_card");
        $tblcard->where("id",$id);
        if (!$tblcard->update($mcard)){
            $error=[
	            "code"       => "5055",
	            "error"      => "10",
	            "message"    => $this->db->error()
	        ];
            return (object) $error;
        }
    }
}