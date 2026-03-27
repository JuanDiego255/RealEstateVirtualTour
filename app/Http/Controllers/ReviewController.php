<?php

namespace App\Http\Controllers;

use App\Review;
use App\User;
use App\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar las calificaciones recibidas por el usuario autenticado
     */
    public function myReviews()
    {
        $user = Auth::user();

        $reviews = Review::with(['reviewer', 'sale.property'])
            ->where('reviewee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'average_rating' => $user->average_rating,
            'total_reviews' => $user->reviews_count,
            'rating_distribution' => Review::where('reviewee_id', $user->id)
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray()
        ];

        return view('reviews.my-reviews', compact('reviews', 'stats'));
    }

    /**
     * Mostrar las calificaciones de un usuario específico
     */
    public function index($userId)
    {
        $user = User::findOrFail($userId);

        $reviews = Review::with(['reviewer', 'sale.property'])
            ->where('reviewee_id', $user->id)
            ->withComment()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'average_rating' => $user->average_rating,
            'total_reviews' => $user->reviews_count,
            'rating_distribution' => Review::where('reviewee_id', $user->id)
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->pluck('count', 'rating')
                ->toArray()
        ];

        return view('reviews.user-reviews', compact('user', 'reviews', 'stats'));
    }

    /**
     * Mostrar el formulario para calificar a un usuario
     */
    public function create($userId, Request $request)
    {
        $reviewee = User::findOrFail($userId);
        $reviewer = Auth::user();

        // No puede calificarse a sí mismo
        if ($reviewer->id == $reviewee->id) {
            return redirect()->back()->with('error', 'No puedes calificarte a ti mismo');
        }

        $saleId = $request->get('sale_id');
        $sale = null;

        if ($saleId) {
            $sale = Sale::with('property')->findOrFail($saleId);

            // Verificar si ya calificó esta venta
            $existingReview = Review::where('reviewer_id', $reviewer->id)
                ->where('reviewee_id', $reviewee->id)
                ->where('sale_id', $sale->id)
                ->first();

            if ($existingReview) {
                return redirect()->route('reviews.my-reviews')
                    ->with('warning', 'Ya has calificado esta transacción');
            }
        }

        return view('reviews.create', compact('reviewee', 'sale'));
    }

    /**
     * Guardar una nueva calificación
     */
    public function store(Request $request, $userId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'sale_id' => 'nullable|exists:sales,id'
        ]);

        $reviewee = User::findOrFail($userId);
        $reviewer = Auth::user();

        // No puede calificarse a sí mismo
        if ($reviewer->id == $reviewee->id) {
            return redirect()->back()->with('error', 'No puedes calificarte a ti mismo');
        }

        // Verificar si ya existe una calificación para esta combinación
        $existingReview = Review::where('reviewer_id', $reviewer->id)
            ->where('reviewee_id', $reviewee->id)
            ->where('sale_id', $request->sale_id)
            ->first();

        if ($existingReview) {
            return redirect()->back()->with('warning', 'Ya has calificado este usuario para esta transacción');
        }

        $review = Review::create([
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $reviewee->id,
            'sale_id' => $request->sale_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return redirect()->route('reviews.my-reviews')
            ->with('success', 'Calificación enviada exitosamente');
    }

    /**
     * Actualizar una calificación existente
     */
    public function update(Request $request, $reviewId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::findOrFail($reviewId);

        // Solo el creador puede actualizar
        if ($review->reviewer_id !== Auth::id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return redirect()->back()->with('success', 'Calificación actualizada exitosamente');
    }

    /**
     * Eliminar una calificación
     */
    public function destroy($reviewId)
    {
        $review = Review::findOrFail($reviewId);

        // Solo el creador puede eliminar
        if ($review->reviewer_id !== Auth::id()) {
            return redirect()->back()->with('error', 'No autorizado');
        }

        $review->delete();

        return redirect()->back()->with('success', 'Calificación eliminada exitosamente');
    }
}
