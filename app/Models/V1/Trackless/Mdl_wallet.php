<?php
namespace App\Models\Trackless;

use CodeIgniter\Model;
use Exception;
//user_id master swap 2 milik trackless

class Mdl_wallet extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }


    public function get_all($bank_id=NULL){
        $sql="SELECT currency,symbol FROM tbl_currency WHERE status='active'";
        $cur=$this->db->query($sql)->getResult();
        $mdata=array();
        if (empty($bank_id)){
            foreach($cur as $dt){
                $sql="SELECT IFNULL(sum(amount),0) as amount FROM (
                        SELECT sum(pbs_cost) as amount FROM tbl_member_topup WHERE currency=? AND is_proses='yes'
                        UNION ALL
                        SELECT sum(pbs_cost) as amount FROM tbl_member_tobank WHERE currency=?
                        UNION ALL
                        SELECT sum(pbs_cost) as amount FROM tbl_member_swap WHERE currency=?
                        UNION ALL
                        SELECT sum(pbs_sender_cost) as amount FROM tbl_member_towallet WHERE currency=?
                        UNION ALL
                        SELECT sum(pbs_receiver_cost) as amount FROM tbl_member_towallet WHERE currency=?
                        UNION ALL
                        SELECT sum(pbs_cost+pbs_ship) as amount FROM tbl_card WHERE currency=?
                        UNION ALL
                        SELECT sum(amount+wise_cost)*-1 as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id='1' AND a.currency=?
                        UNION ALL
                        SELECT sum(amount) *-1 as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id='1' AND currency=? 
                        UNION ALL
                        SELECT sum(receive) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id='1' AND target_cur=?
                    ) x
                ";
            
                $result=$this->db->query($sql,[
                    $dt->currency,
                    $dt->currency,
                    $dt->currency,
                    $dt->currency,
                    $dt->currency,
                    $dt->currency,
                    $dt->currency,
                    $dt->currency,
                    $dt->currency,
                ])->getRow()->amount;
                    
                $temp["currency"]=$dt->currency;
                $temp["symbol"]=$dt->symbol;
                $temp["amount"]=$result;
                array_push($mdata,$temp);
            }
        }else{
            foreach($cur as $dt){
                $sql="SELECT IFNULL(sum(amount),0) as amount FROM (
                        SELECT sum(pbs_cost) as amount FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE a.currency=? AND a.is_proses='yes' AND b.bank_id=?
                        UNION ALL
                        SELECT sum(pbs_cost) as amount FROM tbl_member_tobank  a INNER JOIN tbl_member b ON a.sender_id=b.id  WHERE a.currency=? AND b.bank_id=?
                        UNION ALL
                        SELECT sum(pbs_cost) as amount FROM tbl_member_swap  a INNER JOIN tbl_member b ON a.id_member=b.id  WHERE a.currency=?  AND b.bank_id=?
                        UNION ALL
                        SELECT sum(pbs_sender_cost) as amount FROM tbl_member_towallet  a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE a.currency=? AND b.bank_id=?
                        UNION ALL
                        SELECT sum(pbs_receiver_cost) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE a.currency=? AND b.bank_id=?
                        UNION ALL
                        SELECT sum(pbs_cost+pbs_ship) as amount FROM tbl_card  a INNER JOIN tbl_member b ON a.id_member=b.id WHERE a.currency=? AND b.bank_id=?
                        UNION ALL
                        SELECT IFNULL(sum(amount+wise_cost)*-1,0) as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE a.currency=? AND  b.bank_id=? AND b.role='super admin'
                        UNION ALL
                        SELECT sum(pbs_cost) as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE a.currency=? AND b.bank_id=? AND b.role='admin'
                        UNION ALL
                        SELECT IFNULL(sum(amount) *-1,0) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE currency=? AND b.bank_id=? AND b.role='super admin'
                        UNION ALL
                        SELECT sum(pbs_cost) as amount FROM tbl_master_swap  a INNER JOIN tbl_user b ON a.user_id=b.id WHERE currency=? AND b.bank_id=? AND b.role='admin'
                        UNION ALL
                        SELECT IFNULL(sum(receive),0) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE target_cur=? AND b.bank_id=? AND b.role='super admin'
                    ) x
                ";
            
                $result=$this->db->query($sql,[
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                    $dt->currency,$bank_id,
                ])->getRow()->amount;
                    
                $temp["currency"]=$dt->currency;
                $temp["symbol"]=$dt->symbol;
                $temp["amount"]=$result;
                array_push($mdata,$temp);
            }
        }
        return (object)$mdata;
    }
    
    public function balance_bycurrency($currency,$bank_id=NULL){
        if (empty($bank_id)){
            $sql="SELECT IFNULL(sum(amount),0) as amount FROM (
                    SELECT sum(pbs_cost) as amount FROM tbl_member_topup WHERE currency=? AND is_proses='yes'
                    UNION ALL
                    SELECT sum(pbs_cost) as amount FROM tbl_member_tobank WHERE currency=?
                    UNION ALL
                    SELECT sum(pbs_cost) as amount FROM tbl_member_swap WHERE currency=?
                    UNION ALL
                    SELECT sum(pbs_sender_cost) as amount FROM tbl_member_towallet WHERE currency=?
                    UNION ALL
                    SELECT sum(pbs_receiver_cost) as amount FROM tbl_member_towallet WHERE currency=?
                    UNION ALL
                    SELECT sum(pbs_cost+pbs_ship) as amount FROM tbl_card WHERE currency=?
                    UNION ALL
                    SELECT sum(amount+wise_cost)*-1 as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id='1' AND currency=?
                    UNION ALL
                    SELECT sum(amount) *-1 as amount FROM tbl_master_swap  a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id='1' AND currency=? 
                    UNION ALL
                    SELECT sum(receive) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE b.bank_id='1' AND target_cur=?
                ) x
            ";
                
            $result=$this->db->query($sql,[
                $currency,
                $currency,
                $currency,
                $currency,
                $currency,
                $currency,
                $currency,
                $currency,
                $currency,
            ])->getRow()->amount;
        }else{
            $sql="SELECT IFNULL(sum(amount),0) as amount FROM (
                    SELECT sum(pbs_cost) as amount FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE a.currency=? AND is_proses='yes' AND b.bank_id=?
                    UNION ALL
                    SELECT sum(pbs_cost) as amount FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE a.currency=? AND b.bank_id=?
                    UNION ALL
                    SELECT sum(pbs_cost) as amount FROM tbl_member_swap a INNER JOIN tbl_member b ON a.id_member=b.id WHERE a.currency=? AND b.bank_id=?
                    UNION ALL
                    SELECT sum(pbs_sender_cost) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE a.currency=? AND b.bank_id=?
                    UNION ALL
                    SELECT sum(pbs_receiver_cost) as amount FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE a.currency=? AND b.bank_id=?
                    UNION ALL
                    SELECT sum(pbs_cost+pbs_ship) as amount FROM tbl_card a INNER JOIN tbl_member b ON a.id_member=b.id WHERE a.currency=? AND b.bank_id=?
                    UNION ALL
                    SELECT sum(amount+wise_cost)*-1 as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE a.currency=? AND b.bank_id=? AND b.role='super admin'
                    UNION ALL
                    SELECT sum(pbs_cost) as amount FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id WHERE a.currency=? AND b.bank_id=? AND b.role='admin'
                    UNION ALL
                    SELECT sum(amount) *-1 as amount FROM tbl_master_swap  a INNER JOIN tbl_user b ON a.user_id=b.id WHERE currency=? AND b.bank_id=? AND b.role='super admin'
                    UNION ALL
                    SELECT sum(pbs_cost) as amount FROM tbl_master_swap  a INNER JOIN tbl_user b ON a.user_id=b.id WHERE currency=? AND b.bank_id=? AND b.role='admin'
                    UNION ALL
                    SELECT sum(receive) as amount FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id WHERE target_cur=? AND b.bank_id=? AND b.role='super admin'
                ) x
            ";
                
            $result=$this->db->query($sql,[
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
                $currency,$bank_id,
            ])->getRow()->amount;
        }
        return $result;
    }
    
    public function get_historybycurrency($currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        $sql="SELECT amount, ket, comission, cost, date_created FROM (
                  SELECT amount, CONCAT('topup ',ucode) as ket, pbs_cost as comission, wise_cost as cost, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_proses='yes'
                  UNION ALL
                  SELECT amount, CONCAT('Withdraw ',ucode) as ket, pbs_cost as comission, wise_cost as cost,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE  currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='no'
                  UNION ALL
                  SELECT amount, CONCAT('Request Topup Card ',ucode) as ket, pbs_cost as comission, wise_cost as cost,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE  currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='yes'
                  UNION ALL
                  SELECT amount, CONCAT('Transfer from ',b.ucode, ' to ', c.ucode) as ket, (pbs_sender_cost+pbs_receiver_cost) as comission,0 as cost, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_member c ON a.receiver_id=c.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
                  UNION ALL
                  SELECT amount, CONCAT(ucode, ' Swap From ', currency, ' to ', target_cur) as ket, pbs_cost as comission, 0 as cost ,  convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_swap a INNER JOIN tbl_member b ON a.id_member=b.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?
                  UNION ALL
                  SELECT '' as amount, CONCAT('Request ',card_type,' Card from ', ucode) as ket, (pbs_cost+pbs_ship) as comission, (card_cost+ship_card) as cost ,  convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_card a INNER JOIN tbl_member b ON a.id_member=b.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?
              ) x ORDER BY date_created DESC
        ";
        
        $query=$this->db->query($sql,
        [
            $timezone,$currency,$timezone,$awal,$akhir,
            $timezone,$currency,$timezone,$awal,$akhir,
            $timezone,$currency,$timezone,$awal,$akhir,
            $timezone,$currency,$timezone,$awal,$akhir,
            $timezone,$currency,$timezone,$awal,$akhir,
            $timezone,$currency,$timezone,$awal,$akhir,
        ]);
        
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
        
    }

    public function getHistory_byid($userid=NULL,$currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        $sql="SELECT IFNULL(sum(amount-fee),0) as lastsaldo FROM (
                  SELECT amount, 'topup' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_topup WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) < ? AND is_proses='YES'
                  UNION ALL
                  SELECT amount*-1, 'Withdraw' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank WHERE sender_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) < ?
                  UNION ALL
                  SELECT 0 as amount, CONCAT('Request ',card_type,' Card') as ket, (fee+pbs_cost+card_cost+ship_card+pbs_ship) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_card WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) < ?
                  UNION ALL
                  SELECT amount*-1, 'Send' as ket, (sender_fee+pbs_sender_cost+referral_sender_fee) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet WHERE sender_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) < ? 
                  UNION ALL
                  SELECT amount, 'Receive' as ket, (receiver_fee+pbs_receiver_cost+referral_receiver_fee) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet WHERE receiver_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) < ? 
                  UNION ALL
                  SELECT (amount-pbs_cost-fee)*-1 as amount, 'Swap' as ket, (pbs_cost+fee) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_swap WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) < ?
                  UNION ALL
                  SELECT receive, 'Swap Receive' as ket, 0 as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_swap WHERE id_member=? AND target_cur=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) < ? 
                  UNION ALL
                  SELECT referral_fee as amount, 'Referral' as ket, 0 as fee,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) < ? AND is_proses='yes'
                  UNION ALL
                  SELECT referral_receiver_fee as amount, 'Referral' as ket, 0 as fee,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) < ? 
                  UNION ALL
                  SELECT referral_sender_fee as amount, 'Referral' as ket, 0 as fee,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) < ? 
                  UNION ALL
                  SELECT referral_fee as amount, 'Referral' as ket, 0 as fee,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a  INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) < ?
            ) x;
        ";
        $query=$this->db->query($sql,
        [
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal,
            $timezone,$userid,$currency,$timezone,$awal
        ])->getRow()->lastsaldo;
        
        $sql2="SELECT amount, ket, fee, date_created FROM (
                  SELECT amount, 'topup' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_topup WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_proses='yes' 
                  UNION ALL
                  SELECT amount*-1 as amount, 'Withdraw' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee, convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank WHERE sender_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='no'
                  UNION ALL
                  SELECT amount*-1 as amount, 'Request Topup Card' as ket, (fee+pbs_cost+referral_fee+wise_cost) as fee, convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank WHERE sender_id=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='yes'
                  UNION ALL
                  SELECT 0 as amount, CONCAT('Request ',card_type,' Card') as ket, (fee+pbs_cost+card_cost+ship_card+pbs_ship) as fee,convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_card WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
                  UNION ALL
                  SELECT a.amount*-1, CONCAT('Send to ',b.ucode) as ket, (a.sender_fee+a.referral_sender_fee+a.pbs_sender_cost) as fee, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE a.sender_id=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
                  UNION ALL
                  SELECT a.amount, CONCAT('Receive from ',b.ucode) as ket, (a.receiver_fee+a.referral_receiver_fee+a.pbs_receiver_cost) as fee, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE a.receiver_id=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
                  UNION ALL
                  SELECT (amount-pbs_cost-fee)*-1, 'Swap' as ket, (fee+ pbs_cost) as fee, convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_swap WHERE id_member=? AND currency=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?
                  UNION ALL
                  SELECT receive, 'Swap Receive' as ket, 0 as fee, convert_tz(date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_swap WHERE id_member=? AND target_cur=? AND DATE(convert_tz(date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
                  UNION ALL
                  SELECT referral_fee as amount, 'Receive Referral Fee' as ket, 0 as fee, DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 1 second) AS date_created FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_proses='yes'
                  UNION ALL
                  SELECT referral_receiver_fee as amount, 'Receive Referral Fee' as ket, 0 as fee, DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 1 second) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.receiver_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
                  UNION ALL
                  SELECT referral_sender_fee as amount, 'Receive Referral Fee' as ket, 0 as fee, DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 2 second) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? 
                  UNION ALL                 
                  SELECT referral_fee as amount, 'Receive Referral Fee' as ket, 0 as fee, DATE_ADD(convert_tz(a.date_created, '".$this->server_tz."', ?),INTERVAL 1 second) AS date_created FROM tbl_member_tobank a  INNER JOIN tbl_member b ON a.sender_id=b.id WHERE b.id_referral=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?) x ORDER BY date_created ASC";
        $query2=$this->db->query($sql2,
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
            $timezone,$userid,$currency,$timezone,$awal,$akhir,
        ]);
        
        $transaksi=array();
        $temp["ket"]='Previous Balance';
        $temp["balance"]=$query;
        $temp["debit"]=NULL;
        $temp["credit"]=NULL;
        $temp["fee"]=NULL;
        $temp["date_created"]=NULL;
        
        array_push($transaksi,$temp);
        $balance=$query;
        foreach ($query2->getResult() as $dt){
            $temp["debit"]=NULL;
            $temp["credit"]=NULL;
            if  ($dt->amount<0){
                $temp["debit"]=$dt->amount;
            }
            if  ($dt->amount>0){
                $temp["credit"]=$dt->amount;
            }
            $temp["ket"]=$dt->ket;
            $temp["fee"]=($dt->fee)>0 ? ($dt->fee)*-1:0;
            $balance=$balance+$dt->amount+$temp["fee"];
            $temp["balance"]=$balance;
            $temp["date_created"]=$dt->date_created;
            array_push($transaksi,$temp);
        }
        return $transaksi;
    }
    
    public function history_topup($bank_id=NULL,$currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        $sql="SELECT amount, CONCAT('topup ',ucode) as ket, fee,wise_cost as cost, pbs_cost as comission, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_topup a INNER JOIN tbl_member b ON a.id_member=b.id WHERE bank_id=? AND currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? ORDER BY DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) DESC";
        $query=$this->db->query($sql,[$timezone,$bank_id,$currency,$timezone,$awal,$akhir,$timezone]);
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
    }
    
    public function history_wallet($bank_id=NULL,$currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        $sql="SELECT amount, CONCAT('Transfer from ',b.ucode, ' to ', c.ucode) as ket, 0 as cost, (pbs_sender_cost+pbs_receiver_cost) as comission, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_towallet a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_member c ON a.receiver_id=c.id WHERE b.bank_id=? AND currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? ORDER BY DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) DESC";
        $query=$this->db->query($sql,[$timezone,$bank_id,$currency,$timezone,$awal,$akhir,$timezone]);
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
    }
    
    public function history_tobank($bank_id=NULL,$currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        $sql="SELECT amount, ket, fee, cost, comission, date_created FROM (
                SELECT amount, CONCAT('Withdraw ',ucode) as ket, fee, wise_cost as cost, pbs_cost as comission,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE bank_id=? AND currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='no'
                UNION ALL
                SELECT amount, CONCAT('Request Topup Card ',ucode) as ket, fee, wise_cost as cost, pbs_cost as comission,convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id WHERE bank_id=? AND currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='yes'
            ) x ORDER BY DATE(convert_tz(date_created, '".$this->server_tz."', ?)) DESC";
        $query=$this->db->query($sql,[
            $timezone,$bank_id,$currency,$timezone,$awal,$akhir,
            $timezone,$bank_id,$currency,$timezone,$awal,$akhir,
            $timezone]);
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }
    }

   public function get_historymasterwallet($currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        $sql="SELECT deskripsi, amount, pbs_cost as comission, date_created,ucode_mwallet,wise_cost as cost FROM (
            SELECT 'Withdraw' as deskripsi, a.amount, a.pbs_cost, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created,c.ucode_mwallet, a.wise_cost FROM tbl_master_withdraw a INNER JOIN tbl_user b ON a.user_id=b.id INNER JOIN bankmember c ON b.bank_id=c.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?
            UNION ALL
            SELECT CONCAT('Swap From ',a.currency,' to ',a.target_cur) as deskripsi, a.amount, a.pbs_cost, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created,c.ucode_mwallet,a.wise_cost FROM tbl_master_swap a INNER JOIN tbl_user b ON a.user_id=b.id INNER JOIN bankmember c ON b.bank_id=c.id WHERE currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ?
            ) x ORDER BY date_created DESC
        ";
        $query=$this->db->query($sql,
        [
            $timezone,$currency,$timezone,$awal,$akhir,
            $timezone,$currency,$timezone,$awal,$akhir,
        ]);
        
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
    
    public function topup($mdata=array(),$causal=NULL){
        $topup=$this->db->table("tbl_member_topup");
        if (empty($causal)){
            if (!$topup->insert($mdata)){
                $error=[
    	            "code"       => "5055",
    	            "error"      => "10",
    	            "message"    => $this->db->error()
    	        ];
                return (object) $error;
            }
        }else{
            $topup->where("causal",$causal);
            if (!$topup->update($mdata)){
                $error=[
    	            "code"       => "5055",
    	            "error"      => "10",
    	            "message"    => $this->db->error()
    	        ];
                return (object) $error;
            }
        }
    }
    
    public function delete_topup(){
        $now=date("Y-m-d");
        $sql="DELETE FROM tbl_member_topup WHERE DATEDIFF(?,date_created)>7 AND is_proses='no'";
        $query=$this->db->query($sql,$now);
    }    
}