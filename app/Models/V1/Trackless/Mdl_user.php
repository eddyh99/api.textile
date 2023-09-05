<?php
namespace App\Models\Trackless;

use CodeIgniter\Model;
use Exception;


class Mdl_user extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function get_all($bank_id=NULL,$timezone=NULL,$currency) {
        if (empty($bank_id)){
            $user_tz = $timezone;
            $sql="SELECT m1.`id`,d.lastdigit, (SELECT (sum(x.amt) - sum(x.cost) - sum(x.fee) - sum(x.referral)) as balance
                FROM (
                    SELECT IFNULL(sum(amount),0) as amt, IFNULL(sum(pbs_cost+wise_cost),0) as cost, IFNULL(sum(fee),0) as fee, IFNULL(sum(referral_fee),0) as referral FROM tbl_member_topup WHERE id_member =m1.id AND currency='".$currency."' AND is_proses='yes'
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(amount)*-1,0) as amt, IFNULL(sum(pbs_cost+wise_cost),0) as cost, IFNULL(sum(fee),0) as fee, IFNULL(sum(referral_fee),0) as referral FROM tbl_member_tobank WHERE sender_id =m1.id AND currency='".$currency."'

                    UNION ALL
                    
                    SELECT 0 as amt, IFNULL(sum(pbs_cost+card_cost),0) as cost, IFNULL(sum(fee),0) as fee, 0 as referral FROM tbl_card WHERE id_member =m1.id AND currency='".$currency."'
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(amount)*-1,0) as amt, IFNULL(sum(pbs_sender_cost),0) as cost, IFNULL(sum(sender_fee),0) as fee, IFNULL(sum(referral_sender_fee),0) as referral FROM tbl_member_towallet WHERE sender_id =m1.id AND currency='".$currency."'
                    
                    UNION ALL

                    SELECT IFNULL(sum(amount),0) as amt, IFNULL(sum(pbs_receiver_cost),0) as cost, IFNULL(sum(receiver_fee),0) as fee, IFNULL(sum(referral_receiver_fee),0) as referral FROM tbl_member_towallet WHERE receiver_id =m1.id AND currency='".$currency."'
                    
                    UNION ALL

