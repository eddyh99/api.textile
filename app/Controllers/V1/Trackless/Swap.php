<?php
namespace App\Controllers\V1\Trackless;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/*----------------------------------------------------------
    Modul Name  : Modul TC Bank Swap
    Desc        : Modul ini di gunakan untuk aktifitas swap di sisi TC Bank
                  Swap di sisi TC Bank tidak dikenakan fee
    Sub fungsi  : 
        - swaptrackless_summary  : berfungsi mendapatkan informasi swap dari wise
        - swaptracklessProcess   : berfungsi memproses swap ke wise dan menyimpan data
        
------------------------------------------------------------*/


class Swap extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        /*----------------------------------------------------------
            trackless\wallet    => untuk cek balance TC bank
            admin\swap          => untuk save ke table master swap
            trackless\currency  => untuk ambil semua currency yg sudah di aktifkan di tcbank
            trackless\cost      => untuk ambil cost default tcbank
        ------------------------------------------------------------*/
        
        $this->wallet   = model('App\Models\V1\Trackless\Mdl_wallet');
        $this->cost     = model('App\Models\V1\Trackless\Mdl_cost');
        $this->swap     = model('App\Models\V1\Admin\Mdl_swap');
        $this->currency = model('App\Models\V1\Trackless\Mdl_currency');
	}
	
	//cek pre swap trackless bank
	public function swaptrackless_summary(){
	    $bank       = getBankId(apache_request_headers()["Authorization"]);
	    $validation = $this->validation;
        $validation->setRules([
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
            'amount'    => FILTER_VALIDATE_FLOAT, 
            'source'    => FILTER_SANITIZE_STRING, 
            'target'    => FILTER_SANITIZE_STRING, 
        );
	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;
        
        $amount         = number_format($data->amount,2,'.','');
        if ($amount<0.02){
    	    $response   = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum swap is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance        = number_format($this->wallet->balance_ByCurrency($data->source),2,'.','');
        $amount_swap    = $amount;

        if ($balance <$amount) {
    	    $response   = [
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
        $resultquote=apiwise(urlapi(NULL,NULL, $bank->wiseprofile)->quote,$jsonquote,
$bank->wisekey);

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
            'amount'    => $amount,
            'receive'   => number_format($amountget,2),
            'cost'      => $fee,
            'currency'  => $this->currency->get_single($data->source)->symbol,
            'target'    => $this->currency->get_single($data->target)->symbol,
            'quoteid'   => $resultquote->id
            );
        
	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => $mdata
	        ];
	   return $this->respond($response);
	}
	
    //proses swap ke wise
    public function swaptracklessProcess(){
        $bank       = getBankId(apache_request_headers()["Authorization"]);
        $validation = $this->validation;
        $validation->setRules([
                    'userid' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Userid is required',
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
        
        $amount         = number_format($data->amount,2,'.','');
        if ($amount<0.02){
    	    $response   = [
    	            "code"     => "5055",
    	            "error"    => "20",
    	            "message"  => "Minimum swap is 0.02"
    	        ];
    	    return $this->respond($response);
        }

        $balance        = number_format($this->wallet->balance_ByCurrency($data->source),2,'.','');
        $amount_swap    = $amount;

        if ($balance <$amount) {
    	    $response   = [
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
            'user_id'       => $data->userid,
            'amount'        => $amount,
            'currency'      => $data->source,
            'receive'       => number_format($amountget,2,'.',''),
            'target_cur'    => $data->target,
            'pbs_cost'      => 0,
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
