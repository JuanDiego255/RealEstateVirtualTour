<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    /**
     * Display a listing of packages.
     */
    public function index()
    {
        $packages = Package::ordered()->withCount('subscriptions')->get();
        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        return view('admin.packages.create');
    }

    /**
     * Store a newly created package.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:CRC,USD',
            'billing_period' => 'required|in:monthly,quarterly,yearly',
            'max_users' => 'required|integer|min:1',
            'max_categories' => 'required|integer|min:1',
            'max_posts_per_category' => 'required|integer|min:1',
            'tour_price' => 'nullable|numeric|min:0',
            'included_tours' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only([
            'name', 'description', 'price', 'currency', 'billing_period',
            'max_users', 'max_categories', 'max_posts_per_category',
            'tour_price', 'included_tours', 'trial_days', 'sort_order'
        ]);

        $data['slug'] = Str::slug($request->name);
        $data['allows_tours'] = $request->has('allows_tours');
        $data['allows_commission'] = $request->has('allows_commission');
        $data['is_featured'] = $request->has('is_featured');
        $data['is_active'] = $request->has('is_active');

        // Handle features JSON
        $features = [];
        if ($request->has('feature_keys') && $request->has('feature_values')) {
            foreach ($request->feature_keys as $index => $key) {
                if (!empty($key)) {
                    $features[$key] = $request->feature_values[$index] ?? true;
                }
            }
        }
        $data['features'] = $features;

        // Make slug unique
        $counter = 1;
        $originalSlug = $data['slug'];
        while (Package::where('slug', $data['slug'])->exists()) {
            $data['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        Package::create($data);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paquete creado correctamente.');
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    /**
     * Update the specified package.
     */
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|in:CRC,USD',
            'billing_period' => 'required|in:monthly,quarterly,yearly',
            'max_users' => 'required|integer|min:1',
            'max_categories' => 'required|integer|min:1',
            'max_posts_per_category' => 'required|integer|min:1',
            'tour_price' => 'nullable|numeric|min:0',
            'included_tours' => 'nullable|integer|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $data = $request->only([
            'name', 'description', 'price', 'currency', 'billing_period',
            'max_users', 'max_categories', 'max_posts_per_category',
            'tour_price', 'included_tours', 'trial_days', 'sort_order'
        ]);

        $data['allows_tours'] = $request->has('allows_tours');
        $data['allows_commission'] = $request->has('allows_commission');
        $data['is_featured'] = $request->has('is_featured');
        $data['is_active'] = $request->has('is_active');

        // Handle slug update
        if ($request->name !== $package->name) {
            $newSlug = Str::slug($request->name);
            $counter = 1;
            $originalSlug = $newSlug;
            while (Package::where('slug', $newSlug)->where('id', '!=', $package->id)->exists()) {
                $newSlug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $data['slug'] = $newSlug;
        }

        // Handle features JSON
        $features = [];
        if ($request->has('feature_keys') && $request->has('feature_values')) {
            foreach ($request->feature_keys as $index => $key) {
                if (!empty($key)) {
                    $features[$key] = $request->feature_values[$index] ?? true;
                }
            }
        }
        $data['features'] = $features;

        $package->update($data);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paquete actualizado correctamente.');
    }

    /**
     * Remove the specified package.
     */
    public function destroy(Package $package)
    {
        // Check if package has active subscriptions
        if ($package->subscriptions()->whereIn('status', ['active', 'trial'])->count() > 0) {
            return redirect()->route('admin.packages.index')
                ->with('error', 'No se puede eliminar el paquete porque tiene suscripciones activas.');
        }

        $package->delete();

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paquete eliminado correctamente.');
    }

    /**
     * Toggle package active status.
     */
    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Estado del paquete actualizado.');
    }

    /**
     * Toggle package featured status.
     */
    public function toggleFeatured(Package $package)
    {
        $package->update(['is_featured' => !$package->is_featured]);

        return redirect()->route('admin.packages.index')
            ->with('success', 'Paquete ' . ($package->is_featured ? 'destacado' : 'sin destacar') . '.');
    }
}
