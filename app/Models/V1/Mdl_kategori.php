<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_kategori extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT a.id, a.namakategori, (SELECT count(1) FROM barang WHERE kategori_id=a.id AND is_deleted='no') as barang FROM kategori a WHERE a.is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_bykategori($id){
        $sql    = "SELECT a.id, a.namakategori FROM kategori a WHERE a.id=? AND a.is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }
    

    public function add($data) {
        $kategori   = $this->db->table("kategori");
        $sql        = $kategori->set($data)->getCompiledInsert()." ON DUPLICATE KEY UPDATE namakategori=?, is_deleted='no'";
        $query      = $this->db->query($sql,$data["namakategori"]); 
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
        $kategori   = $this->db->table("kategori");
        $kategori->where("id",$id);
        if (!$kategori->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($id){
        $kategori   = $this->db->table("kategori");
        $kategori->where("id",$id);
        $kategori->set("is_deleted","yes");
        $kategori->set("updated_at",date("y-m-d H:i:s"));
        if (!$kategori->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}