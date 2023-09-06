<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_sales extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all(){
        $sql    = "SELECT nama, alamat, kota, telp, tgllahir, komisi, area FROM sales WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function add($data) {
        $sales      = $this->db->table("sales");
        $sql        = $sales->set($data)->getCompiledInsert()." ON DUPLICATE KEY UPDATE nama=?, alamat=?, kota=?, telp=?, komisi=?, area=?, is_deleted='no'";
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
        $sales   = $this->db->table("sales");
        $sales->where("id",$id);
        if (!$sales->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($id){
        $sales   = $this->db->table("sales");
        $sales->where("id",$id);
        $sales->set("is_deleted","yes");
        $sales->set("updated_at",date("y-m-d H:i:s"));
        if (!$sales->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
 
    

}