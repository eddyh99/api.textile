<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function apicard($url,$postData=NULL,$type="POST"){

    $privateKey = file_get_contents(APPPATH .'rsa/private.pem');

    $payload = [
        'api_key' => '6DPqfebg7ZcYDKyfx73Q68jlX9JjSo0dDsJYZDNMOflMrPCuhlNcZq0pl6KJBdgNvIDZdgELsWSoNQTxCWnGa5MEnwvcvaV3Zs1z',
        'ts' => time(),
    ];

    $token = JWT::encode($payload, $privateKey, 'RS256');    
    $ch     = curl_init($url);
    $headers    = array(
        'Authorization: Bearer '.$token,
        'Content-Type: application/json'
    );
    
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if ($type=="POST"){
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData)); 
    }elseif ($type=="DELETE"){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
    }elseif ($type=="PATCH"){
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    }
    $result = json_decode(curl_exec($ch));
    curl_close($ch);
    return $result;        
}
    
    
function cardurl($quoteid=NULL,$fromdate=NULL,$todate=NULL){
    $data=array(
        "createaccount" => "https://api-frontend.wallester.com/v1/accounts",
        "getaccount"    => "https://api-frontend.wallester.com/v1/accounts/".$quoteid,
        "gethashnumber" => "https://api-frontend.wallester.com/v1/cards/".$quoteid."/encrypted-card-number",
        "createcard"    => "https://api-frontend.wallester.com/v1/cards",
        "getcvvnumber"  => "https://api-frontend.wallester.com/v1/cards/".$quoteid."/encrypted-cvv2",
        "getcard"       => "https://api-frontend.wallester.com/v1/cards/".$quoteid,
        "transfer"      => "https://api-frontend.wallester.com/v1/payments/account-transfer",
        "gethistory"    => "https://api-frontend.wallester.com/v1/accounts/".$quoteid."/statement?from_record=0&records_count=1000",
        "getcardhistory"=> "https://api-frontend.wallester.com/v1/cards/".$quoteid."/transactions?from_record=0&records_count=1000&from_date=".$fromdate."&to_date=".$todate,
        "getcardbyaccount" => "https://api-frontend.wallester.com/v1/accounts/".$quoteid."/cards?from_record=0&records_count=1000",
        "closeaccount"  => "https://api-frontend.wallester.com/v1/accounts/".$quoteid."/close",
        "closecard"     => "https://api-frontend.wallester.com/v1/cards/".$quoteid."/close",
        "searchaccount" => "https://api-frontend.wallester.com/v1/accounts?from_record=0&records_count=1000"
    );
    return (object) $data;
}