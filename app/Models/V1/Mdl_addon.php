<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use Exception;


class Mdl_addon extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT a.namaaddon, x.harga, x.tanggal 
            FROM addon a INNER JOIN 
            (SELECT a.harga, a.tanggal ,a.id_addon 
                FROM addon_harga a INNER JOIN (SELECT MAX(tanggal) as tanggal,id_addon 
                FROM addon_harga GROUP BY id_addon) x
             ON a.id_addon=x.id_addon AND a.tanggal=x.tanggal) x ON a.id=x.id_addon WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function getby_addonid($id){
        $sql    = "SELECT a.namaaddon, x.harga, x.tanggal 
            FROM addon a INNER JOIN 
            (SELECT a.harga, a.tanggal ,a.id_addon 
                FROM addon_harga a INNER JOIN (SELECT MAX(tanggal) as tanggal,id_addon 
                FROM addon_harga GROUP BY id_addon) x
            ON a.id_addon=x.id_addon AND a.tanggal=x.tanggal) x ON a.id=x.id_addon WHERE id=? AND is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }
    

    public function add($data, $harga) {
        $bahan      = $this->db->table("addon");        
        $hargabahan = $this->db->table("addon_harga");
        $this->db->transStart();
            $bahan->insert($data);
            $error[]=$this->db->error();
            $id     = $this->db->insertID();

            $harga["id_addon"]=$id;
            $hargabahan->insert($harga);
            $error[]=$this->db->error();
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $error
            ];
            return (object) $error;
        } 
    }

    public function updatedata($data, $harga, $id){
        $bahan      = $this->db->table("addon");        
        $hargabahan = $this->db->table("addon_harga");
        $this->db->transStart();
            $bahan->where("id",$id);
            $bahan->update($data);
            $error[]=$this->db->error();

            $harga["id_addon"]=$id;
            $hargabahan->insert($harga);
            $error[]=$this->db->error();
        $this->db->transComplete();
        if ($this->db->transStatus() === false) {
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $error
            ];
            return (object) $error;
        } 
    }
    
    public function hapus($id){
        $kategori   = $this->db->table("addon");
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