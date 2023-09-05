<?php
namespace App\Models\Trackless;

use CodeIgniter\Model;
use Exception;


class Mdl_currency extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }


    public function get_single($currency){
        $sql="SELECT currency,symbol FROM tbl_currency WHERE status='active' AND currency=?";
        $cur=$this->db->query($sql,[$currency])->getRow();
        return $cur;
    }
    

    public function get_all() {
        $sql = "SELECT `currency`, `symbol`, `name`, `status`,min_amt FROM `tbl_currency`";
        $query = $this->db->query($sql)->getResult();
        if ($query){
            return $query;
        }
    }
    

    public function enable($currency) {
        $tblcurrency=$this->db->table("tbl_currency");
        $tblcurrency->where("currency",$currency);
        $tblcurrency->set("status","active");
        if (!$tblcurrency->update()){
            $error=[
	            "code"       => "5052",
	            "error"      => "11",
	            "message"    => "Invalid Currency"
	        ];
            return (object) $error;
        }
    }
    
    public function disable($currency) {
        $tblcurrency=$this->db->table("tbl_currency");
        $tblcurrency->where("currency",$currency);
        $tblcurrency->set("status","disabled");
        if (!$tblcurrency->update()){
            $error=[
	            "code"       => "5052",
	            "error"      => "11",
	            "message"    => "Invalid Currency"
	        ];
            return (object) $error;
        }
    }    
}