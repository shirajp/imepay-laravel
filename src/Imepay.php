<?php

namespace Shiraj19\ImePay;

use Illuminate\Http\Request;
use Shiraj19\ImePay\model\ImeTransaction;

class Imepay
{
    private static $apiuser;
    private static $password;
    private static $module;
    private static $merchantcode;

    public function __construct() {
        self::$apiuser = config('ime-pay-config.apiuser');
        self::$password = config('ime-pay-config.password');
        self::$module = config('ime-pay-config.module');
        self::$merchantcode = config('ime-pay-config.merchant_code');
    }

    public static function index($amount, $refId){
        $token_responses = static::getToken($amount,$refId);
        $token_response = json_decode($token_responses);

        ImeTransaction::create([
            'MerchantCode' => self::$merchantcode,
            'TranAmount' => $token_response->Amount,
            'RefId' => $token_response->RefId,
            'TokenId' => $token_response->TokenId,
        ]); //store in table
        $merch_code = self::$merchantcode;
        return view('Imepay::blep', compact('token_response', 'merch_code'));
//        return static::authenticate_user($token_response,$merch_code = "MANDALAIT");

    }
    private static function getToken($amt,$ref_id){
        $data = ["MerchantCode" => self::$merchantcode, "Amount" => $amt, "RefId" => $ref_id];
        $header_array = [];
        $header_array[] = "Authorization: Basic ".base64_encode(self::$apiuser.":".self::$password);
        $header_array[] = "Module: ".base64_encode(self::$module);
        $header_array[] = "Content-Type: application/json";
        // Initializing a cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://202.166.194.123:7979/api/Web/GetToken');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

//    private static function authenticate_user($token_response, $merch_code){
//        $data = ["TokenId"=> $token_response->TokenId, "MerchantCode" => $merch_code,"RefId" => $token_response->RefId, "TranAmount" => $token_response->Amount, "Source" => "W"];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, 'http://202.166.194.123:7979/WebCheckout/Checkout');
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $result = curl_exec($ch);
//        curl_close($ch);
//        return $result;
//    }

    public function transaction_status(Request $request){
        if($request){
            if($request['ResponseCode'] == "0") {
                $row = ImeTransaction::latest()->first();
                $row->TransactionId = $request['TransactionId'];
                $row->Msisdn = $request['Msisdn'];

                $row->TranStatus = $request['ResponseCode'];
                $row->StatusDetail = "Transaction Initiated";
                $row->save();
                sleep(5);
                $response_json = static::confirm($request['RefId'], $row['MerchantCode'], $request['TransactionId'],$request['Msisdn']);
                $response = json_decode($response_json);
                if($response->ResponseCode != 0){
                    $response_json = static::confirm($request['RefId'], $row['MerchantCode'], $request['TransactionId'],$request['Msisdn']);
                    $response = json_decode($response_json);
                }

                $row->TranStatus = $response->ResponseCode;
                $row->StatusDetail = $response->ResponseDescription;
                $row->save();
                return view("Imepay::test")->with('message', $row->StatusDetail);

            } else {
                $row = ImeTransaction::where('RefId', $request['RefId'])->first();
                $row['TransactionId'] = $request['TransactionId'];
                $row['Msisdn'] = $request['Msisdn'];
                $row['TranStatus'] = $request['ResponseCode'];
                $row['StatusDetail'] = "Transaction Failed";
                $row->save();
                return view("Imepay::test")->with('message', $row->StatusDetail);
            }
        } else {
            $latest_transaction = ImeTransaction::latest('created_at')->first();
            $rechecked_json = static::reconfirm($latest_transaction->MerchantCode, $latest_transaction->TokenId, $latest_transaction->RefId);
            $rechecked = json_decode($rechecked_json);
            if($rechecked->ResponseCode == "0"){
                $latest_transaction->TransactionId = $rechecked->TransactionId;
                $latest_transaction->Msisdn = $rechecked->Msisdn;
                $latest_transaction->TranStatus = $rechecked->ResponseCode;
                $latest_transaction->StatusDetail = "Success";
            } else {
                $latest_transaction->TransactionId = $rechecked->TransactionId;
                $latest_transaction->Msisdn = $rechecked->Msisdn;
                $latest_transaction->TranStatus = $rechecked->ResponseCode;
                $latest_transaction->StatusDetail = "Not Found or Failed";
            }
            return view("Imepay::test")->with('message', "Transaction Failed. Try again or contact the respective firm!");
        }
    }

    private static function confirm($ref_id,$merch_code,$transaction_id, $msisdn){
        $data = ["TransactionId" => $transaction_id, "MerchantCode" => $merch_code, "Msisdn" => $msisdn, "RefId" => $ref_id];
        $header_array = [];
        $header_array[] = "Authorization: Basic " . base64_encode(self::$apiuser. ":" . self::$password);
        $header_array[] = "Module: " . base64_encode(self::$module);
        $header_array[] = "Content-Type: application/json";
        // Initializing a cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://202.166.194.123:7979/api/Web/Confirm');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }
    private static function reconfirm($MerchantCode, $TokenId,$RefId) {

        $data = ["MerchantCode" => $MerchantCode, "TokenId" => $TokenId, "RefId" => $RefId];
        $header_array = [];
        $header_array[] = "Authorization: Basic " . base64_encode(self::$apiuser. ":" . self::$password);
        $header_array[] = "Module: " . base64_encode(self::$module);
        $header_array[] = "Content-Type: application/json";
        // Initializing a cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://202.166.194.123:7979/api/Web/Recheck');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
