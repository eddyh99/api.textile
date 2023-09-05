<?php
namespace App\Controllers\V1\Admin;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Admin Withdraw
    Desc        : Modul ini di gunakan untuk aktifitas withdraw di sisi admin bank
                  Withdraw di sisi admin bank akan dikenakan TC Bank Fee dan Wise Fee
    Sub fungsi  : 
        - withdrawSummary   : berfungsi mendapatkan informasi Fee Transfer
        - getFee            : berfungsi mendapatkan TC Fee & Wise fee
        - withdrawTransfer  : berfungsi memproses transfer wise
        
------------------------------------------------------------*/


class Withdraw extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        /*----------------------------------------------------------
            admin\wallet        => untuk cek balance admin dan simpan withdraw
            trackless\cost      => untuk ambil cost default TC bank & Wise fee
        ------------------------------------------------------------*/
        
        $this->wallet       = model('App\Models\V1\Admin\Mdl_wallet');
        $this->cost         = model('App\Models\V1\Trackless\Mdl_cost');        
	}
	
	//pre withdraw transfer
	public function withdrawSummary(){
	    $bank  = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
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
    	            "message"  => "Minimum Withdraw is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format($this->wallet->balance_bycurrency($bank->id,$data->currency),2,'.','');

        $fee        = $this->getFee($data->currency,$amount);
        $wise_cost  = 0;
        $tc_cost    = 0;
        $deduct     = 0;

        if ($data->transfer_type=="circuit"){
            $wise_cost  = number_format($fee["wise_circuit"],2,'.','');
            $tc_cost    = number_format($fee['cost_circuit'],2,'.','');
        }elseif($data->transfer_type="outside"){
            $wise_cost  = number_format($fee["wise_outside"],2,'.','');
            $tc_cost    = number_format($fee['cost_outside'],2,'.','');
        }
        
        $deduct     = number_format($amount + $wise_cost+$tc_cost);
        if ($balance < $deduct) {
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "20",
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
                "fee"       => number_format($wise_cost+$tc_cost,2,'.','')
            ]
        ];
        return $this->respond($response);        
    }
    
    //withdraw proses wise
    public function withdrawTransfer(){
	    $bank  = getBankId(apache_request_headers()["Authorization"]);
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
    	            "message"  => "Minimum Withdraw is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format($this->wallet->balance_bycurrency($bank->id,$data->currency),2,'.','');

        $fee        = $this->getFee($data->currency,$amount);
        $deduct     = 0;
        $wise_cost  = 0;
        $tc_cost    = 0;
        if ($data->transfer_type=="circuit"){
            $wise_cost  = number_format($fee["wise_circuit"],2,'.','');
            $tc_cost    = number_format($fee['cost_circuit'],2,'.','');
        }elseif($data->transfer_type="outside"){
            $wise_cost  = number_format($fee["wise_outside"],2,'.','');
            $tc_cost    = number_format($fee['cost_outside'],2,'.','');
        }
        
        $deduct     = number_format($amount + $wise_cost+$tc_cost,2,'.','');
        if ($balance < $deduct) {
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "20",
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

        $jsonrecipient=json_encode($data_recipient);
        $resultrecipient=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->recipient,$jsonrecipient,$bank->wisekey);

        if (!empty($resultrecipient->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Something wrong, please try again later!"
    	        ];
    	    return $this->respond($response);
        }
        
        if (empty($resultrecipient->id) && ($resultquote->id)){
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
    	            "message"  => "Something wrong, please try again later!"
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
    	            "message"  => $resultfund->errorMessage
    	        ];
    	    return $this->respond($response);
        }
        
        
        $mdata = array(
            'user_id'           => $data->userid,
            'currency'          => $data->currency,
            'type'              => $data->transfer_type,
            'amount'            => $amount,
            'pbs_cost'          => $tc_cost,
            'wise_cost'         => $wise_cost
        );
        
        $result = $this->wallet->withdraw($mdata);
	    $response=[
	            "code"     => "200",
	            "error"    => NULL,
	            "message"  => "Transfer completed"
	        ];
	    return $this->respond($response);
    }
    
    //get tc bank fee
    private function getFee($currency,$amount) {
        $costdb = $this->cost->get_single($currency);
        $wisedb = $this->cost->get_wise($currency);
        
        $data=array(
                "cost_circuit"      => number_format(($amount*$costdb->walletbank_circuit_pct)+$costdb->walletbank_circuit_fxd,2,'.',''),
                "cost_outside"      => number_format(($amount* $costdb->walletbank_outside_pct)+$costdb->walletbank_outside_fxd,2,'.',''),
                "wise_circuit"      => number_format(($amount*$wisedb->transfer_circuit_pct)+$wisedb->transfer_circuit_fxd,2,'.',''),
                "wise_outside"      => number_format(($amount*$wisedb->transfer_outside_pct)+$wisedb->transfer_outside_fxd,2,'.',''),
                );
        return $data;
    }	    
}
