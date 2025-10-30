<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Testimonial;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::with('customer')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials
        ]);
    }

    public function store(Request $request)
    {
        $customer = auth('customer')->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'message' => 'required|string|max:1000',
        ]);

        $testimonial = Testimonial::create([
            'customer_id' => $customer->id,
            'name' => $validated['name'] ?? $customer->name,
            'rating' => $validated['rating'],
            'message' => $validated['message'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $testimonial,
        ]);
    }
}
