<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, $orderId) { /* create payment / checkout */ }
    public function show($orderId) { /* payment status */ }
}
