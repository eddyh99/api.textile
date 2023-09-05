<?php
namespace App\Controllers\V1\Member;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul Member Swap
    Desc        : Modul ini di gunakan untuk aktifitas swap di sisi Member/User
                  Swap di sisi member akan dikenakan TC Bank Fee saja
    Sub fungsi  : 
        - getSummary    : berfungsi mendapatkan informasi swap dari wise
        - getFee        : berfungsi mendapatkan TC fee
        - swapCurrency  : berfungsi mendapatkan informasi swap dari wise
        
------------------------------------------------------------*/


class Swap extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        /*----------------------------------------------------------
            wallet              => untuk cek balance member
            swap                => untuk save ke table swap
            currency            => untuk ambil semua currency yg sudah di aktifkan member
            trackless\cost      => untuk ambil cost swap TC Bank
        ------------------------------------------------------------*/
        
        $this->wallet   = model('App\Models\V1\Mdl_wallet');
        $this->cost     = model('App\Models\V1\Trackless\Mdl_cost');
        $this->swap     = model('App\Models\V1\Mdl_swap');
        $this->currency = model('App\Models\V1\Mdl_currency');
	}
	
	//cek pre swap member
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
                    'source' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Source Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'target' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Target Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
                    'amount' => [
					    'rules'  => 'required|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Amount to swap is required',
					        'greater_than'  => 'Amount cannot be negative or zero'
					    ]
					]					
				]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'    => FILTER_SANITIZE_STRING, 
            'amount'    => FILTER_VALIDATE_FLOAT, 
            'source'    => FILTER_SANITIZE_STRING, 
            'target'    => FILTER_SANITIZE_STRING, 
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
    	            "message"  => "Minimum swap is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format( $this->wallet->get_balance($data->userid,$data->source),2,'.','');
        $fee        = $this->getFee($data->source);

        $komisi=number_format(($fee["cost_pct"]*$amount)-$fee["cost_fxd"],2,'.','');
        
        if ($komisi<0.01){
            $amount_swap=number_format($amount-0.01,2,'.','');
        }else{
            $amount_swap = number_format($amount - $komisi,2,'.','');
        }


        if ($balance < $amount) {
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Insufficient Fund"
    	        ];
    	    return $this->respond($response);
        }
        
        $fee=0;

        $dataquote=array(
        	"sourceCurrency"    => $data->source,
        	"targetCurrency"    => $data->target,
        	"sourceAmount"      => number_format($amount_swap,2,'.',''),
        	"targetAmount"      => null,
            "profile"           => $bank->wiseprofile,
            "payOut"            => "BALANCE"
        );
        $jsonquote=json_encode($dataquote);
        $resultquote=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->quote,$jsonquote,$bank->wisekey);

        if (!empty($resultquote->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Something wrong, please contact the administrator!"
    	        ];
    	    return $this->respond($response);
        }

        $amountget=0;
        foreach ($resultquote->paymentOptions as $dt){
            if ($dt->payIn=="BALANCE"){
                $amountget=$dt->targetAmount;
                break;
            }
        }
        
        $mdata = array(
            'userid'    => $data->userid,
            'amount'    => $amount,
            'receive'   => number_format($amountget,2,'.',''),
            'currency'  => $this->currency->get_single($data->source,$data->userid)->symbol,
            'target'    => $this->currency->get_single($data->target,$data->userid)->symbol,
            'fee'       => $fee,
            'quoteid'   => $resultquote->id
            );
        
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $mdata
	        ];
	   return $this->respond($response);
	}
	
	//read fee member
    private function getFee($currency) {
        $costdb = $this->cost->get_single($currency);
        return array("fee" => 0, "cost_pct" => IS_NULL($costdb->swap) ? 0 : $costdb->swap, "cost_fxd" => IS_NULL($costdb->swap_fxd) ? 0 : $costdb->swap_fxd);
    }	
    
    //proses swap wise
    public function swapCurrency(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
        $validation = $this->validation;
        $validation->setRules([
					'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'User ID is required',
					    ]
					],
                    'source' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Source Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
					'target' => [
					    'rules'  => 'required|max_length[3]|min_length[3]',
					    'errors' =>  [
					        'required'      => 'Target Currency is required',
					        'min_length'    => 'Invalid Currency',
					        'max_length'    => 'Invalid Currency'
					    ]
					],
                    'amount' => [
					    'rules'  => 'required|greater_than[0]',
					    'errors' =>  [
					        'required'      => 'Amount to swap is required',
					        'greater_than'  => 'Amount cannot be negative or zero'
					    ]
					],
					'quoteid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Quoteid is required',
					    ]
					]
				]);
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'userid'    => FILTER_SANITIZE_STRING, 
            'amount'    => FILTER_VALIDATE_FLOAT, 
            'source'    => FILTER_SANITIZE_STRING, 
            'target'    => FILTER_SANITIZE_STRING,
            'quoteid'   => FILTER_SANITIZE_STRING,
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
    	            "message"  => "Minimum swap is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance    = number_format( $this->wallet->get_balance($data->userid,$data->source),2,'.','');
        $fee        = $this->getFee($data->source);

        $komisi=number_format(($fee["cost_pct"]*$amount)-$fee["cost_fxd"],2,'.','');
        if ($komisi<0.01){
            $amount_swap=number_format($amount-0.01,2,'.','');
            $komisi=0.01;
        }else{
            $amount_swap = number_format($amount - $komisi,2,'.','');
        }
        
        if ($balance < $amount) {
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Insufficient Fund"
    	        ];
    	    return $this->respond($response);
        }

        //read amount swap quote
        $ch     = curl_init(urlapi($data->quoteid, NULL, $bank->wiseprofile)->readquote);
        $headers    = array(
            'Authorization: Bearer '.$bank->wisekey,
            'Content-Type: application/json'
        );
        
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $resultquote = json_decode(curl_exec($ch));
        curl_close($ch);

        
        foreach ($resultquote->paymentOptions as $dt){
            if ($dt->payIn=="BALANCE"){
                $amountget=$dt->targetAmount;
                break;
            }    
        }


        //execute swap process
        $dataquote=array(
            "quoteId"   => $data->quoteid
        );
        $jsonquote=json_encode($dataquote);
        
        $ch     = curl_init(urlapi(NULL,NULL, $bank->wiseprofile)->balancemove);
        $headers    = array(
            'Content-Type: application/json',
            'X-idempotence-uuid: '.$data->quoteid,
            'Authorization: Bearer '.$bank->wisekey,
        );
        
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonquote); 
        $resultswap = json_decode(curl_exec($ch));
        curl_close($ch);

        if (!empty($resultswap->errors)){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Something wrong, please contact the administrator!"
    	        ];
    	    return $this->respond($response);
        }

        if ($resultswap->state!="COMPLETED"){
    	    $response=[
    	            "code"     => "5055",
    	            "error"    => "21",
    	            "message"  => "Failed to convert, please contact the administrator!"
    	        ];
    	    return $this->respond($response);
        }

        $mdata = array(
            'id_member'     => $data->userid,
            'amount'        => $amount,
            'currency'      => $data->source,
            'receive'       => number_format($amountget,2,'.',''),
            'target_cur'    => $data->target,
            'fee'           => 0,
            'pbs_cost'      => $komisi,
            );

        $result = $this->swap->add($mdata);
        if (@$result->code==5055){
    	   return $this->respond($result);
        }
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $mdata
	        ];
	   return $this->respond($response);
        

    }
}