                    SELECT IFNULL(sum(receive),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_swap WHERE id_member =m1.id AND target_cur='".$currency."'

                    UNION ALL
                    
                    SELECT IFNULL(sum(amount-pbs_cost)*-1,0) as amt, IFNULL(sum(pbs_cost),0) as cost, fee, 0 as referral FROM tbl_member_swap WHERE id_member =m1.id AND currency='".$currency."'

                    UNION ALL

                    SELECT IFNULL(sum(referral_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral 
                        FROM tbl_member_topup t INNER JOIN tbl_member m ON t.id_member=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'  AND is_proses='yes'
                    
                    UNION ALL

                    SELECT IFNULL(sum(referral_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral 
                        FROM tbl_member_tobank t INNER JOIN tbl_member m ON t.sender_id=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(referral_receiver_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_towallet t INNER JOIN tbl_member m ON t.receiver_id=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'
                    
                    UNION ALL

                    SELECT IFNULL(sum(referral_sender_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_towallet t INNER JOIN tbl_member m ON t.sender_id=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'
                    
                    ) x
		        ) as balance ,m1.`ucode`, m1.`refcode`, m1.`email`, m1.`passwd`, m1.`status`, m1.`token`, m1.`id_referral`, convert_tz(m1.`date_created`, '".$this->server_tz."',?) AS date_created, convert_tz(m1.`last_accessed`, '".$this->server_tz."',?) AS last_login, m1.`location`, IFNULL(m2.ucode, c.ucode_mwallet) AS referral
		        FROM `tbl_member` m1 LEFT JOIN `tbl_member` m2 ON m1.`id_referral`=m2.`id` INNER JOIN  bankmember c ON m1.bank_id=c.id LEFT JOIN tbl_card d ON d.id_member=m1.id AND d.id_member=m2.id";
            $query = $this->db->query($sql, array($user_tz, $user_tz))->getResult();            
        }else{
            $smwallet="SELECT ucode_mwallet FROM bankmember WHERE id=?";
            $mwallet=$this->db->query($smwallet,$bank_id)->getRow()->ucode_mwallet;
            
            $user_tz = $timezone;
            $sql = "SELECT m1.`id`, d.lastdigit, (SELECT (sum(x.amt) - sum(x.cost) - sum(x.fee) - sum(x.referral)) as balance
                FROM (
                    SELECT IFNULL(sum(amount),0) as amt, IFNULL(sum(pbs_cost+wise_cost),0) as cost, IFNULL(sum(fee),0) as fee, IFNULL(sum(referral_fee),0) as referral FROM tbl_member_topup WHERE id_member =m1.id AND currency='".$currency."'  AND is_proses='yes'
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(amount)*-1,0) as amt, IFNULL(sum(pbs_cost+wise_cost),0) as cost, IFNULL(sum(fee),0) as fee, IFNULL(sum(referral_fee),0) as referral FROM tbl_member_tobank WHERE sender_id =m1.id AND currency='".$currency."'
                    
                    UNION ALL

                    SELECT 0 as amt, IFNULL(sum(pbs_cost+card_cost),0) as cost, IFNULL(sum(fee),0) as fee, 0 as referral FROM tbl_card WHERE id_member =m1.id AND currency='".$currency."'
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(amount)*-1,0) as amt, IFNULL(sum(pbs_sender_cost),0) as cost, IFNULL(sum(sender_fee),0) as fee, IFNULL(sum(referral_sender_fee),0) as referral FROM tbl_member_towallet WHERE sender_id =m1.id AND currency='".$currency."'
                    
                    UNION ALL

                    SELECT IFNULL(sum(amount),0) as amt, IFNULL(sum(pbs_receiver_cost),0) as cost, IFNULL(sum(receiver_fee),0) as fee, IFNULL(sum(referral_receiver_fee),0) as referral FROM tbl_member_towallet WHERE receiver_id =m1.id AND currency='".$currency."'
                    
                    UNION ALL

                    SELECT IFNULL(sum(receive),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_swap WHERE id_member =m1.id AND target_cur='".$currency."'

                    UNION ALL
                    
                    SELECT IFNULL(sum(amount-pbs_cost)*-1,0) as amt, IFNULL(sum(pbs_cost),0) as cost, fee, 0 as referral FROM tbl_member_swap WHERE id_member =m1.id AND currency='".$currency."'

                    UNION ALL

                    SELECT IFNULL(sum(referral_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral 
                        FROM tbl_member_topup t INNER JOIN tbl_member m ON t.id_member=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'  AND is_proses='yes'
                    
                    UNION ALL

                    SELECT IFNULL(sum(referral_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral 
                        FROM tbl_member_tobank t INNER JOIN tbl_member m ON t.sender_id=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(referral_receiver_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_towallet t INNER JOIN tbl_member m ON t.receiver_id=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'
                    
                    UNION ALL

                    SELECT IFNULL(sum(referral_sender_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_towallet t INNER JOIN tbl_member m ON t.sender_id=m.id WHERE m.id_referral =m1.id AND currency='".$currency."'
                    
                    ) x
		        ) as balance , m1.`ucode`, m1.`refcode`, m1.`email`, m1.`passwd`, m1.`status`, m1.`token`, m1.`id_referral`, convert_tz(m1.`date_created`, '".$this->server_tz."', ?) AS date_created, convert_tz(m1.`last_accessed`, '".$this->server_tz."', ?) AS last_login, m1.`location`, IFNULL(m2.ucode, ?) AS referral FROM `tbl_member` m1 LEFT JOIN `tbl_member` m2 ON m1.`id_referral`=m2.`id` LEFT JOIN tbl_card d ON d.id_member=m1.id AND d.id_member=m2.id WHERE m1.bank_id=?";
            $query = $this->db->query($sql, array($user_tz, $user_tz,$mwallet, $bank_id))->getResult();
        }
        return $query;
    }
    
    public function activate($id) {
        $member=$this->db->table("tbl_member");
        $mdata = array(
            "token" => NULL,
            "status" => "active",
            );
        $member->where("id", $id);
        $member->where("status", "new");
        $member->update($mdata);
        if ($this->db->affectedRows()==0){
	        $error=[
	            "code"       => "5053",
	            "error"      => "15",
	            "message"    => "Failed to activate member"
	        ];
            return (object) $error;
        }
    }
    
    public function change_password($id, $new_pass) {
        $member=$this->db->table("tbl_member");
        $mdata = array(
            "passwd" => $new_pass,
            );
        $member->where("id", $id);
        $member->update($mdata);
        if ($this->db->affectedRows()==0){
	        $error=[
	            "code"       => "5053",
	            "error"      => "16",
	            "message"    => "Failed change member's password/Use same password"
	        ];
            return (object) $error;
        }
    }
    
    public function enable_member($id) {
        $member=$this->db->table("tbl_member");
        $mdata = array(
            "token" => NULL,
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
    
    public function disable_member($id) {
        $member=$this->db->table("tbl_member");
        $mdata = array(
            "token" => NULL,
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