<?php
namespace App\Controllers\V1\Member;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Member Card
    Desc        : Modul ini di gunakan untuk aktifitas Card di sisi member
    Sub fungsi  : 
        - check_card    : mengecek apakah user sudah memiliki kartu aktif atau belum
        - activate_card : request card virtual ke wallester 
        - getFee        : mendapatkan fee untuk card
        - decodeCard    : mendecode cardnumber dan cvv dari wallester
        - getcardbalance: mengambil balance dari card wallester
        - topupprocess  : melakukan proses topup
        - getcardhistory: mendapatkan history transaksi penggunaan kartu
        - activate_physical_card : request card fisik ke wallester
------------------------------------------------------------*/

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA\PrivateKey;

class Card extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   

        $this->fee      = model('App\Models\V1\Admin\Mdl_fee');
        $this->cost     = model('App\Models\V1\Trackless\Mdl_cost');   
        $this->wallet   = model('App\Models\V1\Mdl_wallet');
        $this->card     = model('App\Models\V1\Mdl_card');
	}
	
	public function check_card(){
	    $userid     = $this->request->getGet('userid', FILTER_SANITIZE_STRING);
    	$result=$this->card->get_single($userid);
    	if (@$result->code==5052){
	        return $this->respond(@$result);
	    }
    	$response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $result
	        ];
        return $this->respond($response);
	}
	
	public function activate_card(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Userid is required',
					    ]
					],
					'ucode' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Unique Code is required',
					    ]
					],
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'phone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Phone number is required',
					    ]
					],
					'password' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => '3d secure password is required',
					    ]
					]
			]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'    => FILTER_SANITIZE_STRING, 
            'ucode'     => FILTER_SANITIZE_STRING, 
            'currency'  => FILTER_SANITIZE_STRING, 
            'phone'     => FILTER_SANITIZE_STRING, 
            'password'  => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered; 

        //cek sudah memiliki kartu atau belum
        $acc_card=$this->card->get_single($data->userid);
	    if (@$acc_card->card!='unavailable'){
	        $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => "Member already have a card"
	        ];
            return $this->respond($response);
	    }
	    
        //cek saldo EUR member
        $balance    = number_format((float) $this->wallet->get_balance($data->userid,'EUR'), 2, '.', '');

        $fee        = $this->getFee('EUR',$bank->id);
        $fee_bank   = number_format((float) $fee["fee_bank"], 2, '.', '');
        $cost_bank  = number_format((float) $fee["cost_bank"], 2, '.', '');

        //send to wallester directly
        $card_cost  = number_format((float) $fee["card_cost"], 2, '.', '');
        $deduct     = number_format((float) $fee_bank+$cost_bank+$card_cost, 2, '.', '');
        
        if ($balance < $deduct) {
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => "Insufficient Funds"
	        ];
            return $this->respond($response);
        }

        //create wallester account
        $cekaccount=$this->card->check_account($data->userid);
        $accountid="";
        $insertid="";
        if (@!$cekaccount){
            $acc_data=array(
                    "currency_code" => 'EUR',
                    "name"          => $data->ucode
                );
            $resultcreate=apicard(cardurl()->createaccount,$acc_data);
            if (@$resultcreate->body!=null){
                $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultcreate->body
    	        ];
                return $this->respond($response);
            }
            
            $accountid  = $resultcreate->account->id;
            $mdata=array(
                "id_member"     => $data->userid,
                "account_id"    => $accountid,
            );
        
            $insertid   = @$this->card->add($mdata);
        }else{
            $accountid  = $cekaccount->account_id;
            $insertid   = $cekaccount->insert_id;
        }

        //create card
        $card_data=array(
                "account_id"    => $accountid,
                "type"          => 'Virtual',
                "3d_secure_settings"    => array(
                        "mobile"        => $data->phone,
                        "password"      => $data->password,
                    ),
                "expiry_days"   => 365
            );

        $resultcard=apicard(cardurl()->createcard,$card_data);
        //cek;
        if (@$resultcard->body!=null){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => $resultcard->body
	        ];
            return $this->respond($response);
        }
        
        $mcard=array(
                "card_id"       => $resultcard->card->id,
                "pbs_cost"      => $cost_bank,
                "fee"           => $fee_bank,
                "card_cost"     => $card_cost,
                "lastdigit"     => substr($resultcard->card->masked_card_number,-4),
                "card_type"     => "virtual",
                "status"        => "active"
            );
        $result=$this->card->updatecard($mcard,$insertid);
        if (@$result->code==5055){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => $result
	        ];
            return $this->respond($response);
        }


        //get wallester bank
        $cardbank=$this->card->getBank('EUR');
        //kirim ke wallester menggunakan wise API
        $dataquote=array(
            	"sourceCurrency"    => 'EUR',
            	"targetCurrency"    => 'EUR',
            	"sourceAmount"      => null,
            	"targetAmount"      => $card_cost,
                "profile"           => $bank->wiseprofile
        );
        $jsonquote=json_encode($dataquote);
        $resultquote=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->quote,$jsonquote,$bank->wisekey);

        $bankdata= (object)array(
                "transfer_type"     => "circuit",
                "bank_detail"       => (object) array(
                    "accountHolderName" => $cardbank->registered_name,
                    "IBAN"              => $cardbank->iban,
                    "causal"            => $cardbank->causal,
                )
            );
        $data_recipient=dataEUR($bankdata);
        $jsonrecipient=json_encode($data_recipient);
        $resultrecipient=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->recipient,$jsonrecipient,$bank->wisekey);
        
        if (!empty($resultrecipient->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultrecipient->errors
    	        ];
    	    return $this->respond($response);
        }
        
        if (empty($resultrecipient->id) && empty($resultquote->id)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Something wrong, please try again later!"
    	        ];
    	    return $this->respond($response);
        }
        
        //transfer
        $data_transfer=array(
            "targetAccount" => $resultrecipient->id,
            "quoteUuid"     => $resultquote->id,
            "customerTransactionId" => $resultquote->id,
            "details" => array (
                "reference" => $bankdata->bank_detail->causal,
                "transferPurpose"   => "verification.source.of.funds.other",
                "sourceOfFunds"     => "Trust funds"
            )
        );
        $jsontransfer=json_encode($data_transfer);
        $resulttransfer=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->transfer,$jsontransfer,$bank->wisekey);

        if (!empty($resulttransfer->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resulttransfer->errors
    	        ];
    	    return $this->respond($response);
        }

        $data_fund=array(
            "type"  => "BALANCE"
        );
        $jsonfund=json_encode($data_fund);
        $resultfund=apiwise(urlapi(NULL,$resulttransfer->id,$bank->wiseprofile)->payment,$jsonfund,$bank->wisekey);

        if ($resultfund->status!="COMPLETED"){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultfund
    	        ];
    	    return $this->respond($response);
        }        
        
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                    "card_id"   => $resultcard->card->id,
	                    "account_id"=> $accountid,
	                    "exp_date"  => $resultcard->card->expiry_date,
	                ]
	        ];
	   return $this->respond($response);       
	}

	public function activate_physical_card(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Userid is required',
					    ]
					],
					'ucode' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Unique Code is required',
					    ]
					],
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
			]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'        => FILTER_SANITIZE_STRING, 
            'ucode'         => FILTER_SANITIZE_STRING, 
            'currency'      => FILTER_SANITIZE_STRING, 
            'card_detail'   => FILTER_DEFAULT
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="card_detail"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        
        $data=(object) $filtered; 

        //cek sudah memiliki kartu atau belum
        $acc_card=$this->card->get_single($data->userid);
	    if (@$acc_card->card!='unavailable'){
	        $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => "Member already have a card"
	        ];
            return $this->respond($response);
	    }
	    
        //cek saldo EUR member
        $balance    = number_format((float) $this->wallet->get_balance($data->userid,'EUR'), 2, '.', '');

        $fee        = $this->getFee('EUR',$bank->id);
        $fee_bank   = number_format((float) $fee["fee_bank"], 2, '.', '');
        $cost_bank  = number_format((float) $fee["cost_bank"], 2, '.', '');
        $card_cost  = number_format((float) $fee["card_cost"], 2, '.', '');
        
        if ($data->card_detail->delivery_address->dispatch_method=="DHLGlobalMail"){
            $ship_fee   = number_format($fee["ship_card_reg"], 2, '.', '');
            $pbs_ship   = number_format($fee["pbs_ship_reg"], 2, '.', '');
        }else{
            $ship_fee   = number_format($fee["ship_card_fast"], 2, '.', '');
            $pbs_ship   = number_format($fee["pbs_ship_fast"], 2, '.', '');
        }        
        $deduct     = number_format($fee_bank+$cost_bank+$card_cost+$ship_fee+$pbs_ship, 2, '.', '');
        
        if ($balance < $deduct) {
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => "Insufficient Funds"
	        ];
            return $this->respond($response);
        }

        //create wallester account
        $cekaccount=$this->card->check_account($data->userid);
        $accountid="";
        $insertid="";
        if (@!$cekaccount){
            $acc_data=array(
                    "currency_code" => 'EUR',
                    "name"          => $data->ucode
                );
            $resultcreate=apicard(cardurl()->createaccount,$acc_data);
            if (@$resultcreate->body!=null){
                $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultcreate->body
    	        ];
                return $this->respond($response);
            }
            
            $accountid  = $resultcreate->account->id;

            /*uncomment for debugging
            $accountid  = '123456';
            */
            $mdata=array(
                "id_member"     => $data->userid,
                "account_id"    => $accountid,
            );
            $insertid   = @$this->card->add($mdata);
            
            $mtransfer=array(
                    "from_account_id"   => "72f70e2f-e879-47f0-a269-8eff38fa6797", //<-- PBS ONLINE CARD
                    "to_account_id"     => $accountid,
                    "amount"            => $ship_fee,
                    "description"       => "shiping cost"
                );
            $resultcreate=apicard(cardurl()->transfer,$mtransfer);
        }else{
            $accountid  = $cekaccount->account_id;
            $insertid   = $cekaccount->insert_id;
        }

        //create card
        $card_data=$data->card_detail;

        $resultcard=apicard(cardurl()->createcard,$card_data);

        /*uncomment for debugging
        $resultcard=(object) array(
                "card" => (object) array(
                        "id"                    => "23456",
                        "masked_card_number"    => "4984********1299"
                    )
            );
        */
            
        //cek;
        if (@$resultcard->body!=null){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => $resultcard->body
	        ];
            return $this->respond($response);
        }
        
        $mcard=array(
                "card_id"       => $resultcard->card->id,
                "pbs_cost"      => $cost_bank,
                "fee"           => $fee_bank,
                "card_cost"     => $card_cost,
                "pbs_ship"      => $pbs_ship,
                "ship_card"     => $ship_fee,
                "lastdigit"     => substr($resultcard->card->masked_card_number,-4),
                "card_type"     => "physical",
                "status"        => "new"
            );
        $result=$this->card->updatecard($mcard,$insertid);
        if (@$result->code==5055){
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => $result
	        ];
            return $this->respond($response);
        }
        

        //get wallester bank
        $cardbank=$this->card->getBank('EUR');
        //kirim ke wallester menggunakan wise API
        $dataquote=array(
            	"sourceCurrency"    => 'EUR',
            	"targetCurrency"    => 'EUR',
            	"sourceAmount"      => null,
            	"targetAmount"      => number_format((float) $card_cost+$ship_fee, 2, '.', ''),
                "profile"           => $bank->wiseprofile
        );
        $jsonquote=json_encode($dataquote);
        $resultquote=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->quote,$jsonquote,$bank->wisekey);

        $bankdata= (object)array(
                "transfer_type"     => "circuit",
                "bank_detail"       => (object) array(
                    "accountHolderName" => $cardbank->registered_name,
                    "IBAN"              => $cardbank->iban,
                    "causal"            => $cardbank->causal,
                )
            );
        $data_recipient=dataEUR($bankdata);
        $jsonrecipient=json_encode($data_recipient);
        $resultrecipient=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->recipient,$jsonrecipient,$bank->wisekey);
        
        if (!empty($resultrecipient->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultrecipient->errors
    	        ];
    	    return $this->respond($response);
        }
        
        if (empty($resultrecipient->id) && empty($resultquote->id)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Something wrong, please try again later!"
    	        ];
    	    return $this->respond($response);
        }
        
        //transfer
        $data_transfer=array(
            "targetAccount" => $resultrecipient->id,
            "quoteUuid"     => $resultquote->id,
            "customerTransactionId" => $resultquote->id,
            "details" => array (
                "reference" => $bankdata->bank_detail->causal,
                "transferPurpose"   => "verification.source.of.funds.other",
                "sourceOfFunds"     => "Trust funds"
            )
        );
        $jsontransfer=json_encode($data_transfer);
        $resulttransfer=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->transfer,$jsontransfer,$bank->wisekey);

        if (!empty($resulttransfer->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resulttransfer->errors
    	        ];
    	    return $this->respond($response);
        }

        $data_fund=array(
            "type"  => "BALANCE"
        );
        $jsonfund=json_encode($data_fund);
        $resultfund=apiwise(urlapi(NULL,$resulttransfer->id, $bank->wiseprofile)->payment,$jsonfund,$bank->wisekey);

        if ($resultfund->status!="COMPLETED"){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultfund
    	        ];
    	    return $this->respond($response);
        }        
        
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                    "card_id"   => $resultcard->card->id,
	                    "account_id"=> $accountid,
	                    "exp_date"  => $resultcard->card->expiry_date,
	                ]
	        ];
	   return $this->respond($response);       
	}
	
	private function getFee($currency,$bankid) {
        $feedb  = $this->fee->get_single($bankid,$currency);
        $costdb = $this->cost->get_single($currency);
        $carddb = $this->cost->get_wise($currency);

        return array(
            "fee_bank"      => IS_NULL($feedb->card_fxd) ? 0 : $feedb->card_fxd, 
            "cost_bank"     => IS_NULL($costdb->card_fxd) ? 0 : $costdb->card_fxd, 
            "card_cost"     => IS_NULL($carddb->card_fxd) ? 0 : $carddb->card_fxd, 
            "ship_card_reg" => IS_NULL($carddb->card_ship_reg) ? 0 : $carddb->card_ship_reg, 
            "ship_card_fast"=> IS_NULL($carddb->card_ship_fast) ? 0 : $carddb->card_ship_fast, 
            "pbs_ship_reg"  => IS_NULL($costdb->card_ship_reg) ? 0 : $costdb->card_ship_reg,
            "pbs_ship_fast" => IS_NULL($costdb->card_ship_fast) ? 0 : $costdb->card_ship_fast);
    }	
    

    public function decodeCard(){
        $card_id     = $this->request->getGet('card_id', FILTER_SANITIZE_STRING);
         
        $private = RSA::createKey();
        $publicKey = $private->getPublicKey();
        $data=array(
                "public_key"=> base64_encode($publicKey),
            );
        
        /**GET CARD NUMBER HASH**/
        $result=apicard(cardurl($card_id)->gethashnumber,$data);
        $encCardNumber=$result->encrypted_card_number;

        $encCardNumber=str_replace("-----BEGIN CardNumber MESSAGE-----", "",$encCardNumber);
        $encCardNumber=str_replace("-----END CardNumber MESSAGE-----", "",$encCardNumber);
        $encCardNumber=str_replace("\n", "",$encCardNumber);

        $cardNumber = $private->withLabel("CardNumber")->decrypt(base64_decode($encCardNumber));
        
        /**GET CVV HASH**/
        $result=apicard(cardurl($card_id)->getcvvnumber,$data);
        $encCVV=$result->encrypted_cvv2;

        $encCVV=str_replace("-----BEGIN CVV2 MESSAGE-----", "",$encCVV);
        $encCVV=str_replace("-----END CVV2 MESSAGE-----", "",$encCVV);
        $encCVV=str_replace("\n", "",$encCVV);

        $cvv = $private->withLabel("CVV2")->decrypt(base64_decode($encCVV));
        
        $response=[
	            "cardnumber" => $cardNumber,
	            "cvv"        => $cvv
	        ];
	    
	   return $this->respond($response); 
    }
    
    public function getcardbalance(){
        $account_id  = $this->request->getGet('account_id', FILTER_SANITIZE_STRING);
        $card_id     = $this->request->getGet('card_id', FILTER_SANITIZE_STRING);
        $card        = apicard(cardurl($card_id)->getcard,NULL,"GET");
        $account     = apicard(cardurl($account_id)->getaccount,NULL,"GET");
        
        $response=[
	            "exp_date"      => $card->card->expiry_date,
	            "cardbalance"   => $account->account->available_amount
	        ];
	    
	   return $this->respond($response); 
    }
    
    public function topupprocess(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Userid is required',
					    ]
					],
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'amount' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Amount is required',
					    ]
					],
			]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'    => FILTER_SANITIZE_STRING, 
            'amount'    => FILTER_VALIDATE_FLOAT, 
            'currency'  => FILTER_SANITIZE_STRING, 
            'transfer_type'  => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered; 

        $amount     = number_format((float) $data->amount, 2, '.', '');
        
        if ($amount<0.02){
    	    $response = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum Topup is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format((float) $this->wallet->get_balance($data->userid,'EUR'), 2, '.', '');

        $bankwallet = new Wallet();
        $fee        = $bankwallet->getFee('EUR',$data->amount,'circuit',$bank->id);
        $fee_bank   = number_format((float)$fee["fee_bank"], 2, '.', '');
        $cost_bank  = number_format((float)$fee["cost_bank"], 2, '.', '');
        $wise_cost  = number_format((float)$fee["wise_cost"], 2, '.', '');
        $ref_bank   = number_format((float)$fee["referral_bank"], 2, '.', '');
        $komisi     = number_format((float)$fee_bank+$cost_bank+$wise_cost+$ref_bank, 2, '.', '');
        $deduct     = number_format((float)$amount+$komisi, 2, '.', '');
        
        if ($balance < $deduct) {
            $response=[
	            "code"     => "5055",
	            "error"    => "21",
	            "message"  => "Insufficient Funds"
	        ];
            return $this->respond($response);
        }
        
        //get wallester bank
        $cardbank=$this->card->getBank('EUR');
        //kirim ke wallester menggunakan wise API
        $dataquote=array(
            	"sourceCurrency"    => 'EUR',
            	"targetCurrency"    => 'EUR',
            	"sourceAmount"      => null,
            	"targetAmount"      => $amount,
                "profile"           => $bank->wiseprofile
        );

        $jsonquote=json_encode($dataquote);
        $resultquote=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->quote,$jsonquote,$bank->wisekey);

        $bankdata= (object)array(
                "transfer_type"     => "circuit",
                "bank_detail"       => (object) array(
                    "accountHolderName" => $cardbank->registered_name,
                    "IBAN"              => $cardbank->iban,
                    "causal"            => $cardbank->causal,
                )
            );
        $data_recipient=dataEUR($bankdata,$bank->wiseprofile);
        $jsonrecipient=json_encode($data_recipient);
        $resultrecipient=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->recipient,$jsonrecipient,$bank->wisekey);
        if (!empty($resultrecipient->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultrecipient->errors
    	        ];
    	    return $this->respond($response);
        }
        
        if (empty($resultrecipient->id) && empty($resultquote->id)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Something wrong, please try again later!"
    	        ];
    	    return $this->respond($response);
        }
        
        //transfer
        $data_transfer=array(
            "targetAccount" => $resultrecipient->id,
            "quoteUuid"     => $resultquote->id,
            "customerTransactionId" => $resultquote->id,
            "details" => array (
                "reference" => $bankdata->bank_detail->causal,
                "transferPurpose"   => "verification.source.of.funds.other",
                "sourceOfFunds"     => "Trust funds"
            )
        );
        $jsontransfer=json_encode($data_transfer);
        $resulttransfer=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->transfer,$jsontransfer,$bank->wisekey);
        
        if (!empty($resulttransfer->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resulttransfer->errors
    	        ];
    	    return $this->respond($response);
        }

        $data_fund=array(
            "type"  => "BALANCE"
        );
        $jsonfund=json_encode($data_fund);
        $resultfund=apiwise(urlapi(NULL,$resulttransfer->id,$bank->wiseprofile)->payment,$jsonfund,$bank->wisekey);

        if ($resultfund->status!="COMPLETED"){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => $resultfund
    	        ];
    	    return $this->respond($response);
        } 
        
        $transferdata = array(
            'sender_id'         => $data->userid,
            'currency'          => 'EUR',
            'type'              => 'circuit',
            'receiver_name'     => 'Wallester Topup',
            'iban'              => empty($bankdata->bank_detail->accountNumber)?$bankdata->bank_detail->IBAN : $bankdata->bank_detail->accountNumber,
            'bic'               => empty($bankdata->bank_detail->swiftCode)?NULL:$bankdata->bank_detail->swiftCode,
            'amount'            => $amount,
            'causal'            => $bankdata->bank_detail->causal,
            'pbs_cost'          => $cost_bank,
            'fee'               => $fee_bank,
            'referral_fee'      => $ref_bank,
            'wise_cost'         => $wise_cost,
            'is_card'           => 'yes'
        );
        
        $result = $this->wallet->wallet2bank($transferdata);
	    $response=[
	            "code"     => "200",
	            "error"    => NULL,
	            "message"  => "Transfer completed"
	        ];
	    return $this->respond($response);        
    }
    
    public function gethistory(){
        $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					],
					'date_start' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Start Date is required',
					    ]
					],
					'date_end' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'End Date is required',
					    ]
					],
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'        => FILTER_SANITIZE_STRING, 
            'date_start'    => FILTER_SANITIZE_STRING, 
            'date_end'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $card=$this->card->get_single($data->userid);
        $transaksi=apicard(cardurl($card->card_id,$data->date_start,$data->date_end)->getcardhistory,NULL,"GET");
        $response=[
	            "code"     => "200",
	            "error"    => NULL,
	            "message"  => $transaksi->transactions
	        ];
	    return $this->respond($response);
    }
    
    public function testapi(){
        $cardid=$_GET["cardid"];
/*        $acc_data=array(
                "currency_code" => 'EUR',
                "name"          => "Eddy Test"
            );
        $resultcreate=apicard(cardurl()->createaccount,$acc_data);
*/        
        //$cardid="fc006ded-8f56-4b36-bf8a-7574a5cf2664";
        //$cardid="73ac7d31-f4ab-4992-86f2-90f6c1b6584d";
        
        //$resultcreate=apicard(cardurl($cardid)->gethistory,NULL,"GET");
        $resultcreate=apicard(cardurl($cardid)->getcardbyaccount,NULL,"GET");
        
        $reason=array(
                "close_reason" => "ClosedBySystem"
            );
        //$cardid="31503b89-d86c-4c25-b534-7dd695b075dd";
        //$resultcreate=apicard(cardurl($cardid)->closecard,$reason,"PATCH");
        $resultcreate=apicard(cardurl($cardid)->closeaccount,NULL,"DELETE");
        //$resultcreate=apicard(cardurl()->searchaccount,NULL,"GET");
        print_r($resultcreate);
    }
}

