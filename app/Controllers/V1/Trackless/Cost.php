<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Cost extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->cost      = model('App\Models\V1\Trackless\Mdl_cost');
	}
	
	public function getCost(){
	    $currency   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
	    $result=$this->cost->get_single($currency);
	    if (@$result->code==5052){
	            return $this->respond(@$result);
    	    }
    	$mdata=array(
    	    "currency"          => $result->currency,
    	    "topup_circuit_fxd" => $result->topup_circuit_fxd,
    	    "topup_circuit_pct" => $result->topup_circuit_pct,
    	    "topup_outside_fxd" => $result->topup_outside_fxd, 
    	    "topup_outside_pct" => $result->topup_outside_pct,
    	    "wallet_sender_fxd" => $result->wallet_sender_fxd,
    	    "wallet_sender_pct" => $result->wallet_sender_pct,
    	    "wallet_receiver_fxd"   => $result->wallet_receiver_fxd, 
    	    "wallet_receiver_pct"   => $result->wallet_receiver_pct, 
    	    "walletbank_circuit_fxd"    => $result->walletbank_circuit_fxd, 
    	    "walletbank_circuit_pct"    => $result->walletbank_circuit_pct, 
    	    "walletbank_outside_fxd"    => $result->walletbank_outside_fxd, 
    	    "walletbank_outside_pct"    => $result->walletbank_outside_pct, 
    	    "swap"                      => $result->swap, 
    	    "swap_fxd"                  => $result->swap_fxd, 
    	    "card_fxd"                  => $result->card_fxd, 
    	    "card_ship_reg"             => $result->card_ship_reg, 
    	    "card_ship_fast"            => $result->card_ship_fast, 
    	    "date_created"              => $result->date_created
    	    
    	    );
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => (object) $mdata
	        ];
	   return $this->respond($response);
	}
	
	public function setBankcost(){
	    $validation = $this->validation;
        $validation->setRules([
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
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
					'swap' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Swap fee Percentage is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'swap_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Swap fee Fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'card_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Card fee is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'card_ship_reg' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Card Ship regular fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'card_ship_fast' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Card ship fast fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'          => FILTER_SANITIZE_STRING, 
            'topup_circuit_fxd' => FILTER_VALIDATE_FLOAT, 
            'topup_circuit_pct' => FILTER_VALIDATE_FLOAT, 
            'topup_outside_fxd' => FILTER_VALIDATE_FLOAT, 
            'topup_outside_pct' => FILTER_VALIDATE_FLOAT, 
            'swap'              => FILTER_VALIDATE_FLOAT, 
            'swap_fxd'          => FILTER_VALIDATE_FLOAT, 
            'card_fxd'          => FILTER_VALIDATE_FLOAT, 
            'walletbank_circuit_fxd' => FILTER_VALIDATE_FLOAT, 
            'walletbank_circuit_pct' => FILTER_VALIDATE_FLOAT, 
            'walletbank_outside_fxd' => FILTER_VALIDATE_FLOAT, 
            'walletbank_outside_pct' => FILTER_VALIDATE_FLOAT, 
            'wallet_sender_fxd'      => FILTER_VALIDATE_FLOAT, 
            'wallet_sender_pct'      => FILTER_VALIDATE_FLOAT,
            'wallet_receiver_fxd'    => FILTER_VALIDATE_FLOAT, 
            'wallet_receiver_pct'    => FILTER_VALIDATE_FLOAT, 
            'card_ship_reg'          => FILTER_VALIDATE_FLOAT, 
            'card_ship_fast'         => FILTER_VALIDATE_FLOAT, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $mdata=array(
                "topup_circuit_fxd"     => $data->topup_circuit_fxd,
                "topup_circuit_pct"     => $data->topup_circuit_pct,
                "topup_outside_fxd"     => $data->topup_outside_fxd,
                "topup_outside_pct"     => $data->topup_outside_pct,
                "swap"                  => $data->swap,
                "swap_fxd"              => $data->swap_fxd,
                "card_fxd"              => $data->card_fxd,
                "walletbank_circuit_fxd"    => $data->walletbank_circuit_fxd,
                "walletbank_circuit_pct"    => $data->walletbank_circuit_pct,
                "walletbank_outside_fxd"    => $data->walletbank_outside_fxd,
                "walletbank_outside_pct"    => $data->walletbank_outside_pct,
                "wallet_sender_fxd"         => $data->wallet_sender_fxd,
                "wallet_sender_pct"         => $data->wallet_sender_pct,
                "wallet_receiver_fxd"       => $data->wallet_receiver_fxd,
                "wallet_receiver_pct"       => $data->wallet_receiver_pct,
                "card_ship_reg"             => $data->card_ship_reg,
                "card_ship_fast"            => $data->card_ship_fast,
                "currency"                  => $data->currency,
            );
            
        $result=$this->cost->set_bankcost($mdata);
	    if (@$result->code==5052){
	            return $this->respond(@$result);
    	    }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Fee Already Set"
	        ];
	   return $this->respond($response);
        
	}
	
	public function getWiseCost(){
	    $currency   = $this->request->getGet('currency', FILTER_SANITIZE_STRING);
	    $result=$this->cost->get_wise($currency);
	    if (@$result->code==5052){
	            return $this->respond(@$result);
    	    }
    	$mdata=array(
    	    "currency"              => $result->currency,
    	    "topup_circuit_fxd"     => $result->topup_circuit_fxd,
    	    "topup_circuit_pct"     => $result->topup_circuit_pct,
    	    "topup_outside_fxd"     => $result->topup_outside_fxd, 
    	    "topup_outside_pct"     => $result->topup_outside_pct,
    	    "transfer_circuit_fxd"  => $result->transfer_circuit_fxd, 
    	    "transfer_circuit_pct"  => $result->transfer_circuit_pct, 
    	    "transfer_outside_fxd"  => $result->transfer_outside_fxd, 
    	    "transfer_outside_pct"  => $result->transfer_outside_pct, 
    	    "card_fxd"              => $result->card_fxd, 
    	    "card_ship_reg"         => $result->card_ship_reg, 
    	    "card_ship_fast"        => $result->card_ship_fast, 
    	    );
    	    
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => (object) $mdata
	        ];
	   return $this->respond($response);
	}
	
	public function setWisecost(){
	    $validation = $this->validation;
        $validation->setRules([
					'currency' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
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
					'card_fxd' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Card Fee fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'card_ship_reg' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Card Ship regular fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
					'card_ship_fast' => [
					    'rules'  => 'required|greater_than_equal_to[0]',
					    'errors' =>  [
					        'required'      => 'Card ship fast fixed is required',
					        'greater_than_equal_to' => 'fee can not be negative'
					    ]
					],
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'          => FILTER_SANITIZE_STRING, 
            'topup_circuit_fxd' => FILTER_VALIDATE_FLOAT, 
            'topup_circuit_pct' => FILTER_VALIDATE_FLOAT, 
            'topup_outside_fxd' => FILTER_VALIDATE_FLOAT, 
            'topup_outside_pct' => FILTER_VALIDATE_FLOAT, 
            'walletbank_circuit_fxd' => FILTER_VALIDATE_FLOAT, 
            'walletbank_circuit_pct' => FILTER_VALIDATE_FLOAT, 
            'walletbank_outside_fxd' => FILTER_VALIDATE_FLOAT, 
            'walletbank_outside_pct' => FILTER_VALIDATE_FLOAT, 
            'card_fxd'               => FILTER_VALIDATE_FLOAT, 
            'card_ship_fast'         => FILTER_VALIDATE_FLOAT, 
            'card_ship_reg'          => FILTER_VALIDATE_FLOAT, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $mdata=array(
                "topup_cf_fxd"       => $data->topup_circuit_fxd,
                "topup_cf_pct"       => $data->topup_circuit_pct,
                "topup_ocf_fxd"      => $data->topup_outside_fxd,
                "topup_ocf_pct"      => $data->topup_outside_pct,
                "transfer_cf_fxd"    => $data->walletbank_circuit_fxd,
                "transfer_cf_pct"    => $data->walletbank_circuit_pct,
                "transfer_ocf_fxd"   => $data->walletbank_outside_fxd,
                "transfer_ocf_pct"   => $data->walletbank_outside_pct,
                "card_fxd"           => $data->card_fxd,
                "card_ship_fast"     => $data->card_ship_fast,
                "card_ship_reg"      => $data->card_ship_reg,
                "currency"           => $data->currency,
            );
            
        $result=$this->cost->set_wisecost($mdata);
	    if (@$result->code==5052){
	            return $this->respond(@$result);
    	    }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Bank Cost Already Set"
	        ];
	   return $this->respond($response);
        
	}	
}
