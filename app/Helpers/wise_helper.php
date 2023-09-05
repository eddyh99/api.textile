<?php
    function apiwise($url_quote,$postData=NULL,$token, $type="POST"){
        $ch     = curl_init($url_quote);
        $headers    = array(
            'Authorization: Bearer '.$token,
            'Content-Type: application/json'
        );
        
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($type=="POST"){
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); 
        }
        $result = json_decode(curl_exec($ch));
        curl_close($ch);
        return $result;        
    }
    
    function urlapi($quoteid=NULL,$transferID=NULL,$profile=NULL){
        $api_mode= getenv('api_mode');
        if ($api_mode=="sandbox"){
            $data=array(
                    "profile"       => $profile,
                    "quote"         => "https://api.sandbox.transferwise.tech/v2/quotes",
                    "balancemove"   => "https://api.sandbox.transferwise.tech/v2/profiles/".$profile."/balance-movements",
                    "readquote"     => "https://api.sandbox.transferwise.tech/v3/profiles/".$profile."/quotes/".$quoteid,
                    "recipient"     => "https://api.sandbox.transferwise.tech/v1/accounts",
                    "transfer"      => "https://api.sandbox.transferwise.tech/v1/transfers",
                    "payment"       => "https://api.sandbox.transferwise.tech/v3/profiles/".$profile."/transfers/".$transferID."/payments",
                    "checkbalance"  => "https://api.sandbox.transferwise.tech/v4/profiles/".$profile."/balances?types=STANDARD",
                    "bankcode"      => "https://api.sandbox.transferwise.tech/v1/banks?country=".$quoteid,
                    "branchcode"    => "https://api.sandbox.transferwise.tech/v1/bank-branches?country=".$quoteid."&bankCode=".$transferID,
                    "bankdetail"    => "https://api.sandbox.transferwise.tech/v1/profiles/".$profile."/account-details",
                    "depositbank"   => "https://api.sandbox.transferwise.tech/v1/profiles/".$profile."/transfers/".$transferID."/deposit-details/bank-transfer",
                    "statustransfer"=> "https://api.sandbox.transferwise.tech/v1/transfers/".$transferID
                );
        }elseif ($api_mode=="live"){
            $data=array(
                    "profile"       => $profile,
                    "quote"         => "https://api.transferwise.com/v2/quotes",
                    "balancemove"   => "https://api.transferwise.com/v2/profiles/".$profile."/balance-movements",
                    "readquote"     => "https://api.transferwise.com/v3/profiles/".$profile."/quotes/".$quoteid,
                    "recipient"     => "https://api.transferwise.com/v1/accounts",
                    "transfer"      => "https://api.transferwise.com/v1/transfers",
                    "payment"       => "https://api.transferwise.com/v3/profiles/".$profile."/transfers/".$transferID."/payments",
                    "checkbalance"  => "https://api.transferwise.com/v4/profiles/".$profile."/balances?types=STANDARD",
                    "bankcode"      => "https://api.transferwise.com/v1/banks?country=".$quoteid,
                    "branchcode"    => "https://api.transferwise.com/v1/bank-branches?country=".$quoteid."&bankCode=".$transferID,
                    "bankdetail"    => "https://api.transferwise.com/v1/profiles/".$profile."/account-details",
                    "depositbank"   => "https://api.transferwise.com/v1/profiles/".$profile."/transfers/".$transferID."/deposit-details/bank-transfer",
                    "statustransfer"=> "https://api.transferwise.com/v1/transfers/".$transferID
            );
        }
        return (object) $data;
    }
    

