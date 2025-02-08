<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Scene;
use App\Hotspot;
use App\Properties;
use Datatables;
use Illuminate\Support\Facades\Storage;

class SceneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $scene = Scene::where('property_id', $id)->get();
        $hotspots = Hotspot::all();
        return view('admin.config', compact('hotspots', 'scene', 'id'));
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function frontendIndex()
    {
        $properties = Properties::all();
        return view('frontend.index', compact('properties'));
    }

    public function dataScene(Request $request)
    {
        if ($request->ajax()) {
            $propertyId = $request->input('property_id');
            $data = Scene::where('property_id', $propertyId)->select('*');
            return Datatables::of($data)
                ->addColumn('status', function ($row) {
                    $sendData = route('changeFScene', $row->id);
                    $csrf = csrf_token();
                    if ($row->status != 0)
                        return '<form method="post" id="status' . $row->id . '" action=' . $sendData . '>
                                    <input name="_token" type="hidden" value=' . $csrf . '>
                                    <input name="property_id" type="hidden" value=' . $row->property_id . '>
                                    <input name="_method" type="hidden" value="PUT">
                                    <input type="checkbox" id="' . $row->id . '" name="check" checked value="1"/>
                                </form>';
                    else
                        return '<form method="post" id="status' . $row->id . '" action=' . $sendData . '>
                                    <input name="_token" type="hidden" value=' . $csrf . '>
                                    <input name="property_id" type="hidden" value=' . $row->property_id . '>
                                    <input name="_method" type="hidden" value="PUT">            
                                    <input type="checkbox" id="' . $row->id . '" name="check" value="1"/>
                                </form>';
                })
                ->addColumn('action', function ($row) {
                    return '<a href="#" class="text-success" data-toggle="modal" 
                        data-target="#detailScene' . $row->id . '"><i class="fa fa-eye"></i></a>
                            <a href="#" class="text-info" data-toggle="modal" 
                        data-target="#editModal' . $row->id . '"><i class="fa fa-edit"></i></a>
                            <a href="#" class="text-danger" data-toggle="modal" 
                        data-target="#deleteModal' . $row->id . '"><i class="ti-trash"></i></a>';
                })
                ->escapeColumns([])
                ->make(true);
        }
    }

    public function dataHotspot(Request $request)
    {
        $propertyId = $request->input('property_id');
        $hotspots = DB::table('hotspots')->where('sc1.property_id',$propertyId)
            ->join('scenes as sc1', 'hotspots.sourceScene', '=', 'sc1.id')
            ->join('scenes as sc2', 'hotspots.targetScene', '=', 'sc2.id')
            ->select('sc1.title as sourceSceneName', 'sc2.title as targetSceneName', 'hotspots.*');

        return Datatables::of($hotspots)
            ->addColumn('action', function ($row) {
                return '<a href="#" class="text-success" data-toggle="modal" 
                    data-target="#detailHotspot' . $row->id . '"><i class="fa fa-eye"></i></a>
                        <a href="#" class="text-info" data-toggle="modal" 
                    data-target="#editHotspot' . $row->id . '"><i class="fa fa-edit"></i></a>
                        <a href="#" class="text-danger" data-toggle="modal" 
                    data-target="#deleteHotspot' . $row->id . '"><i class="ti-trash"></i></a>';
            })
            ->make(true);
    }

    public function pannellum($id)
    {
        $fscene = DB::table('scenes')
            ->join('properties', 'scenes.property_id', '=', 'properties.id')
            ->where('scenes.property_id', $id)
            ->where('scenes.status', '1')
            ->select(
                'scenes.*',
                'properties.name as property_name'
            )
            ->first();


        $scenes = DB::table('scenes')
            ->where('property_id', $id)
            ->join('properties', 'scenes.property_id', '=', 'properties.id')
            ->select(
                'scenes.*',
                'properties.name as property_name'
            )
            ->get();

        $hotspots = DB::table('hotspots')
            ->where('scenes.property_id', $id)
            ->join('scenes', 'scenes.id', '=', 'hotspots.sourceScene')
            ->select('hotspots.*')
            ->get();

        return view('welcome', compact('fscene', 'scenes', 'hotspots'));
    }

    public function allProperties()
    {
        $fscene = DB::table('scenes')->where('status', '1')->first();
        $scenes = DB::table('scenes')->get();
        $hotspots = DB::table('hotspots')
            ->join('scenes', 'scenes.id', '=', 'hotspots.sourceScene')
            ->select('hotspots.*')
            ->get();

        return view('welcome', compact('fscene', 'scenes', 'hotspots'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'type' => 'required',
            'hfov' => 'required|min:-360|max:360',
            'yaw' => 'required|min:-360|max:360',
            'pitch' => 'required|min:-360|max:360',
            'image' => 'required|image',
            'image_ref' => 'required|image'
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('uploads', 'public');
        }
        if ($request->hasFile('image_ref')) {
            $imageRef = $request->file('image_ref')->store('uploads', 'public');
        }
        $property_id = $request['property_id'];

        Scene::create([
            'title' => $request['title'],
            'property_id' => $property_id,
            'type' => $request['type'],
            'hfov' => $request['hfov'],
            'yaw' => $request['yaw'],
            'pitch' => $request['pitch'],
            'image' => $image,
            'image_ref' => $imageRef
        ]);

        return redirect()->route('config', $property_id)->with('success', 'Escena guardada con éxito!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $scene = Scene::find($id);
        return view('/scene', compact('scene', 'id'));
    }

    public function update(Request $request, $id)
    {
        $scene = Scene::find($id);

        $request->validate([
            'title' => 'required|max:255',
            'type' => 'required',
            'hfov' => 'required|min:-360|max:360',
            'yaw' => 'required|min:-360|max:360',
            'pitch' => 'required|min:-360|max:360',
            'image' => 'image'
        ]);
        $property_id = $request['property_id'];

        if ($request->hasFile('image')) {
            Storage::delete('public/' . $scene->image);
            $image = $request->file('image')->store('uploads', 'public');
            $image = $image;
        }
        if ($request->hasFile('image_ref')) {
            Storage::delete('public/' . $scene->image);
            $image_ref = $request->file('image_ref')->store('uploads', 'public');
            $imageRef = $image_ref;
        }

        Scene::where('id', $id)->update([
            'title' => $request['title'],
            'type' => $request['type'],
            'property_id' => $property_id,
            'hfov' => $request['hfov'],
            'yaw' => $request['yaw'],
            'pitch' => $request['pitch'],
            'image' => $image,
            'image_ref' => $imageRef
        ]);

        return redirect()->route('config', $property_id)->with('success', 'Escena editada con éxito');
    }

    public function status(Request $request, $id)
    {
        $property_id = $request['property_id'];
        $scene = Scene::find($id);
        $updated = Scene::where('id', $id)->update([
            'status' => $request['check']
        ]);

        return redirect()->route('config', $property_id)->with('success', 'La escena principal cambió con éxito');
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        $scene = Scene::findOrfail($id);
        if (
            Storage::delete('public/' . $scene->image)
        ) {
            Scene::destroy($id);
        }
        Scene::destroy($id);
        $property_id = $request['property_id'];
        return redirect()->route('config', $property_id)->with('success', '
        Escena eliminada exitosamente');
    }
}
