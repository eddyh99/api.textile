<?php
namespace App\Models\V1;

use CodeIgniter\Model;
use Exception;


class Mdl_retailer extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT email, nama, alamat, kota, telp, tgllahir FROM retailer WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_byemail($email){
        $sql    = "SELECT email, nama, alamat, kota, telp, tgllahir FROM retailer WHERE email=? AND is_deleted='no'";
        $query  = $this->db->query($sql,$email);
        return $query->getRow();
    }

    public function add($data) {
        $reseller   = $this->db->table("retailer");
        $sql        = $reseller->set($data)->getCompiledInsert()." ON DUPLICATE KEY UPDATE passwd=?, nama=?, alamat=?, kota=?, telp=?, is_deleted='no'";
        $query      = $this->db->query($sql,[ 
            $data["passwd"],
            $data["nama"],
            $data["alamat"],
            $data["kota"],
            $data["telp"],     
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

    public function updatedata($data, $email){
        $reseller   = $this->db->table("retailer");
        $reseller->where("email",$email);
        if (!$reseller->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($email){
        $reseller   = $this->db->table("retailer");
        $reseller->where("email",$email);
        $reseller->set("is_deleted","yes");
        $reseller->set("updated_at",date("y-m-d H:i:s"));
        if (!$reseller->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}