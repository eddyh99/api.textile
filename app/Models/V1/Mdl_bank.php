<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_bank extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function getby_currency($currency) {
        $sql="SELECT currency, c_account_number, c_registered_name, c_routing_number, c_bank_name, c_bank_address, oc_registered_name, oc_iban, oc_bic, oc_bank_name, oc_bank_address FROM tbl_tracklessbank WHERE currency=?";
        $query=$this->db->query($sql, $currency)->getRow();
        if (!$query) {
	        $error=[
	            "code"       => "5054",
	            "error"      => "17",
	            "message"    => "Bank not found"
	        ];
            return (object) $error;
        }
        
        return $query;
    }
}