/* Recipient Data*/
    function dataUSD($data=NULL, $profile){
        if ($data->transfer_type=="circuit"){
            $recipient=array(
              "currency"    => "USD", 
              "type"        => "ABA", 
              "profile"     => $profile, 
              "accountHolderName"   => $data->bank_detail->accountHolderName,
              "legalType"           => "PRIVATE",
              "details"             => array ( 
                "abartn"            => $data->bank_detail->abartn,
                "accountNumber"     => $data->bank_detail->accountNumber,
                "accountType"       => strtoupper($data->bank_detail->accountType),
                "address"       => array (
                    "countryCode"   => "US",
                    "firstLine"     => $data->bank_detail->firstLine,
                    "postCode"      => $data->bank_detail->postCode,
                    "city"          => $data->bank_detail->city,
                    "state"         => $data->bank_detail->state,
                ),
              ),
            );                
        }else{
            $recipient=array(
                "currency"    => "USD", 
                "type"        => "SWIFT_CODE", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "legalType"           => "PRIVATE",
                "details"    => array (
                    "address"   => array (
                        "countryCode"   => $data->bank_detail->countryCode,
                        "city"          => $data->bank_detail->city,
                        "firstLine"     => $data->bank_detail->firstLine,
                        "postCode"      => $data->bank_detail->postCode,
                    ),
                    "swiftCode"    => $data->bank_detail->swiftCode,
                    "accountNumber"=> $data->bank_detail->accountNumber
              )
            );                
        }
        return $recipient;
    }
    
    function dataEUR($data=NULL, $profile){
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "currency"    => "EUR", 
                "type"        => "IBAN", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "legalType"           => "PRIVATE",
                "details"    => array ( 
                    "iban"    => $data->bank_detail->IBAN
                )
            ); 
        }else{
            $recipient=array(
                "currency"    => "EUR", 
                "type"        => "SWIFT_CODE", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "legalType"           => "PRIVATE",
                "details"    => array (
                    "swiftCode"    => $data->bank_detail->swiftCode,
                    "accountNumber"=> $data->bank_detail->accountNumber
                )
            );         
        }
        return $recipient;        
    }

    function dataAED($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "currency"    => "AED", 
                "type"        => "emirates", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "legalType"           => "PRIVATE",
                "details"    => array ( 
                    "iban"    => $data->bank_detail->IBAN
                )
            ); 
        }
        return $recipient;        
    }

    function dataARS($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "ARS", 
                "type"        => "argentina", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "taxId"         => $data->bank_detail->taxId,
                    "accountNumber" => $data->bank_detail->accountNumber
                )
            ); 
        }
        return $recipient;        
    }
    
    
    function dataAUD($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "AUD", 
                "type"        => "australian", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "address"   => array (
                        "countryCode"   => $data->bank_detail->countryCode,
                        "state"         => $data->bank_detail->state,
                        "city"          => $data->bank_detail->city,
                        "firstLine"     => $data->bank_detail->firstLine,
                        "postCode"      => $data->bank_detail->postCode,
                    ),
                    "legalType"     => "PRIVATE",
                    "bsbCode"       => $data->bank_detail->bsbCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }
    
    function dataBDT($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "BDT", 
                "type"        => "bangladesh", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "branchCode"    => $data->bank_detail->branchCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }
    
       function dataCAD($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "CAD", 
                "type"        => "canadian", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "institutionNumber" => $data->bank_detail->institutionNumber,
                    "transitNumber"     => $data->bank_detail->transitNumber,
                    "accountNumber"     => $data->bank_detail->accountNumber,
                    "accountType"       => $data->bank_detail->accountType,
                ),
            ); 
        }
        return $recipient;        
    }

    function dataCHF($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "CHF", 
                "type"        => "iban", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "IBAN"          => $data->bank_detail->IBAN,
                    "town"          => $data->bank_detail->town,
                    "postCode"      => $data->bank_detail->postCode,
                ),
            ); 
        }
        return $recipient;        
    }
    
    function dataCLP($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "CLP", 
                "type"        => "chile", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "rut"           => $data->bank_detail->rut,
                    "accountType"   => $data->bank_detail->accountType,
                    "phoneNumber"   => $data->bank_detail->phoneNumber,
                ),
            ); 
        }
        return $recipient;        
    }
    
    function dataCNY($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            if ($data->bank_detail->legalType=="BUSINESS"){
                $recipient=array(
                    "profile"     => $profile, 
                    "currency"    => "CNY", 
                    "type"        => "chinese_local_business", 
                    "accountHolderName"   => $data->bank_detail->accountHolderName,
                    "details"    => array ( 
                        "legalType"     => "BUSINESS",
                        "accountNumber" => $data->bank_detail->accountNumber,
                        "swiftCode"     => $data->bank_detail->swiftCode,
                        "address"   => array(
                            "city"      => $data->bank_detail->city,
                            "country"   => "CN",
                            "firstLine" => $data->bank_detail->firstLine,
                            "postCode"  => $data->bank_detail->postCode,
                        )  
                    ),
                ); 
            }elseif ($data->bank_detail->legalType=="PRIVATE"){
                $recipient=array(
                    "profile"     => $profile, 
                    "currency"    => "CNY", 
                    "type"        => $data->bank_detail->type, 
                    "accountHolderName"   => $data->bank_detail->accountHolderName,
                    "details"    => array ( 
                        "legalType"     => "PRIVATE",
                        "accountNumber" => $data->bank_detail->accountNumber,
                    ),
                ); 
            }
        }
        return $recipient;        
    }    

    function dataCZK($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "CZK", 
                "type"        => "czech", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "prefix"        => $data->bank_detail->prefix,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "bankCode"      => $data->bank_detail->bankCode,
                ),
            ); 
        }
        return $recipient;        
    }
    
    function dataIBAN($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => $data->currency, 
                "type"        => "iban", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "IBAN"          => $data->bank_detail->IBAN,
                ),
            ); 
        }
        return $recipient;        
    }
    
    
    function dataGBP($data=NULL, $profile){
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "currency"    => "GBP", 
                "type"        => "sort_code", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "sortCode"      => $data->bank_detail->sortCode,
                    "accountNumber" => $data->bank_detail->accountNumber
                )
            ); 
        }else{
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "GBP", 
                "type"        => "iban", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "IBAN"          => $data->bank_detail->IBAN,
                ),
            );      
        }
        return $recipient;        
    }  
    
    function dataGHS($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "GHS", 
                "type"        => "ghana_local", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "bankCode"      => $data->bank_detail->bankCode,
                    "branchCode"    => $data->bank_detail->branchCode,
                ),
            ); 
        }
        return $recipient;        
    }       
    
    function dataHKD($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "HKD", 
                "type"        => "hongkong", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "bankCode"      => $data->bank_detail->bankCode,
                    "address"   => array(
                            "city"      => $data->bank_detail->city,
                            "country"   => "HK",
                            "firstLine" => $data->bank_detail->firstLine,
                            "postCode"  => $data->bank_detail->postCode,
                        )
                ),
            ); 
        }
        return $recipient;        
    }      
    
    function dataHUF($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "HUF", 
                "type"        => "hungarian", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }    

    function dataIDR($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "IDR", 
                "type"        => "indonesian", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }    
    
    function dataILS($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "ILS", 
                "type"        => "israeli_local", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "IBAN"          => $data->bank_detail->IBAN,
                    "address"   => array(
                        "city"      => $data->bank_detail->city,
                        "country"   => "IL",
                        "firstLine" => $data->bank_detail->firstLine,
                        "postCode"  => $data->bank_detail->postCode,
                    )
                ),
            ); 
        }
        return $recipient;        
    }
    
    function dataINR($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "INR", 
                "type"        => "indian", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "ifscCode"      => $data->bank_detail->ifscCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }    

    function dataJPY($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "JPY", 
                "type"        => "japanese", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "branchCode"    => $data->bank_detail->branchCode,
                    "accountType"   => $data->bank_detail->accountType,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }    
    
    
    function dataKES($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "KES", 
                "type"        => "kenya_local", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }    

    function dataKRW($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "KRW", 
                "type"        => "south_korean_paygate", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "dateOfBirth"   => $data->bank_detail->dateOfBirth,
                    "email"         => $data->bank_detail->email,
                ),
            ); 
        }
        return $recipient;        
    }  

    function dataLKR($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "LKR", 
                "type"        => "srilanka", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "branchCode"    => $data->bank_detail->branchCode,
                ),
            ); 
        }
        return $recipient;        
    }      

    function dataMAD($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "MAD", 
                "type"        => "morocco", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "address"       => array(
                        "city"      => $data->bank_detail->city,
                        "country"   => "MA",
                        "firstLine" => $data->bank_detail->firstLine,
                        "postCode"  => $data->bank_detail->postCode,
                    )
                ),
            ); 
        }
        return $recipient;        
    }        

    function dataMXN($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "MXN", 
                "type"        => "mexican", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "clabe"         => $data->bank_detail->clabe,
                ),
            ); 
        }
        return $recipient;        
    }  

    function dataMYR($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "MYR", 
                "type"        => "malaysian", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "swiftCode"     => $data->bank_detail->swiftCode,
                    "accountNumber"     => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }  
    
    function dataNGN($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "NGN", 
                "type"        => "nigeria", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }  

    function dataNPR($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "NPR", 
                "type"        => "nepal", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "legalType"     => "PRIVATE",
                "details"    => array ( 
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }      
    
    function dataNZD($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "NZD", 
                "type"        => "newzealand", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }      

    function dataPHP($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "PHP", 
                "type"        => "philippines", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "address"       => array(
                        "city"      => $data->bank_detail->city,
                        "country"   => "PH",
                        "firstLine" => $data->bank_detail->firstLine,
                        "postCode"  => $data->bank_detail->postCode,
                    )
                ),
            ); 
        }
        return $recipient;        
    }    

    function dataSGD($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "SGD", 
                "type"        => "singapore", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                ),
            ); 
        }
        return $recipient;        
    }      

    function dataTHB($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "profile"     => $profile, 
                "currency"    => "THB", 
                "type"        => "thailand", 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "bankCode"      => $data->bank_detail->bankCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "address"       => array(
                        "city"      => $data->bank_detail->city,
                        "country"   => "TH",
                        "firstLine" => $data->bank_detail->firstLine,
                        "postCode"  => $data->bank_detail->postCode,
                    )
                ),
            ); 
        }
        return $recipient;        
    } 
    
    function dataTRY($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "currency"    => "TRY", 
                "type"        => "turkish_earthport", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType" => "PRIVATE",
                    "iban"      => $data->bank_detail->IBAN
                )
            ); 
        }
        return $recipient;        
    }    

    function dataUAH($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "currency"    => "UAH", 
                "type"        => "privatbank", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "phoneNumber"   => $data->bank_detail->phoneNumber,
                    "accountNumber" => $data->bank_detail->accountNumber,
                    "address"       => array(
                        "city"      => $data->bank_detail->city,
                        "country"   => "UA",
                        "firstLine" => $data->bank_detail->firstLine,
                        "postCode"  => $data->bank_detail->postCode,
                    )
                )
            ); 
        }
        return $recipient;        
    }      

    function dataVND($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "currency"    => "VND", 
                "type"        => "vietname_earthport", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "swiftCode"     => $data->bank_detail->swiftCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                )
            ); 
        }
        return $recipient;        
    }

    function dataZAR($data=NULL, $profile){
        //only local bank account
        if ($data->transfer_type=="circuit"){
            $recipient=array(
                "currency"    => "ZAR", 
                "type"        => "southafrica", 
                "profile"     => $profile, 
                "accountHolderName"   => $data->bank_detail->accountHolderName,
                "details"    => array ( 
                    "legalType"     => "PRIVATE",
                    "swiftCode"     => $data->bank_detail->swiftCode,
                    "accountNumber" => $data->bank_detail->accountNumber,
                )
            ); 
        }
        return $recipient;        
    }    
