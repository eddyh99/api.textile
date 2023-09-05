<?php
namespace App\Models\Trackless;

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
        $sql="SELECT currency, c_account_number as number_circuit, c_registered_name as name_circuit, c_routing_number as routing_circuit, c_transit as transit_circuit, c_bank_name as bankname_circuit, c_bank_address as address_circuit, oc_registered_name as name_outside, oc_iban as iban_outside, oc_bic as bic_outside, oc_bank_name as bankname_outside, oc_bank_address as address_outside, minimum FROM tbl_tracklessbank WHERE currency=?";
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
    
    public function setBank($mdata=array()){
        $tblbank=$this->db->table("tbl_tracklessbank");
        if (!$tblbank->replace($mdata)){
	        $error=[
	            "code"       => "5054",
	            "error"      => "17",
	            "message"    => "Bank not found"
	        ];
            return (object) $error;
        }
    }
    
    public function getTCbank(){
        $sql="SELECT id, bank_name, site, logo,logodark, is_comingsoon,is_public,member FROM bankmember WHERE id!=1";
        $query=$this->db->query($sql)->getResult();
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