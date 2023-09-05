<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_swap extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function add($mdata=array()) {
        $swap=$this->db->table("tbl_member_swap");
        if (!$swap->insert($mdata)){
            $error=[
	            "code"       => "5055",
	            "error"      => "10",
	            "message"    => $this->db->error()
	        ];
            return (object) $error;
        }
    }
}