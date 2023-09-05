<?php
namespace App\Models\Trackless;

use CodeIgniter\Model;
use Exception;


class Mdl_business extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql="SELECT a.*,b.ucode FROM tbl_findme a INNER JOIN tbl_member b ON a.id_member=b.id ORDER BY is_approve='no' DESC";
        $query=$this->db->query($sql);
        if (!$query) {
	        $error=[
	            "code"       => "5054",
	            "error"      => "17",
	            "message"    => "Card Bank not found"
	        ];
            return (object) $error;
        }
        
        return $query->getResult();
    }
    
    public function approve_bisnis($business_id){
        $sql="UPDATE tbl_findme SET is_approve='yes' WHERE id=?";
        $query=$this->db->query($sql,[$business_id]);
        return ($this->db->affectedRows() != 1) ? false : true;
    }
    
    public function delete_bisnis($business_id){
        $sql="DELETE FROM tbl_findme WHERE id=?";
        $query=$this->db->query($sql,[$business_id]);
        return ($this->db->affectedRows() != 1) ? false : true;
    }
    
    public function get_category(){
        $sql="SELECT * FROM tbl_category";
        $query=$this->db->query($sql);
        return $query->getResult();
    }
    
    public function setCategory($mdata){
        $category=$this->db->table("tbl_category");
        if (!$category->insert($mdata)){
	        $error=[
	            "code"       => "5054",
	            "error"      => "14",
	            "message"    => "Failed to save category"
	        ];
            return (object) $error;
        }
    }
    
    public function updateCategory($where,$mdata){
        $category=$this->db->table("tbl_category");
        $category->where("id",$where);
        if (!$category->update($mdata)){
	        $error=[
	            "code"       => "5054",
	            "error"      => "14",
	            "message"    => "Failed to save category"
	        ];
            return (object) $error;
        }
    }
    
}