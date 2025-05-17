<?php

namespace App\Http\Controllers;

use App\Cart;

use Exception;
use Midtrans\Snap;
use App\Transaction;
use Midtrans\Config;

use App\TransactionDetail;

use Midtrans\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        //save user data
        
        
        $user = Auth::user();
        $user->update($request->except('total_price'));

        //proces checkout
        $code = 'STORE-' . mt_rand(00000,99999);
        $carts = Cart::with(['product','user'])
                    ->where('users_id', Auth::user()->id)
                    ->get();

        //proces transaksi
        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'inscurance_price' => 0,
            'shipping_price' => 0,
            'total_price' =>(int) $request->total_price,
            'transaction_status' => 'PENDING',
            'code' => $code
        ]);
        foreach ($carts as $cart) {
            $trx = 'TRX-' . mt_rand(00000,99999);

            TransactionDetail::create([
                'transactions_id' => $transaction->id,
                'products_id' => $cart->product->id,
                'price' => $cart->product->price,
                'shipping_status' => 'PENDING',
                'resi' => '',
                'code' => $trx
            ]);
        }

        //delete cart data
        Cart::where('users_id',Auth::user()->id)->delete();
        
        // Konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Buat array untuk dikirim ke midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' =>  $code,
                'gross_amount' => (int) $request->total_price,
            ],
            'customer_details' => [
                'first_name'    => Auth::user()->name,
                'email'         => Auth::user()->email
            ],
            'enabled_payments' => ['gopay','bank_transfer','bri_va'],
            
            'vtweb' => []
        ];

        try {
            // Ambil halaman payment midtrans
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;

            // Redirect ke halaman midtrans
            return redirect($paymentUrl);
        }
        catch (Exception $e) {
            echo $e->getMessage();
        }
    }
    public function callback(Request $request)
    {

    }
}
