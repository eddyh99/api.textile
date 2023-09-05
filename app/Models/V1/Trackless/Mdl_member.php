<?php
namespace App\Models\Trackless;

use CodeIgniter\Model;
use Exception;


class Mdl_member extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all() {
        $sql="SELECT `id`, `bank_name`, `address`,email, `site`, `ucode_mwallet`, `status`, `date_created` FROM `bankmember` WHERE id<>'1'";
        $query = $this->db->query($sql)->getResult();
        if (!$query) {
	        $error=[
	            "code"       => "5053",
	            "error"      => "04",
	            "message"    => "Failed to get data"
	        ];
            return (object) $error;
        }
        
        return $query;
    }
    
    public function enable_bank($id) {
        if ($id==1){
	        $error=[
	            "code"       => "5053",
	            "error"      => "14",
	            "message"    => "Trackless Bank Can't be enabled/disabled"
	        ];
            return (object) $error;
        }
        
        $member=$this->db->table("bankmember");
        $mdata = array(
            "status" => "active",
        );
        $member->where("id", $id);
        $member->update($mdata);
        if ($this->db->affectedRows()==0){
	        $error=[
	            "code"       => "5053",
	            "error"      => "14",
	            "message"    => "Failed to disabled/reenable member"
	        ];
            return (object) $error;
        }
    }
    
    public function disable_bank($id) {
        if ($id==1){
	        $error=[
	            "code"       => "5053",
	            "error"      => "14",
	            "message"    => "Trackless Bank Can't be enabled/disabled"
	        ];
            return (object) $error;
        }
        $member=$this->db->table("bankmember");
        $mdata = array(
            "status" => "disabled",
        );
        $member->where("id", $id);
        $member->update($mdata);
         if ($this->db->affectedRows()==0){
            $error=[
                "code"       => "5053",
                "error"      => "14",
                "message"    => "Failed to disabled/reenable member"
            ];
            return (object) $error;
        }
   }    
}