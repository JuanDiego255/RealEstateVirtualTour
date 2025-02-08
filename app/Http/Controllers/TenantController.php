<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $tenants = Tenant::all();
        return view('admin.tenant.index', compact('tenants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      

            $campos = [
                'tenant' => 'required'
            ];
 
            $mensaje = ["required" => 'El :attribute es requerido store'];
            $this->validate($request, $campos, $mensaje);

            $tenant = Tenant::create(['id' => $request->tenant]);
            $tenant->domains()->create([
                'domain' => $request->get('tenant'). '.' . 'localhost'
            ]);
            
           
            return redirect('/tenants')->with(['status' => 'Se ha guardado el inquilino con éxito', 'icon' => 'success']);
     
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tenant $tenant)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        DB::beginTransaction();
        try {
           
           Tenant::destroy($id);

            DB::commit();
            return redirect('/tenants')->with(['status' => 'Se ha eliminado el inquilino con éxito', 'icon' => 'success']);
        } catch (\Exception $th) {
            //throw $th;
            DB::rollBack();
        }
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function domain(Tenant $tenant)
    {
        DB::beginTransaction();
        try {            
        
            $tenant->domains()->create([
                'domain' => $tenant . '.' . 'velvetboutique.safeworsolutions.com'
            ]);
            
            DB::commit();
            return redirect('/tenants')->with(['status' => 'Se ha creado el dominio el inquilino con éxito', 'icon' => 'success']);
        } catch (\Exception $th) {
            DB::rollBack();
            return redirect('/tenants')->with(['status' => 'No se pudo guardar el inquilino', 'icon' => 'error']);
        }
    }
}
