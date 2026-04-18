<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RafflePrize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RafflePrizeController extends Controller
{
    private function companyId(): int
    {
        $user = Auth::user();
        if ($user->isSuperAdmin() && request('company_id')) {
            return (int) request('company_id');
        }
        return (int) $user->company_id;
    }

    public function index()
    {
        $prizes = RafflePrize::where('company_id', $this->companyId())
            ->orderBy('active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.raffle-prizes.index', compact('prizes'));
    }

    public function create()
    {
        return view('admin.raffle-prizes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:80',
            'emoji'    => 'nullable|string|max:10',
            'color'    => 'required|string|max:20',
            'quantity' => 'nullable|integer|min:1|max:9999',
            'weight'   => 'required|integer|min:1|max:100',
            'active'   => 'nullable|boolean',
        ]);

        $data['company_id'] = $this->companyId();
        $data['active']     = $request->boolean('active', true);

        RafflePrize::create($data);

        return redirect()->route('admin.raffle-prizes.index')
            ->with('success', 'Premio agregado a la ruleta.');
    }

    public function edit(RafflePrize $rafflePrize)
    {
        $this->guard($rafflePrize);
        return view('admin.raffle-prizes.edit', compact('rafflePrize'));
    }

    public function update(Request $request, RafflePrize $rafflePrize)
    {
        $this->guard($rafflePrize);

        $data = $request->validate([
            'name'     => 'required|string|max:80',
            'emoji'    => 'nullable|string|max:10',
            'color'    => 'required|string|max:20',
            'quantity' => 'nullable|integer|min:1|max:9999',
            'weight'   => 'required|integer|min:1|max:100',
            'active'   => 'nullable|boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $rafflePrize->update($data);

        return redirect()->route('admin.raffle-prizes.index')
            ->with('success', 'Premio actualizado.');
    }

    public function reset(RafflePrize $rafflePrize)
    {
        $this->guard($rafflePrize);
        $rafflePrize->update(['claimed' => 0]);

        return redirect()->route('admin.raffle-prizes.index')
            ->with('success', 'Contador de entregados reiniciado.');
    }

    public function destroy(RafflePrize $rafflePrize)
    {
        $this->guard($rafflePrize);
        $rafflePrize->delete();

        return redirect()->route('admin.raffle-prizes.index')
            ->with('success', 'Premio eliminado.');
    }

    private function guard(RafflePrize $prize): void
    {
        if ($prize->company_id !== (int) Auth::user()->company_id && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }
    }
}
