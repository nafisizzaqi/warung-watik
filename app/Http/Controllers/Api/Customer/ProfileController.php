<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // constructor untuk middleware auth
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * Show the authenticated customer's profile
     */
    public function show(Request $request)
    {
        $customer = $request->user(); // ambil customer yang login
        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }

    /**
     * Update the authenticated customer's profile
     */
    public function update(Request $request)
    {
        $customer = auth('customer')->user();

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:customers,email,' . $customer->id,
            'image' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update fields
        if ($request->filled('name')) {
            $customer->name = $request->name;
        }

        if ($request->filled('email')) {
            $customer->email = $request->email;
        }

        if ($request->hasFile('image')) {
            $customer->image = $request->file('image')->store('customer_images', 'public');
        }

        if ($request->filled('password')) {
            $customer->password = bcrypt($request->password);
        }

        $customer->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $customer,
        ]);
    }
}
