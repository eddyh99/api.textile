<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use Exception;


class Mdl_barang extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT a.*, GROUP_CONCAT(c.namaaddon) as addon
                FROM barang a INNER JOIN barang_addon b ON a.id=b.id_barang 
                INNER JOIN addon c ON b.id_addon=c.id 
                WHERE a.is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function getby_barangid($id){
        $sql    = "SELECT a.*, GROUP_CONCAT(c.namaaddon) as addon 
        FROM barang a INNER JOIN barang_addon b ON a.id=b.id_barang 
        INNER JOIN addon c ON b.id_addon=c.id 
        WHERE a.is_deleted='no' AND a.id=?";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }
    

    public function add($data, $addon, $harga) {
        $barang      = $this->db->table("barang");        
        $barangharga = $this->db->table("barang_harga");
        $barangaddon = $this->db->table("barang_addon");
        $this->db->transStart();
            $barang->insert($data);
            $error[]=$this->db->error();
            $id     = $this->db->insertID();

            $addonbrg=array();
            foreach($addon as $dt){
                $temp["id_barang"]  = $id;
                $temp["id_addon"]   = $dt->id_addon;
                $temp["jumlah"]     = $dt->jumlah;
                array_push($addonbrg, $temp);
            }
            $barangaddon->insertBatch($addonbrg);

            $harga["id_barang"]=$id;
            $barangharga->insert($harga);
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

    public function updatedata($data, $addon, $harga, $id){
        $barang      = $this->db->table("barang");        
        $barangharga = $this->db->table("barang_harga");
        $barangaddon = $this->db->table("barang_addon");
        $this->db->transStart();
            $barang->where("id",$id);
            $barang->update($data);
            $error[]=$this->db->error();

            $barangaddon->where("id_barang",$id);
            $barangaddon->delete();

            $addonbrg=array();
            foreach($addon as $dt){
                $temp["id_barang"]  = $id;
                $temp["id_addon"]   = $dt->id_addon;
                $temp["jumlah"]     = $dt->jumlah;
                array_push($addonbrg, $temp);
            }
            $barangaddon->insertBatch($addonbrg);

            $harga["id_barang"]=$id;
            $barangharga->insert($harga);
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
        $kategori   = $this->db->table("barang");
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