<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravolt\Avatar\Facade as Avatar;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Jika ada image upload, simpan
        if ($request->image) {
            $imagePath = $request->file('image')->store('customer_images', 'public');
        } else {
            // Kalau tidak ada image, generate avatar dari nama
            $slugName = Str::slug($request->name);
            $avatarFileName = 'customer_images/' . $slugName . '_' . time() . '.png';
            $avatarPath = storage_path('app/public/' . $avatarFileName);

            Avatar::create($request->name)
                ->setBackground('#f0f0f0')   // warna background
                ->setForeground('#333333')   // warna huruf
                ->save($avatarPath);

            $imagePath = $avatarFileName;
        }

        // Create customer
        $customer = Customer::create([
            'image' => $imagePath,
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        if ($customer) {
            return response()->json([
                'success' => true,
                'message' => 'Customer registered successfully',
                'data' => $customer
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Registration failed'
        ], 500);
    }
}
