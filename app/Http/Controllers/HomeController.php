<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Scene;
use App\Hotspot;
use App\Properties;
use App\Sector;
use App\Category;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $scenes = Scene::all();
        $hotspots = Hotspot::all();
        $properties = Properties::all();
        $sectors = Sector::all();
        $categories = Category::all();
        return view('admin.index', compact('scenes', 'hotspots', 'properties', 'sectors', 'categories'));
    }
}
