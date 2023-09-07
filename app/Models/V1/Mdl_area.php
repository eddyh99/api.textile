<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_area extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT a.id, a.area, (SELECT count(1) FROM sales WHERE area=a.id AND is_deleted='no') as sales, (SELECT count(1) FROM hotel WHERE area=a.id AND is_deleted='no') as hotel FROM area a WHERE a.is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_byareaid($id){
        $sql    = "SELECT a.id, a.area FROM area a WHERE a.id=? AND a.is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }
    

    public function add($data) {
        $area   = $this->db->table("area");
        $sql        = $area->set($data)->getCompiledInsert()." ON DUPLICATE KEY UPDATE area=?, is_deleted='no'";
        $query      = $this->db->query($sql,$data["area"]); 
        if (!$query){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }

    public function updatedata($data, $id){
        $area   = $this->db->table("area");
        $area->where("id",$id);
        if (!$area->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($id){
        $area   = $this->db->table("area");
        $area->where("id",$id);
        $area->set("is_deleted","yes");
        $area->set("updated_at",date("y-m-d H:i:s"));
        if (!$area->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}