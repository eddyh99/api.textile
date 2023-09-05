<?php
namespace App\Controllers\V1\Trackless;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Operations extends BaseController
{
    use ResponseTrait;
    

    public function __construct()
    {   
        $this->fee      = model('App\Models\V1\Admin\Mdl_fee');
        $this->member   = model('App\Models\V1\Mdl_member');
        $this->cost     = model('App\Models\V1\Trackless\Mdl_cost');
        $this->wallet   = model('App\Models\V1\Trackless\Mdl_wallet');
    }
	
	public function topup(){
	    $data   = $this->request->getJSON();
        foreach ($data as $dt){
            if (!empty($dt->ucode)){
                $user   = $this->member->getby_ucode($dt->ucode);
                if (@$user->code==5051){
                    continue;
                }
                $fee    = $this->getFee($dt->currency,$dt->amount,$user->bank_id);

                $mdata=array(
                        "id_member" => $user->id,
                        "amount"    => $dt->amount,
                        "currency"  => $dt->currency,
                        "pbs_cost"  => number_format($fee["cost_topup"],2),
                        "fee"       => number_format($fee["fee_topup"],2),
                        "referral_fee"  => number_format($fee["referral_topup"],2),
                        "admin_id"  => $dt->admin_id,
                        "wise_id"   => $dt->wise_id,
                        "wise_cost" => number_format($fee["wise_topup"],2),
                        "is_proses" => 'yes'
                    );
            }else{
                $user   = $this->member->getby_causal($dt->causal);
                $fee    = $this->getFee($dt->currency,$dt->amount,$user->bank_id);
                $mdata=array(
                        "amount"    => $dt->amount,
                        "pbs_cost"  => number_format($fee["cost_topup"],2),
                        "fee"       => number_format($fee["fee_topup"],2),
                        "referral_fee"  => number_format($fee["referral_topup"],2),
                        "wise_id"   => $dt->wise_id,
                        "wise_cost" => number_format($fee["wise_topup"],2),
                        "is_proses" => 'yes'
                    );                
            }
            $result=$this->wallet->topup($mdata,$dt->causal);
        }

	    $response=[
	            "code"     => "200",
	            "error"    => null,
	            "message"  => "Data successfully imported"
	        ];
	    return $this->respond($response);
	}
	
    private function getFee($currency,$amount,$bank_id) {
        $feedb  = $this->fee->get_single($bank_id,$currency);
        $costdb = $this->cost->get_single($currency);
        $wisedb = $this->cost->get_wise($currency);
        
        $fee_topup       = 0;
        $cost_topup      = 0;
        $referral_topup  = 0;
        $wise_topup      = 0;
        
        $fee_topup       = number_format(($amount*$feedb->topup_circuit_pct),2)+$feedb->topup_circuit_fxd;
        $cost_topup      = number_format(($amount*$costdb->topup_circuit_pct),2)+$costdb->topup_circuit_fxd;
        $wise_topup      = number_format(($amount*$wisedb->topup_circuit_pct),2)+$wisedb->topup_circuit_fxd;
        $referral_topup  = number_format(($amount*$feedb->referral_topup_pct),2)+$feedb->referral_topup_fxd;

        
        $data=array(
                "fee_topup"      => number_format($fee_topup,2),
                "referral_topup" => number_format($referral_topup,2),
                "cost_topup"     => number_format($cost_topup,2),
                "wise_topup"     => number_format($wise_topup,2)
                );
        return $data;
    }	
    
    public function gethistory_masterwallet(){
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
					'timezone' => [
					    'rules'  => 'required',
					    'errors' =>  [
					        'required'      => 'Timezone is required',
					    ]
					]					
				]);
				
        if (!$validation->withRequest($this->request)->run()){
            return $this->fail($validation->getErrors());
        }
				
	    $data   = $this->request->getJSON();
	    $filters = array(
            'currency'      => FILTER_SANITIZE_STRING, 
            'date_start'    => FILTER_SANITIZE_STRING, 
            'date_end'      => FILTER_SANITIZE_STRING, 
            'timezone'      => FILTER_SANITIZE_STRING, 
        );

	    $filtered = array();
        foreach($data as $key=>$value) {
             $filtered[$key] = filter_var($value, $filters[$key]);
        }
        
        $data=(object) $filtered;

	    $result     = $this->wallet->get_historymasterwallet($data->currency,$data->date_start,$data->date_end, $data->timezone);
	    $response=[
                "code"      => "200",
                "error"     => NULL,
                "message"   => $result
            ];
        return $this->respond($response);
    }
}
