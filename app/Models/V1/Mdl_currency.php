<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;


class Mdl_currency extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->wallet   = model('App\Models\V1\Mdl_wallet');
        
    }
    
    /* for member*/
    public function get_active($userid) {
        $member_currency=$this->db->table("tbl_member_currency");
        $sql = "SELECT a.currency, a.symbol, a.name, a.status FROM `tbl_currency` a WHERE a.status='active'";
        $query = $this->db->query($sql)->getResult();

        if ($query){
            $mdata=array();
            foreach ($query as $dcur){
                $temp["currency"]  = $dcur->currency;
                $temp["symbol"]    = $dcur->symbol;
                $temp["name"]      = $dcur->name;
                $temp["status"]    = 'disabled';
                if ($dcur->currency=="USD"||$dcur->currency=="EUR"){
                    $temp["status"] = 'active';
                }
                
                if (($this->wallet->get_balance($userid,$dcur->currency)>0) && ($dcur->currency!="USD") && ($dcur->currency!="EUR")){
                     $data = array(
                        "id_member" => $userid,
            	        "currency"  => $dcur->currency,
            	        "status"    => 'active'
                	);
                    $member_currency->replace($data);
                    $temp["status"]='active';
                }else{
                    $membersql="SELECT currency, status FROM tbl_member_currency WHERE id_member=? AND currency=?";
                    $qquery=$this->db->query($membersql,[$userid,$dcur->currency])->getRow();
                    if ($qquery){
                        $temp["status"]=$qquery->status;
                    }
                }
                
                array_push($mdata,$temp);
            }
            return (object) $mdata;
        }
    }
    
    public function set_active($mdata){
        $member_currency=$this->db->table("tbl_member_currency");
        if (!$member_currency->replace($mdata)){
            $error=[
	            "code"       => "5052",
	            "error"      => "10",
	            "message"    => "Invalid Member ID"
	        ];
            return (object) $error;
        }
    }

    public function get_single($currency,$userid) {
            if ($currency=="USD"){
                $susd = "SELECT currency, symbol, name, status FROM tbl_currency WHERE currency='USD'";
                $qusd = $this->db->query($susd);
                return $qusd->getRow();
            }elseif($currency=="EUR"){
                $seur = "SELECT currency, symbol, name, status FROM tbl_currency WHERE currency='USD' OR currency='EUR'";
                $qeur = $this->db->query($seur);
                return $qeur->getRow();
            }else{
                $sql = "SELECT b.currency, b.symbol, b.name, b.status FROM `tbl_member_currency` a INNER JOIN tbl_currency b ON a.currency=b.currency WHERE b.currency=? AND a.id_member=?";
                $query = $this->db->query($sql, [$currency,$userid]);
                if (!$query){
                    $error=[
        	            "code"       => "5052",
        	            "error"      => "11",
        	            "message"    => $this->db->error()
        	        ];
                    return (object) $error;
                }
                return $query->getRow();
            }
    }    
}