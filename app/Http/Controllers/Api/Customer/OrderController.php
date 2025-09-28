<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request) { /* list orders */ }
    public function store(Request $request) { /* create order from cart */ }
    public function show($id) { /* show order + order_items */ }
}
