<?php
namespace App\Controllers\V1\Admin;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Admin fee
    Desc        : Modul ini di gunakan untuk mengatur fee pada masing-masing bank
    Sub fungsi  : 
        - getFee        : berfungsi mendapatkan Bank fee
        - setfee        : berfungsi mengatur Bank Fee
        
------------------------------------------------------------*/
class Fee extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->fee      = model('App\Models\V1\Admin\Mdl_fee');
	}
	
	public function getFee(){
	    $bank  = getBankId(apache_request_headers()["Authorization"]);
	    $currency   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
	    $result=$this->fee->get_single($bank->id,$currency);
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
	
	public function setfee(){
	    $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
					'wallet_sender_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Wallet Sender Fixed fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'wallet_sender_pct' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Wallet Sender Percentage fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'wallet_receiver_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Wallet Receiver Fixed fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'wallet_receiver_pct' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Wallet Receiver Percentage fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'walletbank_circuit_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Bank Circuit Fixed fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'walletbank_circuit_pct' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Bank Circuit Percentage fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'walletbank_outside_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Bank Outside Fixed fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'walletbank_outside_pct' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Wallet 2 Bank Outside Percentage fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'topup_circuit_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Topup percentage fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'topup_circuit_pct' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Topup circuit percentage is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'topup_outside_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Topup outside fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'topup_outside_pct' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Topup outside Percentage is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'referral_topup_fxd' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Topup fixed fee is required',
					        'decimal'       => 'Referral Topup fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Topup fee can not be negative'
					    ]
					],
					'referral_topup_pct' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Topup Percentage fee is required',
					        'decimal'       => 'Referral Topup fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Topup fee can not be negative'
					    ]
					],
					'referral_send_fxd' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Sender fixed fee is required',
					        'decimal'       => 'Referral Sender fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Sender fee can not be negative'
					    ]
					],
					'referral_send_pct' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Sender Percentage fee is required',
					        'decimal'       => 'Referral Sender fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Sender fee can not be negative'
					    ]
					],
					'referral_receive_fxd' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Recipient fixed fee is required',
					        'decimal'       => 'Referral Recipient fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Recipient fee can not be negative'
					    ]
					],
					'referral_receive_pct' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Recipient percentage fee is required',
					        'decimal'       => 'Referral Recipient fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Recipient fee can not be negative'
					    ]
					],
					'referral_bank_fxd' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Bank fixed fee is required',
					        'decimal'       => 'Referral Bank fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Bank fee can not be negative'
					    ]
					],
					'referral_bank_pct' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Referral Bank percentage fee is required',
					        'decimal'       => 'Referral Bank fee should in decimal',
					        'greater_than_equal_to'  => 'Referral Bank fee can not be negative'
					    ]
					],
					'card_fxd' => [
					    'rules'  => 'required|decimal|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Card fee is required',
					        'decimal'       => 'Card fee should in decimal',
					        'greater_than_equal_to'  => 'Card fee can not be negative'
					    ]
					],
					'currency' => [
					    'rules'  => 'required|min_length[3]|max_length[3]',
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
        
        $data           = $this->request->getJSON();
        
        $filters = array(
            'topup_circuit_fxd'      => FILTER_VALIDATE_FLOAT, 
            'topup_circuit_pct'      => FILTER_VALIDATE_FLOAT, 
            'topup_outside_fxd'      => FILTER_VALIDATE_FLOAT, 
            'topup_outside_pct'      => FILTER_VALIDATE_FLOAT, 
            'walletbank_circuit_fxd' => FILTER_VALIDATE_FLOAT, 
            'walletbank_circuit_pct' => FILTER_VALIDATE_FLOAT, 
            'walletbank_outside_fxd' => FILTER_VALIDATE_FLOAT, 
            'walletbank_outside_pct' => FILTER_VALIDATE_FLOAT, 
            'wallet_sender_fxd'      => FILTER_VALIDATE_FLOAT, 
            'wallet_sender_pct'      => FILTER_VALIDATE_FLOAT, 
            'wallet_receiver_fxd'    => FILTER_VALIDATE_FLOAT, 
            'wallet_receiver_pct'    => FILTER_VALIDATE_FLOAT, 
            'referral_send_fxd'      => FILTER_VALIDATE_FLOAT, 
            'referral_send_pct'      => FILTER_VALIDATE_FLOAT, 
            'referral_receive_fxd'   => FILTER_VALIDATE_FLOAT, 
            'referral_receive_pct'   => FILTER_VALIDATE_FLOAT, 
            'referral_topup_fxd'     => FILTER_VALIDATE_FLOAT, 
            'referral_topup_pct'     => FILTER_VALIDATE_FLOAT, 
            'referral_bank_fxd'      => FILTER_VALIDATE_FLOAT, 
            'referral_bank_pct'      => FILTER_VALIDATE_FLOAT, 
            'card_fxd'               => FILTER_VALIDATE_FLOAT, 
            'currency'               => FILTER_SANITIZE_STRING, 
        );
	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;  
        $mdata = array(
                "bank_id"                   => $bank->id,
                "topup_circuit_fxd"         => $data->topup_circuit_fxd,
                "topup_circuit_pct"         => $data->topup_circuit_pct,
                "topup_outside_fxd"         => $data->topup_outside_fxd,
                "topup_outside_pct"         => $data->topup_outside_pct,
                "walletbank_circuit_fxd"    => $data->walletbank_circuit_fxd,
                "walletbank_circuit_pct"    => $data->walletbank_circuit_pct,
                "walletbank_outside_fxd"    => $data->walletbank_outside_fxd,
                "walletbank_outside_pct"    => $data->walletbank_outside_pct,
                "wallet_sender_fxd"         => $data->wallet_sender_fxd,
                "wallet_sender_pct"         => $data->wallet_sender_pct,
                "wallet_receiver_fxd"       => $data->wallet_receiver_fxd,
                "wallet_receiver_pct"       => $data->wallet_receiver_pct,
    	        "referral_send_fxd"         => $data->referral_send_fxd,
    	        "referral_send_pct"         => $data->referral_send_pct,
    	        "referral_receive_fxd"      => $data->referral_receive_fxd,
    	        "referral_receive_pct"      => $data->referral_receive_pct,
    	        "referral_topup_fxd"        => $data->referral_topup_fxd,
    	        "referral_topup_pct"        => $data->referral_topup_pct,
    	        "referral_bank_fxd"         => $data->referral_bank_fxd,
    	        "referral_bank_pct"         => $data->referral_bank_pct,
    	        "card_fxd"                  => $data->card_fxd,
    	        "currency"                  => $data->currency,
    	);

    	$result=$this->fee->setfee($mdata);
    	if (@$result->code==5052){
            return $this->respond(@$result);
	    }
    	$response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Default fee successfully set"
	        ];
	   return $this->respond($response);
	}	
}
