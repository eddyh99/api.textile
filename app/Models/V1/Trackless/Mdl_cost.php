<?php
namespace App\Models\Trackless;

use CodeIgniter\Model;
use Exception;


class Mdl_cost extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }


    //get trackless cost
    public function get_single($currency){
        $sql="SELECT `currency`, `topup_circuit_fxd`, `topup_circuit_pct`, `topup_outside_fxd`, `topup_outside_pct`, `wallet_sender_fxd`, `wallet_sender_pct`, `wallet_receiver_fxd`, `wallet_receiver_pct`, `walletbank_circuit_fxd`, `walletbank_circuit_pct`, `walletbank_outside_fxd`, `walletbank_outside_pct`, `swap`, `swap_fxd`, card_fxd, card_ship_fast, card_ship_reg	,`date_created`, `last_update` FROM `trackless_fee` WHERE currency=?";
        $query=$this->db->query($sql,[$currency])->getRow();
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
    
    //set trackless cost
    public function set_bankcost($mdata=array()){
        $trackless_cost=$this->db->table("trackless_fee");
        if (!$trackless_cost->replace($mdata)){
            $error=[
	            "code"       => "5052",
	            "error"      => "17",
	            "message"    => "Invalid Currency / Bank ID"
	        ];
            return (object) $error;
        }
        
    }
    
    // get wise cost
    public function get_wise($currency){
        $sql="SELECT `currency`, `transfer_cf_fxd` as transfer_circuit_fxd, `transfer_cf_pct` as transfer_circuit_pct, `transfer_ocf_fxd` as transfer_outside_fxd, `transfer_ocf_pct` as transfer_outside_pct, `topup_cf_fxd` as topup_circuit_fxd, `topup_cf_pct`as topup_circuit_pct, `topup_ocf_fxd` as topup_outside_fxd, `topup_ocf_pct` as topup_outside_pct, card_fxd,card_ship_fast, card_ship_reg FROM wise_cost WHERE currency=?";
        $query=$this->db->query($sql,[$currency])->getRow();
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

    //set wise cost
    public function set_wisecost($mdata=array()){
        $wise_cost=$this->db->table("wise_cost");
        if (!$wise_cost->replace($mdata)){
            $error=[
	            "code"       => "5052",
	            "error"      => "17",
	            "message"    => "Invalid Currency"
	        ];
            return (object) $error;
        }
        
    }
    
}