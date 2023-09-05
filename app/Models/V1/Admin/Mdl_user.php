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
    
    public function getby_email($email,$bank_id) {
        $sql="SELECT `id`, `name`, `email`, `passwd`, `role`, `status`, `date_created`, `last_accessed`, `location` FROM `tbl_user` WHERE email=? AND bank_id=?  AND status='active'";
        $query=$this->db->query($sql, [$email,$bank_id])->getRow();
        if (!$query) {
	        $error=[
	            "code"       => "5051",
	            "error"      => "04",
	            "message"    => "Invalid email"
	        ];
            return (object) $error;
        }
        
        return $query;
    }
}