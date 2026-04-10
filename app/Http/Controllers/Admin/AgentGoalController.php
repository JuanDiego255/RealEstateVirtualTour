<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentGoal;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentGoalController extends Controller
{
    /**
     * Listado de metas por agente para el mes seleccionado.
     */
    public function index(Request $request)
    {
        $user      = Auth::user();
        $companyId = $user->isSuperAdmin()
            ? $request->get('company_id', $user->company_id)
            : $user->company_id;

        $month = $request->get('month', now()->format('Y-m'));
        $from  = Carbon::parse($month . '-01')->startOfMonth();

        $agents = User::where('company_id', $companyId)
            ->where('role', User::ROLE_AGENT)
            ->orderBy('name')
            ->get();

        // Preload goals for this month
        $goals = AgentGoal::where('company_id', $companyId)
            ->where('month', $from->format('Y-m-d'))
            ->get()
            ->keyBy('user_id');

        return view('admin.metrics.goals.index', compact('agents', 'goals', 'month', 'from', 'companyId'));
    }

    /**
     * Guardar/actualizar meta de un agente para el mes.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'month'            => 'required|date_format:Y-m',
            'leads_goal'       => 'required|integer|min:0|max:9999',
            'quotes_goal'      => 'required|integer|min:0|max:9999',
            'conversions_goal' => 'required|integer|min:0|max:9999',
        ]);

        $me    = Auth::user();
        $agent = User::findOrFail($request->user_id);

        // Verificar que el agente pertenezca a la misma empresa
        if (!$me->isSuperAdmin() && $agent->company_id !== $me->company_id) {
            abort(403);
        }

        $month = Carbon::parse($request->month . '-01');

        AgentGoal::updateOrCreate(
            ['user_id' => $request->user_id, 'month' => $month->format('Y-m-d')],
            [
                'company_id'       => $agent->company_id,
                'leads_goal'       => $request->leads_goal,
                'quotes_goal'      => $request->quotes_goal,
                'conversions_goal' => $request->conversions_goal,
                'set_by'           => $me->id,
            ]
        );

        return back()->with('success', 'Meta actualizada correctamente.');
    }

    /**
     * Guardar metas de varios agentes en una sola operación.
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'month'                         => 'required|date_format:Y-m',
            'goals'                         => 'required|array',
            'goals.*.leads_goal'            => 'required|integer|min:0|max:9999',
            'goals.*.quotes_goal'           => 'required|integer|min:0|max:9999',
            'goals.*.conversions_goal'      => 'required|integer|min:0|max:9999',
        ]);

        $me    = Auth::user();
        $month = Carbon::parse($request->month . '-01');

        foreach ($request->goals as $userId => $values) {
            $agent = User::find($userId);
            if (!$agent) continue;
            if (!$me->isSuperAdmin() && $agent->company_id !== $me->company_id) continue;

            AgentGoal::updateOrCreate(
                ['user_id' => $userId, 'month' => $month->format('Y-m-d')],
                [
                    'company_id'       => $agent->company_id,
                    'leads_goal'       => $values['leads_goal'] ?? 0,
                    'quotes_goal'      => $values['quotes_goal'] ?? 0,
                    'conversions_goal' => $values['conversions_goal'] ?? 0,
                    'set_by'           => $me->id,
                ]
            );
        }

        return back()->with('success', 'Metas guardadas correctamente.');
    }
}
