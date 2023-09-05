<?php
namespace App\Models\Trackless;

use CodeIgniter\Model;
use Exception;


class Mdl_card extends Model
{
    protected $server_tz = "Asia/Singapore";

	public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    public function setcardBank($mdata=array()){
        $tblbank=$this->db->table("tbl_cardbank");
        if (!$tblbank->replace($mdata)){
	        $error=[
	            "code"       => "5054",
	            "error"      => "17",
	            "message"    => "Card Bank not found"
	        ];
            return (object) $error;
        }
    }
    
    public function getcardbank(){
        $sql="SELECT * FROM tbl_cardbank WHERE currency='EUR'";
        $query=$this->db->query($sql)->getRow();
        if (!$query) {
	        $error=[
	            "code"       => "5054",
	            "error"      => "17",
	            "message"    => "Card Bank not found"
	        ];
            return (object) $error;
        }
        
        return $query;
    }

    public function history_cardtopup($bank_id=NULL,$currency=NULL,$awal=NULL, $akhir=NULL, $timezone=NULL){
        if (empty($bank_id)){
            $sql="SELECT a.id,amount, CONCAT('topup card ',ucode) as ket, proses,c.lastdigit, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_card c ON c.id_member=b.id WHERE a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='yes' ORDER BY DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) DESC";
            $query=$this->db->query($sql,[$timezone,$currency,$timezone,$awal,$akhir,$timezone]);
        }else{
            $sql="SELECT a.id,amount, CONCAT('topup card ',ucode) as ket, proses,c.lastdigit, convert_tz(a.date_created, '".$this->server_tz."', ?) AS date_created FROM tbl_member_tobank a INNER JOIN tbl_member b ON a.sender_id=b.id INNER JOIN tbl_card c ON c.id_member=b.id WHERE bank_id=? AND a.currency=? AND DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) BETWEEN ? AND ? AND is_card='yes' ORDER BY DATE(convert_tz(a.date_created, '".$this->server_tz."', ?)) DESC";
            $query=$this->db->query($sql,[$timezone,$bank_id,$currency,$timezone,$awal,$akhir,$timezone]);
        }
        if ($query) {
            return $query->getResult();
        } else {
            return $this->db->error();            
        }        
    }
    
    public function update_cardtopup($transaction_id){
        
        $sql="UPDATE tbl_member_tobank SET proses='yes' WHERE id=?";
        $query=$this->db->query($sql,[$transaction_id]);
        return ($this->db->affectedRows() != 1) ? false : true;
    }
    
    public function get_inactivecard(){
        $sql="SELECT a.id,ucode, lastdigit FROM tbl_card a INNER JOIN tbl_member b ON a.id_member=b.id WHERE card_type='physical' AND a.status='new'";
        $query=$this->db->query($sql);
        return $query->getResult();
    }
    
    public function link_card($id){
        
        $sql="UPDATE tbl_card SET status='active' WHERE id=?";
        $query=$this->db->query($sql,[$id]);
        return ($this->db->affectedRows() != 1) ? "Card failed to linked" : "Card successfully linked";
    }

}