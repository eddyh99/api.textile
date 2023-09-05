<?php
use App\Models\ValidateToken;

function getBankId($token){
    $token=explode(" ",$token)[1];
    $validBank = new ValidateToken();
    $bankid = $validBank->checkAPIkey($token);
    return $bankid;
}

