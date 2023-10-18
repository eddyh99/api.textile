<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use Exception;


class Mdl_bahanbaku extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT a.namabahan, a.satuan, x.harga, x.tanggal 
            FROM bahanbaku a INNER JOIN 
            (SELECT a.harga, a.tanggal ,a.id_bahan 
                FROM bahan_harga a INNER JOIN (SELECT MAX(tanggal) as tanggal,id_bahan 
                FROM bahan_harga GROUP BY id_bahan) x
             ON a.id_bahan=x.id_bahan AND a.tanggal=x.tanggal) x ON a.id=x.id_bahan WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function getby_bahanid($id){
        $sql    = "SELECT a.namabahan, a.satuan, x.harga, x.tanggal 
            FROM bahanbaku a INNER JOIN 
            (SELECT a.harga, a.tanggal ,a.id_bahan 
                FROM bahan_harga a INNER JOIN (SELECT MAX(tanggal) as tanggal,id_bahan 
                FROM bahan_harga GROUP BY id_bahan) x
            ON a.id_bahan=x.id_bahan AND a.tanggal=x.tanggal) x ON a.id=x.id_bahan WHERE id=? AND is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }
    

    public function add($data, $harga) {
        $bahan      = $this->db->table("bahanbaku");        
        $hargabahan = $this->db->table("bahan_harga");
        $this->db->transStart();
            $bahan->insert($data);
            $error[]=$this->db->error();
            $id     = $this->db->insertID();

            $harga["id_bahan"]=$id;
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
        $bahan      = $this->db->table("bahanbaku");        
        $hargabahan = $this->db->table("bahan_harga");
        $this->db->transStart();
            $bahan->where("id",$id);
            $bahan->update($data);
            $error[]=$this->db->error();

            $harga["id_bahan"]=$id;
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
        $kategori   = $this->db->table("bahanbaku");
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