<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_penjahit extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT a.id, a.nama, a.alamat, a.kota, a.telp, a.tgllahir, a.komisi, b.area  FROM penjahit a INNER JOIN area b ON a.area=b.id  WHERE a.is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_byid($id){
        $sql    = "SELECT a.id, a.nama, a.alamat, a.kota, a.telp, a.tgllahir, a.komisi, b.area  FROM penjahit a INNER JOIN area b ON a.area=b.id  WHERE a.id=? AND a.is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }

    public function add($data) {
        $penjahit      = $this->db->table("penjahit");
        $sql        = $penjahit->set($data)->getCompiledInsert()." ON DUPLICATE KEY UPDATE nama=?, alamat=?, kota=?, telp=?, komisi=?, area=?, is_deleted='no'";
        $query      = $this->db->query($sql,[ 
            $data["nama"],
            $data["alamat"],
            $data["kota"],
            $data["telp"],
            $data["komisi"],
            $data["area"],
        ]);
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
        $penjahit   = $this->db->table("penjahit");
        $penjahit->where("id",$id);
        if (!$penjahit->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($id){
        $penjahit   = $this->db->table("penjahit");
        $penjahit->where("id",$id);
        $penjahit->set("is_deleted","yes");
        $penjahit->set("updated_at",date("y-m-d H:i:s"));
        if (!$penjahit->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}