<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_fee extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function setfee($mdata=array()){
        $currencyfee=$this->db->table("tbl_defaultfee");
        if (!$currencyfee->replace($mdata)){
            $error=[
	            "code"       => "5052",
	            "error"      => "17",
	            "message"    => "Invalid Currency / Bank ID"
	        ];
            return (object) $error;
        }
    }
    
    public function get_single($bank_id,$currency){
        $sql="SELECT `bank_id`, `currency`, `topup_circuit_fxd`, `topup_circuit_pct`, `topup_outside_fxd`, `topup_outside_pct`, `wallet_sender_fxd`, `wallet_sender_pct`, `wallet_receiver_fxd`, `wallet_receiver_pct`, `walletbank_circuit_fxd`, `walletbank_circuit_pct`, `walletbank_outside_fxd`, `walletbank_outside_pct`, `swap`, `referral_send_fxd`, `referral_send_pct`, `referral_receive_fxd`, `referral_receive_pct`, `referral_topup_fxd`, `referral_topup_pct`,`referral_bank_fxd`, `referral_bank_pct`, card_fxd, `date_created` FROM `tbl_defaultfee` WHERE bank_id=? AND currency=?";
        $query=$this->db->query($sql,[$bank_id,$currency])->getRow();
        if (!$query){
            $error=[
	            "code"       => "5052",
	            "error"      => "17",
	            "message"    => "Invalid Currency / Bank ID"
	        ];
            return (object) $error;
        }
        return $query;
    }
    

}