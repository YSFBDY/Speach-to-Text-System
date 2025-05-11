<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;

use App\Models\Plan;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;


use Illuminate\Http\Request;
use App\Http\Requests\Subscription\PaymentRequest;

use App\Traits\TimeAgo;

use Illuminate\Support\Facades\Log;

class SubscribtionController extends Controller
{
    use TimeAgo;
    protected $api_key;
    protected $base_url;
    protected $integration_id;
    protected $frame_id;

    public function __construct()
    {
        $this->base_url = env("PAYMOB_BASE_URL"); 
        $this->api_key = env("PAYMOB_API_KEY"); 
        $this->integration_id = env("PAYMOB_INTEGRATION_ID");
        $this->frame_id = env("PAYMOB_FRAME_ID");

    }

    public function paymentprocess(PaymentRequest $request)
    {
        if(!$request->user_id || ! User::where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'user not found'], 404);
            
        }

        if(Subscription::where('user_id', $request->user_id)->where('subscription_status', 'active')->exists()) {
            return response()->json(['message' => 'User already has an active subscription'], 400);
        }
    
        $plan = Plan::find($request->plan_id);

        if (!$plan || !$request->plan_id) {
            return response()->json(['message' => 'Plan not found'], 404);
        }

        $amount_cents = $plan->plan_price_cents;
        $amount = $plan->getRawOriginal('plan_price');
    
        Log::info('Processing payment for user_id: ' . $request->user_id);
        $response1 = Http::withoutVerifying()->post($this->base_url . '/api/auth/tokens', [
            'api_key' => $this->api_key,
        ]);
    
        if ($response1->failed()) {
            Log::error('Failed to create token', ['response' => $response1->json()]);
            return response()->json(['error' => 'Failed to create token'], 500);
        }
    
        $token = $response1->json()['token'];
        Log::info('Auth token generated: ' . $token);
    
        $items = [
            ["name" => "Subscription", "amount_cents" => $amount_cents, "description" => "Subscription Plan", "quantity" => 1]
        ];
    
        $merchant_order_id = uniqid();
        $orderData = [
            "auth_token" => $token,
            "delivery_needed" => false,
            "amount_cents" => $amount_cents,
            "currency" => "EGP",
            "items" => $items,
            "merchant_order_id" => $merchant_order_id,
        ];
    
        $response2 = Http::withoutVerifying()->post($this->base_url . '/api/ecommerce/orders', $orderData);
    
        if ($response2->failed()) {
            Log::error('Failed to create order', ['response' => $response2->json()]);
            return response()->json(['error' => 'Failed to create order'], 500);
        }
    
        $order_id = $response2->json()['id'];
        Log::info('Order created with order_id: ' . $order_id);
    
        $payment = Payment::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone_number,
            'payment_amount' => $amount,
            'payment_amount_cent' => $amount_cents,
            'payment_currency' => 'EGP',
            'payment_status' => 'pending',
            'order_id' => $order_id,
            'merchant_order_id' => $merchant_order_id,
            'user_id' => $request->user_id,
            'plan_id' => $request->plan_id
        ]);
        Log::info('Payment record created with order_id: ' . $payment->order_id);
    
        $billingData = [
            "first_name" => $request->first_name,
            "last_name" => $request->last_name,
            "email" => $request->email,
            "phone_number" => $request->phone_number,
            "apartment" => "NA",
            "floor" => "NA",
            "street" => "NA",
            "building" => "NA",
            "city" => "NA",
            "country" => "EG",
            "state" => "NA"
        ];
    
        $paymentKeyData = [
            "auth_token" => $token,
            "amount_cents" => $amount_cents,
            "currency" => "EGP",
            "order_id" => $order_id,
            "billing_data" => $billingData,
            "integration_id" => $this->integration_id,
            "expiration" => 3600
        ];
    
        $response3 = Http::withoutVerifying()->post($this->base_url . '/api/acceptance/payment_keys', $paymentKeyData);
    
        if ($response3->failed()) {
            Log::error('Paymob payment key creation failed', [
                'response' => $response3->json(),
                'request' => $paymentKeyData
            ]);
            return response()->json([
                'error' => 'Failed to create payment key',
                'details' => $response3->json()
            ], 500);
        }
    
        $payment_key = $response3->json()['token'];
        $payment_url = "https://accept.paymob.com/api/acceptance/iframes/{$this->frame_id}?payment_token={$payment_key}";
    
        return response()->json([
            'payment_url' => $payment_url,
            'payment_id' => $payment->payment_id 
        ]);
    }







    public function getPaymentStatus($payment_id){

        if (!$payment_id) {
            return response()->json(['error' => 'Payment Not Found'], 404);
        }

        $payment = Payment::where('payment_id', $payment_id)->where('payment_status', 'pending')->first();
        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        Log::info('Fetching payment status for payment_id: ' . $payment_id . ', order_id: ' . $payment->order_id);

        // Step 1: Get auth token
        $response1 = Http::withoutVerifying()->post($this->base_url . '/api/auth/tokens', [
            'api_key' => $this->api_key,
        ]);

        if ($response1->failed()) {
            Log::error('Failed to create token', ['response' => $response1->json()]);
            return response()->json(['error' => 'Failed to create token'], 500);
        }

        $token = $response1->json()['token'];
        Log::info('Auth token generated: ' . $token);

        // Step 2: Fetch the first page of transactions (no pagination)
        $transactionResponse = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->withoutVerifying()->get($this->base_url . '/api/acceptance/transactions', [
            'per_page' => 50, // Fetch up to 50 transactions (max per page)
        ]);

        if ($transactionResponse->failed()) {
            Log::error('Failed to fetch transactions list from Paymob', [
                'response' => $transactionResponse->json(),
                'status' => $transactionResponse->status(),
            ]);
            return response()->json(['error' => 'Failed to fetch transactions list'], 500);
        }

        $responseData = $transactionResponse->json();
        $transactions = $responseData['results'] ?? [];
        Log::info('Transactions fetched from Paymob', ['transactions' => $transactions]);

        // Step 3: Find the transaction matching the order_id
        $transaction = null;
        $transaction_id = null;

        foreach ($transactions as $trans) {
            if (isset($trans['order']['id']) && $trans['order']['id'] == $payment->order_id) {
                $transaction = $trans;
                $transaction_id = $trans['id'];
                break;
            }
        }

        if (!$transaction) {
            Log::info('No matching transaction found for order_id: ' . $payment->order_id);
            return response()->json([
                'status' => 'pending',
                'message' => 'Payment status still pending - no matching transaction found',
            ], 204);
        }

        Log::info('Transaction found for order_id: ' . $payment->order_id, ['transaction' => $transaction]);

        if ($transaction['pending'] === true) {
            Log::info('Transaction is still pending for transaction_id: ' . $transaction_id);
            return response()->json([
                'status' => 'pending',
                'message' => 'Payment status still pending (transaction pending)',
            ],202);
        }

        if ($transaction['success'] === true) {
            $payment->payment_status = 'approved';
            $payment->save();
            Log::info('Payment saved', ['payment' => $payment->toArray()]);

            Log::info('Payment approved, creating subscription');
            $plan = Plan::find($payment->plan_id);
            if (!$plan) {
                Log::error('Plan not found: ' . $payment->plan_id);
                return response()->json(['error' => 'Plan not found'], 404);
            }

            Subscription::create([
                'subscription_status' => 'active',
                'remain_transcription_limit' => $plan->plan_transcription_limit,
                'remain_translation_limit' => $plan->plan_translation_limit,
                'user_id' => $payment->user_id,
                'plan_id' => $payment->plan_id,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Payment approved and subscription created',
            ], 200);
        } else {
            $payment->payment_status = 'declined';
            $payment->save();
            Log::info('Payment saved', ['payment' => $payment->toArray()]);

        return response()->json([
            'status' => 'declined',
            'message' => 'Payment declined by Paymob, try again',
            'status_code' => 400
             ]);
            }
    }







    

    public function showpayments($user_id) {

        if (! User::where('user_id', $user_id)->first() || !$user_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $payments = Payment::where('user_id', $user_id)
        ->orderBy('created_at', 'desc')
        ->select('payment_id', 'first_name', 'last_name', 'email', 'phone', 'payment_amount', 'payment_currency', 'payment_status', 'created_at')
        ->get();

        if (!$payments) {
            return response()->json(status: 204);
        }

        $formatted = $payments->map(function ($payment) {
            return [
                'payment_id' => $payment->payment_id,
                'first_name' => $payment->first_name,
                'last_name' => $payment->last_name,
                'email' => $payment->email,
                'phone' => $payment->phone,
                'payment_amount' => $payment->payment_amount,
                'payment_currency' => $payment->payment_currency,
                'payment_status' => $payment->payment_status,
                'time_ago' => $this->getTimeAgo($payment->created_at),
            ];
        });

        return response()->json(['payments' => $formatted], 200);
    }
    


    public function showsubscriptions($user_id) {

        if (! User::where('user_id', $user_id)->first() || !$user_id) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $subscription = Subscription::where('user_id', $user_id)
        ->where('subscription_status', 'active')
        ->select('subscription_id', 'subscription_status', 'remain_transcription_limit', 'remain_translation_limit', 'start_date', 'end_date', 'plan_id')
        ->first();

        if (!$subscription) {
            return response()->json(status: 204);
        }

        $plan = Plan::where('plan_id', $subscription->plan_id)->first();

        return response()->json([
            'subscription_id' => $subscription->subscription_id,
            'subscription_status' => $subscription->subscription_status,

            'plan_transcription_limit' => $plan->plan_transcription_limit,
            'remain_transcription_limit' => $subscription->remain_transcription_limit,

            'plan_translation_limit' => $plan->plan_translation_limit,
            'remain_translation_limit' => $subscription->remain_translation_limit,

            'start_date' => $subscription->start_date,
            'end_date' => $subscription->end_date,

            'plan_name' => $plan->plan_name,
            'plan_price' => $plan->plan_price,
        ], 200);

    }


}