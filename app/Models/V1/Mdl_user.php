<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_user extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_single($uname,$passwd) {
        $sql="SELECT * FROM users WHERE uname=? AND passwd=?";
        $query=$this->db->query($sql,[$uname,$passwd]);
        if ($query->getNumRows()>0){
            return true;
        }
    }

    public function get_all(){
        $sql    = "SELECT uname, nama, role FROM users WHERE is_deleted='no'";
        $query  = $this->db->query($sql);
        return $query->getResult();
    }

    public function get_byuname($uname){
        $sql    = "SELECT uname, nama, role FROM users WHERE uname=? AND is_deleted='no'";
        $query  = $this->db->query($sql,$uname);
        return $query->getRow();
    }

    public function add($data) {
        $users      = $this->db->table("users");
        $sql        = $users->set($data)->getCompiledInsert()." ON DUPLICATE KEY UPDATE passwd=?, nama=?, role=?, is_deleted='no'";
        $query      = $this->db->query($sql,[ 
            $data["passwd"],
            $data["nama"],
            $data["role"],            
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

    public function updatedata($data, $uname){
        $users   = $this->db->table("users");
        $users->where("uname",$uname);
        if (!$users->update($data)){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
    }
    
    public function hapus($uname){
        $users   = $this->db->table("users");
        $users->where("email",$email);
        $users->set("is_deleted","yes");
        $users->set("updated_at",date("y-m-d H:i:s"));
        if (!$users->update()){
            $error=[
                "code"       => "5055",
                "error"      => "10",
                "message"    => $this->db->error()
            ];
            return (object) $error;
        }
        
    }
}