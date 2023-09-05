<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_wallet extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function topup_wallet($data=array()){
        $topup=$this->db->table("tbl_member_topup");
        if (!$topup->insert($data)){
            $error=[
	            "code"       => "5055",
	            "error"      => "10",
	            "message"    => $this->db->error()
	        ];
            return (object) $error;
        }  
    }
    
    public function get_balance($userid=NULL,$currency=NULL) {
        $sql = "SELECT sum(x.amt) as amt, sum(x.cost) as cost, sum(x.fee) as fee, sum(x.referral) as referral
                FROM (
                    SELECT IFNULL(sum(amount),0) as amt, IFNULL(sum(pbs_cost+wise_cost),0) as cost, IFNULL(sum(fee),0) as fee, IFNULL(sum(referral_fee),0) as referral FROM tbl_member_topup WHERE id_member =? AND currency=? AND is_proses='yes'
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(amount)*-1,0) as amt, IFNULL(sum(pbs_cost+wise_cost),0) as cost, IFNULL(sum(fee),0) as fee, IFNULL(sum(referral_fee),0) as referral FROM tbl_member_tobank WHERE sender_id =? AND currency=?
                    
                    UNION ALL

                    SELECT 0 as amt, IFNULL(sum(pbs_cost+card_cost),0) as cost, IFNULL(sum(fee),0) as fee, 0 as referral FROM tbl_card WHERE id_member =? AND currency=?
                    
                    UNION ALL
                    
                    SELECT IFNULL(sum(amount)*-1,0) as amt, IFNULL(sum(pbs_sender_cost),0) as cost, IFNULL(sum(sender_fee),0) as fee, IFNULL(sum(referral_sender_fee),0) as referral FROM tbl_member_towallet WHERE sender_id =? AND currency=?
                    
                    UNION ALL

                    SELECT IFNULL(sum(amount),0) as amt, IFNULL(sum(pbs_receiver_cost),0) as cost, IFNULL(sum(receiver_fee),0) as fee, IFNULL(sum(referral_receiver_fee),0) as referral FROM tbl_member_towallet WHERE receiver_id =? AND currency=?
                    
                    UNION ALL

                    SELECT IFNULL(sum(receive),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_swap WHERE id_member =? AND target_cur=?

                    UNION ALL
                    
                    SELECT IFNULL(sum(amount-pbs_cost)*-1,0) as amt, IFNULL(sum(pbs_cost),0) as cost, fee, 0 as referral FROM tbl_member_swap WHERE id_member =? AND currency=?

                    UNION ALL

                    SELECT IFNULL(sum(referral_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral 
                        FROM tbl_member_topup t INNER JOIN tbl_member m ON t.id_member=m.id WHERE m.id_referral =? AND currency=? AND is_proses='yes'
                    
                    UNION ALL

                    SELECT IFNULL(sum(referral_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral 
                        FROM tbl_member_tobank t INNER JOIN tbl_member m ON t.sender_id=m.id WHERE m.id_referral =? AND currency=?
                    
                    UNION ALL
                    

                    SELECT IFNULL(sum(referral_receiver_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_towallet t INNER JOIN tbl_member m ON t.receiver_id=m.id WHERE m.id_referral =? AND currency=?
                    
                    UNION ALL

                    SELECT IFNULL(sum(referral_sender_fee),0) as amt, 0 as cost, 0 as fee, 0 as referral FROM tbl_member_towallet t INNER JOIN tbl_member m ON t.sender_id=m.id WHERE m.id_referral =? AND currency=?
                    
                    ) x
            ";
        $query = $this->db->query($sql, [
            $userid,$currency,
            $userid,$currency,
            $userid,$currency,
            $userid,$currency,
            $userid,$currency,
            $userid,$currency,
            $userid,$currency,
            $userid,$currency,
            $userid,$currency, 
            $userid,$currency,
            $userid,$currency])->getRow();
        if ($query) {
            $balance=0;
            $balance = $query->amt - $query->cost - $query->fee - $query->referral;
            return $balance;
        } else {
            return 0;
        }

    }
    
    public function wallet2bank($data=array()){
        $walletbank=$this->db->table("tbl_member_tobank");
        if (!$walletbank->insert($data)){
            $error=[
	            "code"       => "5055",
	            "error"      => "10",
	            "message"    => "Something wrong, please try again later"
	        ];
            return (object) $error;
        }        
    }
    
    public function wallet2wallet($data=array()){
        $wallet2wallet=$this->db->table("tbl_member_towallet");
        if (!$wallet2wallet->insert($data)){
            $error=[
	            "code"       => "5055",
	            "error"      => "10",
	            "message"    => "Something wrong, please try again later"
	        ];
            return (object) $error;
        }        
    }
    
    public function get_history($userid=NULL,$currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        $sql="SELECT amount, ket, fee, date_created, causal FROM (
            SELECT amount, 'topup' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created,'' as causal FROM tbl_member_topup WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?  AND is_proses='yes'
              UNION ALL
              SELECT amount, 'Withdraw' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created,'' as causal FROM tbl_member_tobank WHERE sender_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='no'
              UNION ALL
              SELECT amount, 'Topup Card' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created,'' as causal FROM tbl_member_tobank WHERE sender_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='yes'
              UNION ALL
              SELECT 0 as amount, 'Request Card' as ket, (fee+pbs_cost+card_cost) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created,'' as causal FROM tbl_card WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?
              UNION ALL
              SELECT amount, 'Send' as ket, (sender_fee+pbs_sender_cost+referral_sender_fee) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created, causal FROM tbl_member_towallet WHERE sender_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
              UNION ALL
              SELECT amount, 'Receive' as ket, (receiver_fee+pbs_receiver_cost+referral_receiver_fee) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created, causal FROM tbl_member_towallet WHERE receiver_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
              UNION ALL
              SELECT amount, 'Swap' as ket, (pbs_cost+fee) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created,'' as causal FROM tbl_member_swap WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?
              UNION ALL
              SELECT receive, 'Swap Receive' as ket, 0 as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created,'' as causal FROM tbl_member_swap WHERE id_member=? AND target_cur=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
              UNION ALL
              SELECT referral_fee as amount, 'Referral' as ket, 0 as fee,DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 1 second) AS date_created,'' as causal FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_proses='yes'
              UNION ALL
              SELECT referral_receiver_fee as amount, 'Referral' as ket, 0 as fee,DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 1 second) AS date_created,'' as causal FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
              UNION ALL
              SELECT referral_sender_fee as amount, 'Referral' as ket, 0 as fee,DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 1 second) AS date_created,'' as causal FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
              UNION ALL
              SELECT referral_fee as amount, 'Referral' as ket, 0 as fee,DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 1 second) AS date_created,'' as causal FROM tbl_member_tobank a  INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?) x ORDER BY date_created DESC
        ";
        $query=$this->db->query($sql,
        [
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
            $timezone,$userid,$currency,$timezone,$awal,$akhir
        ]);
        
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();
        }
        
    }

}