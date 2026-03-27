<?php

namespace App\Http\Controllers;

use App\Favorite;
use App\Properties;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar favoritos del usuario autenticado
     */
    public function index()
    {
        $favorites = auth()->user()->favoriteProperties()
            ->with(['category.sector', 'subcategory', 'user'])
            ->paginate(12);

        return view('favorites.index', compact('favorites'));
    }

    /**
     * Agregar propiedad a favoritos (AJAX)
     */
    public function store(Request $request, $propertyId)
    {
        $property = Properties::findOrFail($propertyId);

        $favorite = Favorite::firstOrCreate([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
        ]);

        if ($favorite->wasRecentlyCreated) {
            return response()->json([
                'success' => true,
                'message' => 'Propiedad agregada a favoritos',
                'action' => 'added'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Esta propiedad ya está en tus favoritos',
            'action' => 'exists'
        ]);
    }

    /**
     * Eliminar propiedad de favoritos (AJAX)
     */
    public function destroy($propertyId)
    {
        $deleted = Favorite::where('user_id', auth()->id())
            ->where('property_id', $propertyId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Propiedad eliminada de favoritos',
                'action' => 'removed'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Favorito no encontrado',
            'action' => 'not_found'
        ], 404);
    }
}
