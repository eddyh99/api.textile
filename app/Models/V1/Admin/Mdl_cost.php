<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_cost extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    //admin bank cost pada menu admin bank (trackless cost + wise cost)

    public function get_single($currency){
        $sql="SELECT a.currency, (topup_circuit_fxd+topup_cf_fxd) as topup_circuit_fxd, (topup_circuit_pct+topup_cf_pct) as topup_circuit_pct, (topup_outside_fxd+topup_ocf_fxd) as topup_outside_fxd, (topup_outside_pct+topup_ocf_pct) as topup_outside_pct, wallet_sender_fxd, wallet_sender_pct, wallet_receiver_fxd, wallet_receiver_pct, (transfer_cf_fxd+walletbank_circuit_fxd) as walletbank_circuit_fxd, (transfer_cf_pct+walletbank_circuit_pct) as walletbank_circuit_pct, (transfer_ocf_fxd+walletbank_outside_fxd) as walletbank_outside_fxd, (transfer_ocf_pct+walletbank_outside_pct) as walletbank_outside_pct, swap,swap_fxd, (a.card_fxd+b.card_fxd) as card_fxd, (a.card_ship_fast+b.card_ship_fast) as card_ship_fast,(a.card_ship_reg+b.card_ship_reg) as card_ship_reg, a.date_created, a.last_update FROM trackless_fee a INNER JOIN wise_cost b ON a.currency=b.currency
            WHERE a.currency = ?
        ";
        $query = $this->db->query($sql, [$currency])->getRow();
        
        if ($query) {
            return $query;
        } else {
            return (object) array(
                "topup_circuit_fxd"     => 0,
                "topup_circuit_pct"     => 0,
                "topup_outside_fxd"     => 0,
                "topup_outside_pct"     => 0,
                "wallet_sender_fxd"     => 0,
                "wallet_sender_pct"     => 0,
                "wallet_receiver_fxd"   => 0,
                "wallet_receiver_pct"   => 0,
                "walletbank_circuit_fxd"=> 0,
                "walletbank_circuit_pct"=> 0,
                "walletbank_outside_fxd"=> 0,
                "walletbank_outside_pct"=> 0,
                "swap"                  => 0,
                "swap_fxd"              => 0,
                "card_fxd"              => 0,
                "card_ship_fast"        => 0,
                "card_ship_reg"         => 0,
            );
        }
    }
}