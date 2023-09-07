<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_hotel extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_allcphotel(){
        $sql    = "SELECT id, nama, telp, tgllahir, komisi FROM cphotel WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_bycpid($id){
        $sql    = "SELECT id, nama, telp, tgllahir, komisi FROM cphotel WHERE id=? AND is_deleted='no'";
        $query  = $this->db->query($sql,$id);
        return $query->getRow();
    }
    

    public function addcphotel($data) {
        $hotel      = $this->db->table("cphotel");        
        if (!$hotel->insert($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }

    public function updatedatacphotel($data, $id){
        $hotel   = $this->db->table("cphotel");
        $hotel->where("id",$id);
        if (!$hotel->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapuscphotel($id){
        $hotel   = $this->db->table("cphotel");
        $hotel->where("id",$id);
        $hotel->set("is_deleted","yes");
        $hotel->set("updated_at",date("y-m-d H:i:s"));
        if (!$hotel->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    public function get_all(){
        $sql    = "SELECT a.id, nama, alamat, kota, telp, b.area
                    FROM hotel a INNER JOIN area b
                    ON a.area=b.id                    
                    WHERE a.is_deleted='no'";
        $query  = $this->db->query($sql);
        $data   = $query->getResult();
        foreach($data as $dt){
            $id     = $dt->id;
            $sqlcp  = "SELECT nama, telp FROM hotel_kontak a INNER JOIN cphotel b ON a.cp_id=b.id WHERE hotel_id=?";
            $res    = $this->db->query($sqlcp,$id);
            $dt->cphotel=$res->getResult();
        }
        return $data;
    }

    public function get_byhotelid($id){
        $sql    = "SELECT a.id, nama, alamat, kota, telp, a.area as areaid, b.area
                    FROM hotel a INNER JOIN area b
                    ON a.area=b.id                    
                    WHERE a.is_deleted='no' AND a.id=?";
        $query  = $this->db->query($sql,$id);
        $data   = $query->getRow();

        $sqlcp  = "SELECT a.cp_id, nama, telp FROM hotel_kontak a INNER JOIN cphotel b ON a.cp_id=b.id WHERE hotel_id=?";
        $res    = $this->db->query($sqlcp,$id);
        $data->cphotel = $res->getResult();
        return $data;
    }
    

    public function add($data,$cphotel) {
        $hotel      = $this->db->table("hotel");
        $kontak     = $this->db->table("hotel_kontak");
        $this->db->transStart();
            $hotel->insert($data);
            $error[]=$this->db->error();
            $id=$this->db->insertID();
            $datacp=array();
            foreach ($cphotel as $dt){
                $temp["hotel_id"]   = $id;
                $temp["cp_id"]      = $dt->cp_id;
                array_push($datacp, $temp);
            }
            $kontak->insertBatch($datacp);
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

    public function updatedata($data, $cphotel,$id){
        $hotel      = $this->db->table("hotel");
        $kontak     = $this->db->table("hotel_kontak");
        $this->db->transStart();
            $hotel->where("id",$id);
            $hotel->update($data);
            $error[]=$this->db->error();
    
            $kontak->where("hotel_id",$id);
            $kontak->delete();
            $error[]=$this->db->error();

            $datacp=array();
            foreach ($cphotel as $dt){
                $temp["hotel_id"]   = $id;
                $temp["cp_id"]      = $dt->cp_id;
                array_push($datacp, $temp);
            }
            $kontak->insertBatch($datacp);
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
        $hotel   = $this->db->table("hotel");
        $hotel->where("id",$id);
        $hotel->set("is_deleted","yes");
        $hotel->set("updated_at",date("y-m-d H:i:s"));
        if (!$hotel->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}