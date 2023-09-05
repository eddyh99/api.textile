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


    public function get_all($bankid){
        $sql="SELECT currency,symbol FROM tbl_currency WHERE status='active'";
        $cur=$this->db->query($sql)->getResult();
        $mdata=array();
        foreach($cur as $dt){
            $sql="SELECT IFNULL(sum(amount),0) as amount FROM (
                    SELECT sum(fee) as amount FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.bank_id=? AND a.currency=? AND is_proses='yes'
                    UNION ALL
                    SELECT sum(fee) as amount FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND a.currency=?
                    UNION ALL
                    SELECT sum(fee) as amount FROM tbl_card a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.bank_id=? AND a.currency=?
                    UNION ALL
                    SELECT sum(sender_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND a.currency=?
                    UNION ALL
                    SELECT sum(receiver_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE b.bank_id=? AND a.currency=?
                    UNION ALL
                    SELECT sum(referral_fee) as amount FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=? AND is_proses='yes'
                    UNION ALL
                    SELECT sum(referral_fee) as amount FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=?
                    UNION ALL
                    SELECT sum(referral_sender_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=?
                    UNION ALL
                    SELECT sum(referral_receiver_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=?
                    UNION ALL
                    SELECT IFNULL(sum(amount+pbs_cost+wise_cost)*-1,0) as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id=? AND a.currency=? AND b.role='admin'
                    UNION ALL
                    SELECT IFNULL(sum(amount) *-1,0) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id=? AND a.currency=? AND b.role='admin'
                    UNION ALL
                    SELECT IFNULL(sum(receive),0) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id=? AND a.target_cur=? AND b.role='admin'
                ) x
            ";
            
            $result=$this->db->query($sql,[
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
                $bankid,$dt->currency,
            ])->getRow()->amount;
                
            $temp["currency"]=$dt->currency;
            $temp["symbol"]=$dt->symbol;
            $temp["amount"]=$result;
            array_push($mdata,$temp);
        }
        return (object)$mdata;
    }
    
    public function balance_bycurrency($bankid,$currency){
        $sql="SELECT IFNULL(sum(amount),0) as amount FROM (
                SELECT sum(fee) as amount FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.bank_id=? AND a.currency=? AND is_proses='yes'
                UNION ALL
                SELECT sum(fee) as amount FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND a.currency=?
                UNION ALL
                SELECT sum(fee) as amount FROM tbl_card a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.bank_id=? AND a.currency=?
                UNION ALL
                SELECT sum(sender_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND a.currency=?
                UNION ALL
                SELECT sum(receiver_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE b.bank_id=? AND a.currency=?
                UNION ALL
                SELECT sum(referral_fee) as amount FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=? AND is_proses='yes'
                UNION ALL
                SELECT sum(referral_fee) as amount FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=?
                UNION ALL
                SELECT sum(referral_sender_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=?
                UNION ALL
                SELECT sum(referral_receiver_fee) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE b.bank_id=? AND ISNULL(b.id_referral) AND a.currency=?
                UNION ALL
                SELECT IFNULL(sum(amount+pbs_cost+wise_cost)*-1,0) as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id=? AND a.currency=? AND b.role='admin'
                UNION ALL
                SELECT IFNULL(sum(amount) *-1,0) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id=? AND a.currency=? AND b.role='admin'
                UNION ALL
                SELECT IFNULL(sum(receive),0) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id=? AND a.target_cur=? AND b.role='admin'
            ) x
        ";
            
        $result=$this->db->query($sql,[
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
            $bankid,$currency,
        ])->getRow()->amount;
        return $result;
    }
    
    public function get_historybycurrency($currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL, $bank_id){
        $sql="SELECT amount, ket, fee, cost, referral, date_created FROM (
                  SELECT amount, CONCAT('topup ',ucode) as ket, fee, (pbs_cost+wise_cost) as cost, IF(ISNULL(b.id_referral), referral_fee,0) as referral, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND b.bank_id=? AND is_proses='yes'
                  UNION ALL
                  SELECT amount, CONCAT('Withdraw ',ucode) as ket, fee, (pbs_cost+wise_cost) as cost, IF(ISNULL(b.id_referral), referral_fee,0) as referral,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE  currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?  AND b.bank_id=? AND is_card='no'
                  UNION ALL
                  SELECT amount, CONCAT('Request Topup Card ',ucode) as ket, fee, (pbs_cost+wise_cost) as cost, IF(ISNULL(b.id_referral), referral_fee,0) as referral,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE  currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?  AND b.bank_id=? AND is_card='yes'
                  UNION ALL
                  SELECT '' as amount, CONCAT('Request Card from ', ucode) as ket, fee, (pbs_cost+card_cost+pbs_ship+ship_card) as cost,0 as referral,  convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_card a INNER JOIN tbl_member b ON a.id_member=b.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND b.bank_id=?
                  UNION ALL
                  SELECT amount, ket, sum(fee) as fee, sum(cost) as cost, sum(referral) as referral, date_created FROM (
                  SELECT a.id,amount, CONCAT('Transfer from ',b.ucode, ' to ', c.ucode) as ket, sender_fee as fee, pbs_sender_cost as cost, IF(ISNULL(b.id_referral),referral_sender_fee,0) as referral,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_member c ON a.receiver_id=c.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?  AND b.bank_id=?
                  UNION ALL
                  SELECT a.id, amount, CONCAT('Transfer from ',b.ucode, ' to ', c.ucode) as ket, receiver_fee as fee, pbs_receiver_cost as cost, IF(ISNULL(c.id_referral),referral_receiver_fee,0) as referral,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_member c ON a.receiver_id=c.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?  AND c.bank_id=?
                  ) xx GROUP BY id
              ) x ORDER BY date_created 
        ";
        
        $query=$this->db->query($sql,
        [
            $timezone,$currency,$timezone,$awal,$akhir,$bank_id,
            $timezone,$currency,$timezone,$awal,$akhir,$bank_id,
            $timezone,$currency,$timezone,$awal,$akhir,$bank_id,
            $timezone,$currency,$timezone,$awal,$akhir,$bank_id,
            $timezone,$currency,$timezone,$awal,$akhir,$bank_id,
            $timezone,$currency,$timezone,$awal,$akhir,$bank_id
        ]);
        
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
        
    }
    
    public function history_topup($currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL, $bank_id){
        $sql="SELECT amount, CONCAT('topup ',ucode) as ket, fee, (pbs_cost+wise_cost) as cost, IF(ISNULL(b.id_referral), referral_fee,0) as referral, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND b.bank_id=? AND is_proses='yes'";
        $query=$this->db->query($sql,[$timezone,$currency,$timezone,$awal,$akhir,$bank_id]);
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
    }
    
    public function history_wallet($currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL, $bank_id){
        $sql="SELECT ket, sum(sender_fee) as sender_fee, sum(receiver_fee) as receiver_fee,  sum(referral_sender_fee) as referral_sender_fee,sum(referral_receiver_fee) as referral_receiver_fee, sum(cost) as cost, date_created FROM (
                  SELECT a.id, CONCAT('Transfer from ',b.ucode, ' to ', c.ucode) as ket, sender_fee, 0 as receiver_fee,  IF(ISNULL(b.id_referral),referral_sender_fee,0) as referral_sender_fee, 0 as referral_receiver_fee, pbs_sender_cost as cost, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_member c ON a.receiver_id=c.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?  AND b.bank_id=?
                  UNION ALL
                  SELECT a.id,  CONCAT('Transfer from ',b.ucode, ' to ', c.ucode) as ket, 0 as sender_fee, receiver_fee, 0 as referral_sender_fee,   IF(ISNULL(c.id_referral),referral_receiver_fee,0) as referral_receiver_fee,pbs_receiver_cost as cost, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_member c ON a.receiver_id=c.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?  AND c.bank_id=?
                  ) xx GROUP BY id";
            $query=$this->db->query($sql,
                [
                    $timezone, $currency, $timezone, $awal, $akhir, $bank_id,
                    $timezone, $currency, $timezone, $awal, $akhir, $bank_id
                ]);
            return $query->getResult();
    }
    
    public function history_tobank($currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL, $bank_id){
        $sql="SELECT amount, CONCAT('Withdraw ',ucode) as ket, fee, (pbs_cost+wise_cost) as cost, IF(ISNULL(b.id_referral), referral_fee,0) as referral,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE  currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND b.bank_id=? AND is_card='no'";
        $query=$this->db->query($sql,[$timezone,$currency,$timezone,$awal,$akhir,$bank_id]);
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
    }

    public function history_tocard($currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL, $bank_id){
        $sql="SELECT amount, CONCAT('Request Topup Card ',ucode) as ket, fee, (pbs_cost+wise_cost) as cost, IF(ISNULL(b.id_referral), referral_fee,0) as referral,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE  currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND b.bank_id=? AND is_card='yes'";
        $query=$this->db->query($sql,[$timezone,$currency,$timezone,$awal,$akhir,$bank_id]);
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
    }
    
    public function withdraw($mdata=array()) {
        $swap=$this->db->table("tbl_master_withdraw");
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