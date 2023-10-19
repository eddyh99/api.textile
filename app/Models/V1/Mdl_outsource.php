<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use Exception;


class Mdl_outsource extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT id, nama, alamat, telp,keterangan  FROM outsource  WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_byid($id){
        $sql    = "SELECT id, nama, alamat, telp,keterangan FROM outsource  WHERE id=? AND is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }

    public function add($data) {
        $outsource   = $this->db->table("outsource");
        if (!$outsource->insert($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }

    public function updatedata($data, $id){
        $outsource   = $this->db->table("outsource");
        $outsource->where("id",$id);
        if (!$outsource->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($id){
        $outsource   = $this->db->table("outsource");
        $outsource->where("id",$id);
        $outsource->set("is_deleted","yes");
        $outsource->set("updated_at",date("y-m-d H:i:s"));
        if (!$outsource->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}