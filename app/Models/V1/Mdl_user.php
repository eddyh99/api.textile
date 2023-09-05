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
    
 
    

}