<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_member extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all($currency = "USD") {
        $user = $this->session->userdata("logged_status");
        $query = NULL;
        if ($currency == "USD") {
            if ($user && !empty($user)) {
                $user_tz = $user["time_location"];
                $sql = "SELECT m1.`id`, m1.`ucode`, m1.`refcode`, m1.`email`, m1.`passwd`, m1.`status`, m1.`token`, m1.`id_referral`, convert_tz(m1.`date_created`, '".$this->server_tz."', ?) AS date_created, convert_tz(m1.`last_accessed`, '".$this->server_tz."', ?) AS last_accessed, m1.`location`, m1.`activated`, IFNULL(m2.ucode, 'm3rc4n73') AS referral FROM `tbl_member` m1 LEFT JOIN `tbl_member` m2 ON m1.`id_referral`=m2.`id`";
                $query = $this->db->query($sql, array($user_tz, $user_tz));
                if ($query && $query->num_rows()>0){
                    return $query->result_array();
                }else{
                    return array();
                }
            } else {
                $sql = "SELECT m1.`id`, m1.`ucode`, m1.`refcode`, m1.`email`, m1.`passwd`, m1.`status`, m1.`token`, m1.`id_referral`, m1.date_created, m1.last_accessed, m1.`location`, m1.`activated`, IFNULL(m2.ucode, 'm3rc4n73') AS referral FROM `tbl_member` m1 LEFT JOIN `tbl_member` m2 ON m1.`id_referral`=m2.`id`";
                $query = $this->db->query($sql);
                if ($query && $query->num_rows()>0){
                    return $query->result_array();
                }else{
                    return array();
                }
            } 
        } else {
            if ($user && !empty($user)) {
                $user_tz = $user["time_location"];
                $sql = "SELECT m1.`id`, m1.`ucode`, m1.`refcode`, m1.`email`, m1.`passwd`, m1.`status`, m1.`token`, m1.`id_referral`, convert_tz(m1.`date_created`, '".$this->server_tz."', ?) AS date_created, convert_tz(m1.`last_accessed`, '".$this->server_tz."', ?) AS last_accessed, m1.`location`, m1.`activated`, IFNULL(m2.ucode, 'm3rc4n73') AS referral FROM `tbl_member` m1 LEFT JOIN `tbl_member` m2 ON m1.`id_referral`=m2.`id` INNER JOIN tbl_member_currency c ON m1.id=c.id_member WHERE c.currency=?";
                $query = $this->db->query($sql, array($user_tz, $user_tz, $currency));
                if ($query && $query->num_rows()>0){
                    return $query->result_array();
                }else{
                    return array();
                }
            } else {
                $sql = "SELECT m1.`id`, m1.`ucode`, m1.`refcode`, m1.`email`, m1.`passwd`, m1.`status`, m1.`token`, m1.`id_referral`, m1.date_created, m1.last_accessed, m1.`location`, m1.`activated`, IFNULL(m2.ucode, 'm3rc4n73') AS referral FROM `tbl_member` m1 LEFT JOIN `tbl_member` m2 ON m1.`id_referral`=m2.`id` INNER JOIN tbl_member_currency c ON m1.id=c.id_member WHERE c.currency=?";
                $query = $this->db->query($sql, array($currency));
                if ($query && $query->num_rows()>0){
                    return $query->result_array();
                }else{
                    return array();
                }
            }
        }
		

        if ($query && $query->num_rows()>0){
            return $query->result_array();
        }else{
            return array();
        }
    }
    
    public function get_active($currency = "USD") {
        $user = $this->session->userdata("logged_status");
        $query = NULL;
        if ($currency == "USD") {
            if ($user && !empty($user)) {
                $user_tz = $user["time_location"];
                $sql = "SELECT `id`, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, convert_tz(`date_created`, '".$this->server_tz."', ?) AS date_created, convert_tz(`last_accessed`, '".$this->server_tz."', ?) AS last_accessed, `location`, `activated` FROM `tbl_member` WHERE status='active'";
                $query = $this->db->query($sql, array($user_tz, $user_tz));
            } else {
                $sql = "SELECT `id`, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, date_created, last_accessed, `location`, `activated` FROM `tbl_member` WHERE status='active'";
                $query = $this->db->query($sql);
            }
        } else {
            if ($user && !empty($user)) {
                $user_tz = $user["time_location"];
                $sql = "SELECT m.`id`, m.`ucode`, m.`refcode`, m.`email`, m.`passwd`, m.`status`, m.`token`, m.`id_referral`, convert_tz(`m.date_created`, '".$this->server_tz."', ?) AS date_created, convert_tz(`m.last_accessed`, '".$this->server_tz."', ?) AS last_accessed, m.`location`, m.`activated` FROM `tbl_member` m INNER JOIN `tbl_member_currency` c on m.id=c.id_member WHERE c.currency = ? AND m.status='active'";
                $query = $this->db->query($sql, array($user_tz, $user_tz, $currency));
            } else {
                $sql = "SELECT m.`id`, m.`ucode`, m.`refcode`, m.`email`, m.`passwd`, m.`status`, m.`token`, m.`id_referral`, m.`date_created`, m.`last_accessed`, m.`location`, m.`activated` FROM `tbl_member` m INNER JOIN `tbl_member_currency` c on m.id=c.id_member WHERE c.currency = ? AND m.status='active'";
                $query = $this->db->query($sql, array($currency));
            }
        }
		

        if ($query &&  $query->num_rows()>0){
            return $query->result_array();
        }else{
            return array();
        }
    }
    
    public function getby_id($id) {
        $sql = "SELECT `id`, `bank_id`, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, `location`, `date_created`, `last_accessed` FROM `tbl_member` WHERE id=?";
	    $query = $this->db->query($sql, $id)->getRow();
        if (!$query) {
	        $error=[
	            "code"       => "5051",
	            "error"      => "22",
	            "message"    => "Invalid user ID, please check it again"
	        ];
            return (object) $error;
        }
        
        return $query;

    }
    
    public function getby_causal($causal){
        $sql="SELECT b.bank_id,b.id FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE causal=?";
        $query = $this->db->query($sql, $causal)->getRow();
        return $query;
    }
    
    public function getby_ucode($ucode, $userid=NULL) {
        if (empty($userid)){
            $sql = "SELECT `id`, `bank_id`, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, `location`, `date_created`, `last_accessed` FROM `tbl_member` WHERE `ucode`=?";
            $query=$this->db->query($sql,[$ucode])->getRow();
        }else{
            $sql = "SELECT `id`, `bank_id`, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, `location`, `date_created`, `last_accessed` FROM `tbl_member` WHERE `ucode`=? AND id<>?";
    	    $query = $this->db->query($sql, array($ucode,$userid))->getRow();
        }
        if (!$query) {
	        $error=[
	            "code"       => "5051",
	            "error"      => "22",
	            "message"    => "Invalid unique code, please check it again"
	        ];
            return (object) $error;
        }
        
        return $query;
    }
    
    public function getby_refcode($refcode,$bank_id) {
        $sql = "SELECT `id`, bank_id, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, date_created, last_accessed, `location` FROM `tbl_member` WHERE `refcode`=? AND bank_id=?";
	    $query = $this->db->query($sql, array($refcode,$bank_id))->getRow();

        if (!$query) {
	        $error=[
	            "code"       => "5051",
	            "error"      => "01",
	            "message"    => "Invalid referral code or not found, please check it again"
	        ];
            return (object) $error;
        }
        
        return $query;
    }
    
    public function getby_email($email,$bank_id) {
        $sql = "SELECT `id`, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, date_created, last_accessed, `location` FROM `tbl_member` WHERE `email`=? AND bank_id=?";
        $query = $this->db->query($sql, array($email,$bank_id))->getRow();

        if (!$query) {
	        $error=[
	            "code"       => "5051",
	            "error"      => "04",
	            "message"    => "Unknown user email"
	        ];
            return (object) $error;
        }
        
        return $query;
    }
    
    public function getby_token($token) {
        $sql = "SELECT `id`, `ucode`, `refcode`, `email`, `passwd`, `status`, `token`, `id_referral`, date_created, last_accessed, `location` FROM `tbl_member` WHERE `token`=?";
        $query = $this->db->query($sql, $token)->getRow();

		if (!$query) {
	        $error=[
	            "code"       => "5051",
	            "error"      => "02",
	            "message"    => "Invalid Token/expired token"
	        ];
            return (object) $error;
        }
        
        return $query;
    }
    
    public function add($data=array()) {
        $tblmember=$this->db->table("tbl_member");
        $this->db->transStart();
            try{
                if (!$tblmember->insert($data)){
                    throw new Exception("Email already used");
                }
                
                $id = $this->db->insertID();
                $mdata = array(
                    "ucode" => $this->generate_ucode($id),
                    "refcode" => $this->generate_refcode($id),
                    "token" => $this->generate_token($id),
                    "date_created" => date("Y-m-d H:i:s"),
                    );
            
                    $tblmember->where("id", $id);
                    $tblmember->update($mdata);
            }catch(Exception $e) {
              $error=$e->getMessage();
            }
        $this->db->transComplete();
        
        if ($this->db->transStatus() === false) {
            $this->db->transRollback();
            $error=[
                "code"      => "1060",
	            "error"     => "1060",
	            "message"   => $error
	        ];
            return (object)$error;
        }else {
            $this->db->transCommit();
            return (object)$mdata;
        }
    }
    
    private function generate_ucode($id) {
        require_once APPPATH . "ThirdParty/Hashids/HashidsInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Hashids.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/MathInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/Gmp.php";

        $hashids = new \Hashids\Hashids('', 8, 'abcdefhjkmnpqrtwxyz23478'); 
        return $hashids->encode($id, 7, 91);
    }
    
    private function generate_refcode($id) {
        require_once APPPATH . "ThirdParty/Hashids/HashidsInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Hashids.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/MathInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/Gmp.php";

        $hashids =  new \Hashids\Hashids('', 8, 'abcdefhjkmnpqrtwxyz23478'); 
        return $hashids->encode($id, 13, 243);
    }
    
    private function generate_token($id) {
        require_once APPPATH . "ThirdParty/Hashids/HashidsInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Hashids.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/MathInterface.php";
        require_once APPPATH . "ThirdParty/Hashids/Math/Gmp.php";

        $hashids =  new \Hashids\Hashids('', 48, 'abcdefghijklmnopqrstuvwxyz1234567890'); 
        return $hashids->encode($id, time(), rand()); 
    }
    
    public function activate($ucode) {
        $tblmember=$this->db->table("tbl_member");
        $mdata = array(
            "token" => NULL,
            "status" => "active",
            );
        $tblmember->where("ucode", $ucode);
        $tblmember->where("status", "new");
        $tblmember->update($mdata);
        if ($this->db->affectedRows()==0){
	        $error=[
	            "code"       => "5051",
	            "error"      => "03",
	            "message"    => "Activation failed, Invalid token"
	        ];
            return (object) $error;
        }
    }
    
    public function resetToken($email){
        $sql="SELECT id FROM tbl_member WHERE email=?";
        if (!$this->db->query($sql,$email)->getRow()){
            $error=[
	            "code"       => "5051",
	            "error"      => "07",
	            "message"    => "Member not found"
	        ];
            return (object) $error;
        }
        $id=$this->db->query($sql,$email)->getRow()->id;

        $member=$this->db->table("tbl_member");
        $token=$this->generate_token($id);
        
        $member->where('email', $email);
        $member->set("token",$token);
        $member->update();
        return $token;
        
    }
    
    public function change_password($mdata, $where) {
        $member=$this->db->table("tbl_member");
        $member->where($where);
        $member->update($mdata);
        if ($this->db->affectedRows()==0){
	        $error=[
	            "code"       => "5051",
	            "error"      => "08",
	            "message"    => "Failed to change password, please try again later"
	        ];
            return (object) $error;
        }
    }
    

}