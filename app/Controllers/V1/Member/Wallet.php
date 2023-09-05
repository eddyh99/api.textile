<?php
namespace App\Controllers\V1\Member;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Member Withdraw
    Desc        : Modul ini di gunakan untuk aktifitas withdraw di sisi Member/User
                  Withdraw di sisi member akan dikenakan Bank Fee & TC Bank Fee
    Sub fungsi  : 
        - getAllbank    : berfungsi mengambil semua bank yg terdaftar
        - getBalance    : berfungsi mendapatkan cek balance member
        - getBankCode   : berfungsi mengambil kode bank dari wise untuk currency tertentu
        - getBranchCode : berfungsi mengambil kode cabang bank dari wise untuk currency tertentu
        - bankSummary   : berfungsi mendapatkan informasi Fee ketika mau melakukan transfer bank
        - bankTransfer  : berfungsi memproses transfer ke wise
        - getSummary    : berfungsi mendapatkan informasi fee ketika mau melakukan wallet 2 wallet
        - walletTransfer: berfungsi memproses transfer wallet 2 wallet
        - topup         : berfungsi mempersiapkan topup dan bank detail
------------------------------------------------------------*/

class Wallet extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        /*----------------------------------------------------------
            wallet              => untuk cek balance member
            fee                 => untuk cek Bank Fee
            member              => untuk cek data member
            trackless\cost      => untuk ambil cost TC Bank & Wise Fee
        ------------------------------------------------------------*/
        
        $this->cost     = model('App\Models\V1\Trackless\Mdl_cost');        
        $this->tcmember = model('App\Models\V1\Trackless\Mdl_bank');
        $this->fee      = model('App\Models\V1\Admin\Mdl_fee');
        $this->wallet   = model('App\Models\V1\Mdl_wallet');
        $this->member   = model('App\Models\V1\Mdl_member');
	}
	
	public function getAllbank(){
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $this->tcmember->getTCbank()
	        ];
        return $this->respond($response);
	}
	
	//mendapatkan balance member
	public function getBalance(){
	    $currency   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
	    $userid     = $this->request->getGet('userid', FILTER_SANITIZE_STRING);

    	$result = $this->wallet->get_balance($userid,$currency);
    	$response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => [
	                    "balance"   => $result
	                ]
	        ];
        return $this->respond($response);
	    
	}

    //mendapatkan kode bank untuk currency tertentu
    public function getBankCode(){
	    $country    = $this->request->getGet('country', FILTER_SANITIZE_STRING);

    	$response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => apiwise(urlapi($country)->bankcode,NULL,$bank->wisekey,"GET")
	        ];
        return $this->respond($response);
    }

    //mendapatkan kode cabang untuk currency tertentu
    public function getBranchCode(){
	    $country   = $this->request->getGet('country', FILTER_SANITIZE_STRING);
	    $bankcode  = $this->request->getGet('bankcode', FILTER_SANITIZE_STRING);

    	$response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => apiwise(urlapi($country,$bankcode)->branchcode,NULL,$bank->wisekey,"GET")

	        ];
        return $this->respond($response);
    }
	
	//pre transfer bank
	public function bankSummary(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);

	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					],
                    'amount' => [
					    'rules'  => 'required|decimal|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Amount is required',
					        'greater_than'  => 'Amount can not be negative',
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
					'transfer_type' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Transfer type is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'        => FILTER_SANITIZE_STRING, 
            'amount'        => FILTER_VALIDATE_FLOAT, 
            'currency'      => FILTER_SANITIZE_STRING, 
            'transfer_type' => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $amount     = number_format($data->amount,2,'.','');
        if ($amount<0.02){
    	    $response = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum Transfer is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format($this->wallet->get_balance($data->userid,$data->currency),2,'.','');

        $fee        = $this->getFee($data->currency,$data->amount,$data->transfer_type,$bank->id);
        $fee_bank   = number_format($fee["fee_bank"],2,'.','');
        $cost_bank  = number_format($fee["cost_bank"],2,'.','');
        $wise_cost  = number_format($fee["wise_cost"],2,'.','');
        $ref_bank   = number_format($fee["referral_bank"],2,'.','');
        $komisi     = number_format($fee_bank+$cost_bank+$wise_cost+$ref_bank,2,'.','');
        $deduct     = number_format($amount+$komisi,2,'.','');

        if ($balance < $deduct) {
            $response=[
	            "code"     => "5056",
	            "error"    => "21",
	            "message"  => "Insufficient Fund"
	        ];
            return $this->respond($response);
        }

        $response=[
            "code"     => "200",
            "error"    => null,
            "message"  => [
                "transfer_type" => $data->transfer_type,
                "amount"    => $amount,
                "deduct"    => $deduct,
                "fee"       => $komisi
            ]
        ];
        return $this->respond($response);        
    }
    
    //memproses tranfer wise
    public function bankTransfer(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
        
        $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					],
                    'amount' => [
					    'rules'  => 'required|decimal|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Amount is required',
					        'greater_than'  => 'Amount can not be negative',
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
					'transfer_type' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Transfer type is required',
					    ]
					],
					'bank_detail' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Bank detail is required',
					    ]
					]
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();

	    $filters = array(
            'userid'        => FILTER_SANITIZE_STRING, 
            'amount'        => FILTER_VALIDATE_FLOAT, 
            'currency'      => FILTER_SANITIZE_STRING, 
            'transfer_type' => FILTER_SANITIZE_STRING, 
            'bank_detail'   => FILTER_DEFAULT
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
            if ($key!="bank_detail"){
                $filtered[$key] = filter_var($value, $filters[$key]);
            }else{
                $filtered[$key] = $value;
            }
        }
        
        $data=(object) $filtered;
        $amount     = number_format($data->amount,2,'.','');
        if ($amount<0.02){
    	    $response = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum Transfer is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format($this->wallet->get_balance($data->userid,$data->currency),2,'.','');

        $fee        = $this->getFee($data->currency,$data->amount,$data->transfer_type,$bank->id);
        $fee_bank   = number_format($fee["fee_bank"],2,'.','');
        $cost_bank  = number_format($fee["cost_bank"],2,'.','');
        $wise_cost  = number_format($fee["wise_cost"],2,'.','');
        $ref_bank   = number_format($fee["referral_bank"],2,'.','');
        $komisi     = number_format($fee_bank+$cost_bank+$wise_cost+$ref_bank,2,'.','');
        $deduct     = number_format($amount+$komisi,2,'.','');

        if ($balance < $deduct) {
            $response=[
	            "code"     => "5056",
	            "error"    => "21",
	            "message"  => "Insufficient Fund"
	        ];
            return $this->respond($response);
        }
        
        if ($data->transfer_type=="circuit"){
            $dataquote=array(
            	"sourceCurrency"    => $data->currency,
            	"targetCurrency"    => $data->currency,
            	"sourceAmount"      => null,
            	"targetAmount"      => $amount,
                "profile"           => $bank->wiseprofile
                );
        }elseif ($data->transfer_type=="outside"){
            $dataquote=array(
            	"sourceCurrency"    => $data->currency,
            	"targetCurrency"    => $data->currency,
            	"sourceAmount"      => $amount,
            	"targetAmount"      => null,
                "profile"           => $bank->wiseprofile
                );
        }

        $jsonquote=json_encode($dataquote);
        $resultquote=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->quote,$jsonquote,$bank->wisekey);
         
        if ($data->currency=="USD"){
            $data_recipient=dataUSD($data,$bank->wiseprofile);
        }elseif ($data->currency=="EUR"){
            $data_recipient=dataEUR($data,$bank->wiseprofile);
        }elseif ($data->currency=="AED"){
            $data_recipient=dataAED($data,$bank->wiseprofile);
        }elseif ($data->currency=="ARS"){
            $data_recipient=dataARS($data,$bank->wiseprofile);
        }elseif ($data->currency=="AUD"){
            $data_recipient=dataAUD($data,$bank->wiseprofile);
        }elseif ($data->currency=="BDT"){
            $data_recipient=dataBDT($data,$bank->wiseprofile);
        }elseif ($data->currency=="BGN"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="CAD"){
            $data_recipient=dataCAD($data,$bank->wiseprofile);
        }elseif ($data->currency=="CHF"){
            $data_recipient=dataCHF($data,$bank->wiseprofile);
        }elseif ($data->currency=="CLP"){
            $data_recipient=dataCLP($data,$bank->wiseprofile);
        }elseif ($data->currency=="CNY"){
            $data_recipient=dataCNY($data,$bank->wiseprofile);
        }elseif ($data->currency=="CZK"){
            $data_recipient=dataCZK($data,$bank->wiseprofile);
        }elseif ($data->currency=="DKK"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="EGP"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="GBP"){
            $data_recipient=dataGBP($data,$bank->wiseprofile);
        }elseif ($data->currency=="GEL"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="GHS"){
            $data_recipient=dataGHS($data,$bank->wiseprofile);
        }elseif ($data->currency=="HKD"){
            $data_recipient=dataHKD($data,$bank->wiseprofile);
        }elseif ($data->currency=="HRK"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="HUF"){
            $data_recipient=dataHUF($data,$bank->wiseprofile);
        }elseif ($data->currency=="IDR"){
            $data_recipient=dataIDR($data,$bank->wiseprofile);
        }elseif ($data->currency=="ILS"){
            $data_recipient=dataILS($data,$bank->wiseprofile);
        }elseif ($data->currency=="INR"){
            $data_recipient=dataINR($data,$bank->wiseprofile);
        }elseif ($data->currency=="JPY"){
            $data_recipient=dataJPY($data,$bank->wiseprofile);
        }elseif ($data->currency=="KES"){
            $data_recipient=dataKES($data,$bank->wiseprofile);
        }elseif ($data->currency=="KRW"){
            $data_recipient=dataKRW($data,$bank->wiseprofile);
        }elseif ($data->currency=="LKR"){
            $data_recipient=dataLKR($data,$bank->wiseprofile);
        }elseif ($data->currency=="MAD"){
            $data_recipient=dataMAD($data,$bank->wiseprofile);
        }elseif ($data->currency=="MXN"){
            $data_recipient=dataMXN($data,$bank->wiseprofile);
        }elseif ($data->currency=="MYR"){
            $data_recipient=dataMYR($data,$bank->wiseprofile);
        }elseif ($data->currency=="NGN"){
            $data_recipient=dataNGN($data,$bank->wiseprofile);
        }elseif ($data->currency=="NOK"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="NPR"){
            $data_recipient=dataNPR($data,$bank->wiseprofile);
        }elseif ($data->currency=="NZD"){
            $data_recipient=dataNZD($data,$bank->wiseprofile);
        }elseif ($data->currency=="PHP"){
            $data_recipient=dataPHP($data,$bank->wiseprofile);
        }elseif ($data->currency=="PKR"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="PLN"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="RON"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="SEK"){
            $data_recipient=dataIBAN($data,$bank->wiseprofile);
        }elseif ($data->currency=="SGD"){
            $data_recipient=dataSGD($data,$bank->wiseprofile);
        }elseif ($data->currency=="THB"){
            $data_recipient=dataTHB($data,$bank->wiseprofile);
        }elseif ($data->currency=="TRY"){
            $data_recipient=dataTRY($data,$bank->wiseprofile);
        }elseif ($data->currency=="UAH"){
            $data_recipient=dataUAH($data,$bank->wiseprofile);
        }elseif ($data->currency=="VND"){
            $data_recipient=dataVND($data,$bank->wiseprofile);
        }elseif ($data->currency=="ZAR"){
            $data_recipient=dataZAR($data,$bank->wiseprofile);
        }
        
        if (empty($data_recipient)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Currency not supported"
    	        ];
    	    return $this->respond($response);
        }

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
                "reference" => $data->bank_detail->causal,
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
        
        
        $mdata = array(
            'sender_id'         => $data->userid,
            'currency'          => $data->currency,
            'type'              => $data->transfer_type,
            'receiver_name'     => $data->bank_detail->accountHolderName,
            'iban'              => empty($data->bank_detail->accountNumber)?$data->bank_detail->IBAN : $data->bank_detail->accountNumber,
            'bic'               => empty($data->bank_detail->swiftCode)?NULL:$data->bank_detail->swiftCode,
            'amount'            => $amount,
            'causal'            => $data->bank_detail->causal,
            'pbs_cost'          => $cost_bank,
            'fee'               => $fee_bank,
            'referral_fee'      => $ref_bank,
            'wise_cost'         => $wise_cost
        );
        
        $result = $this->wallet->wallet2bank($mdata);
	    $response=[
	            "code"     => "200",
	            "error"    => NULL,
	            "message"  => "Transfer completed"
	        ];
	    return $this->respond($response);
    }
    
    //mendapatkan fee untuk tranfer bank atau wallet 2 wallet
    public function getFee($currency,$amount,$type=NULL, $bankid) {
        $feedb  = $this->fee->get_single($bankid,$currency);
        $costdb = $this->cost->get_single($currency);
        $wisedb = $this->cost->get_wise($currency);

        $fee_bank       = 0;
        $fee_sender     = 0;
        $fee_receiver   = 0;
        $cost_bank      = 0;
        $cost_sender    = 0;
        $cost_receiver  = 0;
        $referral_send  = 0;
        $referral_receive=0;
        $referral_bank  = 0;
        $wise_cost      = 0;
        
        if (empty($type)){
            $fee_bank       = 0;
            $cost_bank      = 0;
            $referral_bank  = 0;
            $wise_cost      = 0;
            
            $fee_sender     = number_format(($amount*$feedb->wallet_sender_pct),2,'.','')+$feedb->wallet_sender_fxd;
            $fee_receiver   = number_format(($amount*$feedb->wallet_receiver_pct),2,'.','')+$feedb->wallet_receiver_fxd;

            $cost_sender    = number_format(($amount*$costdb->wallet_sender_pct),2,'.','')+$costdb->wallet_sender_fxd;
            $cost_receiver  = number_format(($amount*$costdb->wallet_receiver_pct),2,'.','')+$costdb->wallet_receiver_fxd;
            
            $referral_send      = number_format(($amount*$feedb->referral_send_pct),2,'.','')+$feedb->referral_send_fxd;
            $referral_receive   = number_format(($amount*$feedb->referral_receive_pct),2,'.','')+$feedb->referral_receive_fxd;
        }elseif ($type=="circuit"){
            $fee_sender     = 0;
            $fee_receiver   = 0;
            $cost_sender    = 0;
            $cost_receiver  = 0;
            
            $fee_bank       = number_format(($amount*$feedb->walletbank_circuit_pct),2,'.','')+$feedb->walletbank_circuit_fxd;
            $cost_bank      = number_format(($amount*$costdb->walletbank_circuit_pct),2,'.','')+$costdb->walletbank_circuit_fxd;
            $referral_bank  = number_format(($amount*$feedb->referral_bank_pct),2,'.','')+$feedb->referral_bank_fxd;
            $wise_cost      = number_format(($amount*$wisedb->transfer_circuit_pct),2,'.','')+$wisedb->transfer_circuit_fxd;
            
        }elseif ($type=="outside"){
            $fee_sender     = 0;
            $fee_receiver   = 0;
            $cost_sender    = 0;
            $cost_receiver  = 0;
            $fee_bank       = number_format(($amount*$feedb->walletbank_outside_pct),2,'.','')+$feedb->walletbank_outside_fxd;
            $cost_bank      = number_format(($amount*$costdb->walletbank_outside_pct),2,'.','')+$costdb->walletbank_outside_fxd;
            $referral_bank  = number_format(($amount*$feedb->referral_bank_pct),2,'.','')+$feedb->referral_bank_fxd;
            $wise_cost      = number_format(($amount*$wisedb->transfer_outside_pct),2,'.','')+$wisedb->transfer_outside_fxd;
            
        }elseif ($type=="topup circuit"){
            $fee_bank       = number_format(($amount*$feedb->topup_circuit_pct),2,'.','')+$feedb->topup_circuit_fxd;
            $cost_bank      = number_format(($amount*$costdb->topup_circuit_pct),2,'.','')+$costdb->topup_circuit_fxd;
            $referral_bank  = number_format(($amount*$feedb->referral_topup_pct),2,'.','')+$feedb->referral_topup_fxd;
        }elseif ($type=="topup outside"){
            $fee_bank       = number_format(($amount*$feedb->topup_outside_pct),2,'.','')+$feedb->topup_outside_fxd;
            $cost_bank      = number_format(($amount*$costdb->topup_outside_pct),2,'.','')+$costdb->topup_outside_fxd;
            $referral_bank  = number_format(($amount*$feedb->referral_topup_pct),2,'.','')+$feedb->referral_topup_fxd;
        }

        $data=array(
                "fee_sender"        => $fee_sender,
                "fee_receiver"      => $fee_receiver,
                "fee_bank"          => $fee_bank,
                "referral_send"     => $referral_send,
                "referral_receive"  => $referral_receive,
                "referral_bank"     => $referral_bank,
                "cost_sender"       => $cost_sender,
                "cost_receiver"     => $cost_receiver,
                "cost_bank"         => $cost_bank,
                "wise_cost"         => $wise_cost
                );
        return $data;
    }	    
    
    //pre proses wallet 2 wallet
    public function getSummary(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);

	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					],
                    'amount' => [
					    'rules'  => 'required|decimal|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Amount is required',
					        'greater_than'  => 'Amount can not be negative',
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
					'ucode' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Recipient Unique Code is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'        => FILTER_SANITIZE_STRING, 
            'amount'        => FILTER_VALIDATE_FLOAT, 
            'currency'      => FILTER_SANITIZE_STRING, 
            'ucode'         => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered; 
        
        $recipient_id=$this->member->getby_ucode($data->ucode,$data->userid);
        if (@$recipient_id->code=="5051"){
    	    return $this->respond($recipient_id);
        }
        
        $amount     = number_format($data->amount,2,'.','');
        
        if ($amount<0.02){
    	    $response = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum Tranfer is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format($this->wallet->get_balance($data->userid,$data->currency),2,'.','');
        $fee        = $this->getFee($data->currency,$data->amount, NULL, $bank->id);
        $cost_sender= number_format($fee['cost_sender'],2,'.','');
        $fee_sender = number_format($fee['fee_sender'],2,'.','');
        $ref_send   = number_format($fee['referral_send'],2,'.','');
        $komisi     = number_format($cost_sender+$fee_sender+$ref_send,2,'.','');
        $deduct     = number_format($amount + $komisi,2,'.','');
        
        if ($balance < $deduct) {
            $response=[
	            "code"     => "5056",
	            "error"    => "21",
	            "message"  => "Insufficient Fund"
	        ];
            return $this->respond($response);
        }

        $response=[
            "code"     => "200",
            "error"    => NULL,
            "message"  => [
                    "amount"    => $amount,
                    "deduct"    => $deduct,
                    "fee"       => $komisi
                ]
        ];
        return $this->respond($response);

    }
    
    //memproses wallet 2 wallet
    public function walletTransfer(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
        
	    $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					],
                    'amount' => [
					    'rules'  => 'required|decimal|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Amount is required',
					        'greater_than'  => 'Amount can not be negative',
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
					'ucode' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Recipient Unique Code is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'        => FILTER_SANITIZE_STRING, 
            'amount'        => FILTER_VALIDATE_FLOAT, 
            'currency'      => FILTER_SANITIZE_STRING, 
            'ucode'         => FILTER_SANITIZE_STRING, 
            'causal'        => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered; 
        
        $recipient_id=$this->member->getby_ucode($data->ucode,$data->userid);
        if (@$recipient_id->code=="5051"){
    	    return $this->respond($recipient_id);
        }

    	$amount     = number_format($data->amount,2,'.','');
        if ($amount<0.02){
    	    $response = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum Transfer is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format($this->wallet->get_balance($data->userid,$data->currency),2,'.','');
        $fee        = $this->getFee($data->currency,$data->amount, NULL, $bank->id);
        $cost_sender= number_format($fee['cost_sender'],2,'.','');
        $fee_sender = number_format($fee['fee_sender'],2,'.','');
        $ref_send   = number_format($fee['referral_send'],2,'.','');
        $komisi     = number_format($cost_sender+$fee_sender+$ref_send,2,'.','');
        $deduct     = number_format($amount + $komisi,2,'.','');
        
        if ($balance < $deduct) {
            $response=[
	            "code"     => "5056",
	            "error"    => "21",
	            "message"  => "Insufficient Fund"
	        ];
            return $this->respond($response);
        }
        
        $fee_rv=$this->getFee($data->currency,$data->amount,NULL,$recipient_id->bank_id);
        $mdata=array(
                "sender_id"         => $data->userid,
                "receiver_id"       => $recipient_id->id,
                "amount"            => $data->amount,
                "currency"          => $data->currency,
                "pbs_sender_cost"   => number_format($fee["cost_sender"],2,'.',''),
                "pbs_receiver_cost" => number_format($fee_rv["cost_receiver"],2,'.',''),
                "sender_fee"        => number_format($fee["fee_sender"],2,'.',''),
                "receiver_fee"      => number_format($fee_rv["fee_receiver"],2,'.',''),
                "referral_sender_fee"   => number_format($fee["referral_send"],2,'.',''),
                "referral_receiver_fee" => number_format($fee_rv["referral_receive"],2,'.',''),
                "causal"            => $data->causal
            );

        $result=$this->wallet->wallet2wallet($mdata);

        if (@$result->code=="5055"){
            $this->respond($result);
        }

        $response=[
            "code"     => "200",
            "error"    => NULL,
            "message"  => "Wallet Transfer is completed"
        ];
        return $this->respond($response);

    }
    
    public function topup(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
        
        $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					],
                    'amount' => [
					    'rules'  => 'required|decimal|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Amount is required',
					        'greater_than'  => 'Amount can not be negative',
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
					'transfer_type' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Transfer type is required',
					    ]
					],
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();

	    $filters = array(
            'userid'        => FILTER_SANITIZE_STRING, 
            'amount'        => FILTER_VALIDATE_FLOAT, 
            'currency'      => FILTER_SANITIZE_STRING, 
            'transfer_type' => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
            $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        $amount     = number_format($data->amount,2,'.','');
        $minamount  = number_format($this->tcmember->getby_currency($data->currency)->minimum,2,'.','');
        if ($amount<$minamount){
    	    $response = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum Transfer is ".$minamount
    	        ];
    	    return $this->respond($response);
        }

        $fee        = $this->getFee($data->currency,$data->amount,$data->transfer_type,$bank->id);
        $fee_bank   = number_format($fee["fee_bank"],2,'.','');
        $cost_bank  = number_format($fee["cost_bank"],2,'.','');
        $wise_cost  = number_format($fee["wise_cost"],2,'.','');
        $ref_bank   = number_format($fee["referral_bank"],2,'.','');
        $user       = $this->member->getby_id($data->userid);

        if (@$user->code==5051){
            $response = [
    	            "code"     => "5055",
    	            "error"    => "22",
    	            "message"  => "User id not found"
    	        ];
    	    return $this->respond($response);
        }

        $dataquote=array(
        	"sourceCurrency"    => $data->currency,
        	"targetCurrency"    => $data->currency,
        	"sourceAmount"      => $amount,
        	"targetAmount"      => null,
            "profile"           => $bank->wiseprofile
            );

        $jsonquote=json_encode($dataquote);
        $resultquote=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->quote,$jsonquote,$bank->wisekey);
        $wisebank = apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->bankdetail,NULL,$bank->wisekey,"GET");

        foreach ($wisebank as $dt){
            if (($dt->currency->code=="EUR") && ($data->currency=="EUR")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="IBAN"){
                                $iban   = str_replace(" ",'',$detail->body);
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }
                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "IBAN"              => (getenv('api_mode')=='sandbox') ? "BE19967241233912":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                    
                    if (($rcv->type=="INTERNATIONAL") && ($data->transfer_type=="topup outside")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="IBAN"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="SWIFT_CODE"){
                                $swift  = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }
                        $bankdata= (object)array(
                            "transfer_type"     => "outside",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "swiftCode"         => (getenv('api_mode')=='sandbox') ? "TRWIBEB1XXX":$swift,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "BE19967241233912":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
            elseif (($dt->currency->code=="USD")  && ($data->currency=="USD")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_TYPE"){
                                $acctype    = $detail->body;
                            }elseif ($detail->type=="ROUTING_NUMBER"){
                                $routing    = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }
                        
                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "abartn"            => (getenv('api_mode')=='sandbox') ? "084009519":$routing,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "9600001684642699":$iban,
                                "accountType"       => $acctype,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                    
                    if (($rcv->type=="INTERNATIONAL") && ($data->transfer_type=="topup outside")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="SWIFT_CODE"){
                                $swift  = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }
                        
                        $bankdata= (object)array(
                            "transfer_type"     => "outside",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "swiftCode"         => (getenv('api_mode')=='sandbox') ? "CMFGUS33":$swift,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "822000321142":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
            elseif (($dt->currency->code=="GBP")  && ($data->currency=="GBP")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="BANK_CODE"){
                                $routing    = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }
                      
                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "sortCode"          => (getenv('api_mode')=='sandbox') ? "23-14-70":$routing,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "37532981":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                    
                    if (($rcv->type=="INTERNATIONAL") && ($data->transfer_type=="topup outside")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="IBAN"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }
                        
                        $bankdata= (object)array(
                            "transfer_type"     => "outside",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "IBAN"              => (getenv('api_mode')=='sandbox') ? "GB33TRWI23147037532981":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
            elseif (($dt->currency->code=="AUD")  && ($data->currency=="AUD")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="BANK_CODE"){
                                $routing    = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }

                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "bsbCode"           => (getenv('api_mode')=='sandbox') ? "802-985":$routing,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "512901816":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
            elseif (($dt->currency->code=="NZD")  && ($data->currency=="NZD")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }

                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "04-2021-0097947-89":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
            elseif (($dt->currency->code=="CAD")  && ($data->currency=="CAD")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->title=="Institution number"){
                                $routing    = $detail->body;
                            }elseif ($detail->title=="Transit number"){
                                $transit    = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }

                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "instituionNumber"  => (getenv('api_mode')=='sandbox') ? "621":$routing,
                                "transitNumber"     => (getenv('api_mode')=='sandbox') ? "16001":$transit,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "200110162779":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
            elseif (($dt->currency->code=="HUF")  && ($data->currency=="HUF")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }

                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "12600016-11115385-08723573":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
            elseif (($dt->currency->code=="SGD")  && ($data->currency=="SGD")){
                foreach ($dt->receiveOptions as $rcv){
                    if (($rcv->type=="LOCAL") && ($data->transfer_type=="topup circuit")){
                        foreach ($rcv->details as $detail){
                            if ($detail->type=="ACCOUNT_HOLDER"){
                                $holder = $detail->body;
                            }elseif ($detail->type=="ACCOUNT_NUMBER"){
                                $iban   = $detail->body;
                            }elseif ($detail->type=="BANK_CODE"){
                                $routing    = $detail->body;
                            }elseif ($detail->type=="ADDRESS"){
                                $address = $detail->body;
                            }
                        }

                        $bankdata= (object)array(
                            "transfer_type"     => "circuit",
                            "bank_detail"       => (object) array(
                                "accountHolderName" => $holder,
                                "bankCode"          => (getenv('api_mode')=='sandbox') ? "0516":$routing,
                                "accountNumber"     => (getenv('api_mode')=='sandbox') ? "336-515-2":$iban,
                                "address"           => $address,
                                "causal"            => "RCPT".substr(time(),-6),
                            )
                        );
                        break;
                    }
                }
            }
        }
         
        $mdata=array(
                "id_member" => $data->userid,
                "amount"    => $data->amount,
                "currency"  => $data->currency,
                "pbs_cost"  => $cost_bank,
                "fee"       => $fee_bank,
                "referral_fee"  => $ref_bank,
                "admin_id"  => 2,
                "wise_id"   => NULL,
                "causal"    =>  $bankdata->bank_detail->causal,
                "is_proses" => 'no'
        );
        $this->wallet->topup_wallet($mdata);
        
	    $response=[
	            "code"     => "200",
	            "error"    => NULL,
	            "message"  => $bankdata,
	        ];
	    return $this->respond($response);
    }
    
}
