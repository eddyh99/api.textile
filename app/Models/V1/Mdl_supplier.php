<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use Exception;


class Mdl_supplier extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT id, nama, alamat, telp  FROM supplier  WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_byid($id){
        $sql    = "SELECT id, nama, alamat, telp FROM supplier  WHERE id=? AND is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }

    public function add($data) {
        $supplier   = $this->db->table("supplier");
        if (!$supplier->insert($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }

    public function updatedata($data, $id){
        $supplier   = $this->db->table("supplier");
        $supplier->where("id",$id);
        if (!$supplier->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($id){
        $supplier   = $this->db->table("supplier");
        $supplier->where("id",$id);
        $supplier->set("is_deleted","yes");
        $supplier->set("updated_at",date("y-m-d H:i:s"));
        if (!$supplier->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}