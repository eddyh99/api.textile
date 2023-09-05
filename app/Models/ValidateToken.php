<?php
namespace App\Models;

use CodeIgniter\Model;
use Exception;

class ValidateToken extends Model
{

    protected $allowedFields = ['token'];
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }
    
    function checkAPIkey($token)
    {
        $sql="SELECT * FROM bankmember WHERE apikey=?";
        $data=$this->db->query($sql,array($token))->getRow();
        if (!$data) {
            throw new Exception("invalid API Key, please check your API Key");
        }
        return $data;
    }
}