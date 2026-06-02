<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * POST /api/products/{product}/reviews
     */
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rating'   => 'required|integer|min:1|max:5',
            'body'     => 'nullable|string|max:1000',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        // Prevent duplicate review on same product+order
        $existing = Review::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->when($request->order_id, fn($q) => $q->where('order_id', $request->order_id))
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Vous avez déjà laissé un avis pour ce produit.'], 422);
        }

        $review = Review::create([
            'user_id'    => $request->user()->id,
            'product_id' => $product->id,
            'order_id'   => $request->order_id,
            'rating'     => $request->rating,
            'body'       => $request->body,
            'status'     => 'pending',
        ]);

        return response()->json(['message' => 'Avis soumis, en attente de modération.', 'review' => $review], 201);
    }

    /**
     * POST /api/reviews/{review}/flag
     */
    public function flag(Request $request, Review $review)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $review->update(['flagged' => true, 'flag_reason' => $request->reason]);

        return response()->json(['message' => 'Avis signalé. Notre équipe va l\'examiner.']);
    }

    /**
     * GET /api/products/{product}/reviews
     */
    public function index(Product $product)
    {
        $reviews = $product->reviews()->approved()
            ->with('user:id,name')
            ->latest()
            ->paginate(15);

        return response()->json([
            'reviews'    => $reviews,
            'avg_rating' => $product->averageRating(),
            'count'      => $product->reviews()->approved()->count(),
        ]);
    }
}
