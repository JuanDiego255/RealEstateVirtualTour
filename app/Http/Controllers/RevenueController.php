<?php

namespace App\Http\Controllers;

use App\Jobs\SendCampaignSmsJob;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Yajra\DataTables\Facades\DataTables;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\Models\BusinessLocation;
use App\Models\ExpenseCategory;
use App\Models\User;
use App\Models\City;
use App\Models\Revenue;
use App\Models\TaxRate;
use Illuminate\Support\Facades\Notification;
use App\Models\Account;
use App\Models\Contact;
use App\Models\PaymentRevenue;
use App\Models\SmsCampaign;
use App\Models\SmsCampaignRecipient;
use App\Notifications\CustomerNotification;
use App\Utils\ContactUtil;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevenueController extends Controller
{
    protected $transactionUtil;

    public function __construct(TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ContactUtil $contactUtil)
    {
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->dummyPaymentLine = [
            'method' => 'cash',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
            'data_base' => date('d/m/Y'),
            'intervalo' => '',
            'vencimento' => date('d/m/Y'),
            'qtd_parcelas' => 1
        ];

        $this->contactUtil = $contactUtil;
    }
    public function index()
    {
        if (!auth()->user()->can('cxc.view')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $business_id = request()->session()->get('user.business_id');

            $lastPr = DB::table('payment_revenues as pr2')
                ->selectRaw('MAX(pr2.id) as last_id, pr2.revenue_id')
                ->groupBy('pr2.revenue_id');

            $revenues = Revenue::where('revenues.business_id', $business_id)
                ->leftJoin('contacts AS ct', 'revenues.contact_id', '=', 'ct.id')

                // Para sumar amortiza con TODAS las filas
                ->leftJoin('payment_revenues AS pr', 'revenues.id', '=', 'pr.revenue_id')

                // Para traer SOLO la última fila
                ->leftJoinSub($lastPr, 'lpr', function ($join) {
                    $join->on('lpr.revenue_id', '=', 'revenues.id');
                })
                ->leftJoin('payment_revenues AS pr_last', 'pr_last.id', '=', 'lpr.last_id')

                ->leftJoin('plan_ventas AS pv', 'revenues.plan_venta_id', '=', 'pv.id')
                ->leftJoin('products AS v', 'pv.vehiculo_venta_id', '=', 'v.id')
                ->select([
                    'revenues.id as rev_id',
                    'revenues.referencia',
                    'revenues.expense_category_id',
                    'revenues.location_id',
                    'revenues.status',
                    'revenues.check_sms',
                    'revenues.sucursal',
                    'revenues.valor_total',
                    'revenues.detalle',
                    'revenues.created_by',
                    'ct.contact_id',
                    'ct.id as cliente_id',
                    'v.name as vehiculo',
                    'v.model as model',
                    'ct.name',
                    DB::raw('COALESCE(SUM(pr.amortiza), 0) as amount_paid'),

                    // 👇 ahora es el monto_general de la última línea (no el mínimo)
                    DB::raw('COALESCE(pr_last.monto_general, -1) as min_general_amount'),
                ])
                ->groupBy(
                    'revenues.id',
                    'ct.contact_id',
                    'ct.name',
                    'pr_last.monto_general'
                )
                ->orderBy('rev_id', 'desc');

            $status = request()->get('status');
            if (request()->has('status') && $status != 4) {
                if ($status == 1) {
                    $revenues->where('revenues.status', 1);
                }
                if ($status == 2) {
                    $revenues->where('revenues.status', 0);
                }
                if ($status == 3) {
                    $revenues->where('revenues.status', 2);
                }
                if ($status == 5) {
                    $revenues->where('revenues.status', 3);
                }
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $revenues->whereDate('revenues.created_at', '>=', $start)
                    ->whereDate('revenues.created_at', '<=', $end);
            }

            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id) && $location_id != "TODAS") {
                    $revenues->where('revenues.sucursal', $location_id);
                }
            }

            /* $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $revenues->whereIn('revenues.location_id', $permitted_locations);
            } */

            return Datatables::of($revenues)

                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                    data-toggle="dropdown" aria-expanded="false"> Acciones<span class="caret"></span><span class="sr-only">Toggle Dropdown
                    </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';



                        $html .= '<li><a href="' . action('RevenueController@receive', [$row->cliente_id, $row->rev_id]) . '"><i class="fa fa-eye"></i> Detallar</a></li>';

                        $html .= '<li>
                    <a data-href="' . action('RevenueController@destroy', [$row->rev_id]) . '" class="delete_revenue"><i class="glyphicon glyphicon-trash"></i> Eliminar</a>
                    </li>
                    <li class="divider"></li>';

                        $html .= '</ul></div>';
                        return $html;
                    }
                )
                ->addColumn('mass_check', function ($row) {
                    return '<input type="checkbox" class="row-select" value="' . $row->rev_id . '"'
                        . ($row->check_sms == 1 ? ' checked' : '') . '>';
                })
                ->editColumn('amount_paid', function ($row) {
                    $due = $row->min_general_amount;
                    return '<span class="display_currency payment_due" data-currency_symbol="true" data-orig-value="' . $due . '">' . $due . '</span>';
                })
                ->editColumn(
                    'status',
                    function ($row) {
                        if ($row->status == 2) {
                            return '<span class="label bg-orange">Judicial</span>';
                        } else if ($row->status == 3) {
                            return '<span class="label bg-red">Pérdida</span>';
                        } else if ($row->min_general_amount <= 0 || $row->status == 1) {
                            return '<span class="label bg-blue">Cobrado</span>';
                        } else {
                            return '<span class="label bg-yellow">Pendiente</span>';
                        }
                    }
                )

                ->addColumn(
                    'contact',
                    function ($row) {

                        return $row->name;
                    }
                )
                ->editColumn(
                    'valor_total',
                    '<span class="display_currency final-total" data-currency_symbol="true" data-orig-value="{{$valor_total}}">{{$valor_total}}</span>'
                )
                ->removeColumn('rev_id')
                ->rawColumns(['action', 'mass_check', 'checkbox', 'valor_total', 'status', 'amount_paid'])
                ->make(true);
        }

        $business_id = request()->session()->get('user.business_id');

        $categories = ExpenseCategory::where('business_id', $business_id)
            ->pluck('name', 'id');

        $users = User::forDropdown($business_id, false, true, true);

        $business_locations = BusinessLocation::forDropdown($business_id, true);


        return view('revenues.index')
            ->with(compact('categories', 'business_locations', 'users'));
    }
    public function storeRow($id, Request $request)
    {
        if (!auth()->user()->can('cxc.create')) {
            return response()->json(['success' => false, 'msg' => 'No cuentas con permisos para realizar modificaciones, o insertar nuevas lineas']);
        }
        try {
            DB::beginTransaction();
            $record = PaymentRevenue::where('payment_revenues.revenue_id', $id)
                ->join('revenues', 'payment_revenues.revenue_id', '=', 'revenues.id')
                ->select(
                    'payment_revenues.id',
                    'payment_revenues.created_at',
                    'payment_revenues.monto_general',
                    'revenues.tasa',
                    'revenues.valor_total as monto_general_first',
                    'revenues.cuota'
                )
                ->orderByRaw('CAST(payment_revenues.monto_general AS DECIMAL(15,2)) ASC')
                ->first();
            $business_id = $request->session()->get('user.business_id');
            $monto_general = $record->monto_general;
            $interes = round($monto_general * ($record->tasa / 100), 2);
            $cxc_pay['revenue_id'] = $id;
            $cxc_pay['referencia'] = $this->transactionUtil->getInvoiceNumber($business_id, 'final', "");
            $cxc_pay['monto_general'] = round($monto_general - ($record->cuota - $interes), 2);
            $cxc_pay['interes_c'] = round($interes, 2);
            $cxc_pay['paga'] = 0;
            $cxc_pay['amortiza'] = round($record->cuota - $interes, 2);
            $cxc_pay['created_at'] = Carbon::now('America/Costa_Rica')->format('Y-m-d H:i:s');
            $payment_do = PaymentRevenue::create($cxc_pay);
            if ($payment_do->monto_general == 0) {
                $revenue_to_update = Revenue::findOrFail($id);
                $status["status"] = 1;
                $revenue_to_update->update($status);
            }
            DB::commit();
            return response()->json(['success' => true, 'msg' => $monto_general]);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'msg' => 'Ocurrió un error al insertar la linea']);
        }
    }
    public function nuevaVenta(Request $request, $revenue_id)
    {
        if (!auth()->user()->can('cxc.create')) {
            return response()->json(['success' => false, 'msg' => 'No cuentas con permisos para realizar esta operación']);
        }
        try {
            DB::beginTransaction();
            $business_id = $request->session()->get('user.business_id');

            $revenue = Revenue::where('id', $revenue_id)
                ->where('business_id', $business_id)
                ->firstOrFail();

            // Current balance: last monto_general
            $lastPayment = PaymentRevenue::where('revenue_id', $revenue_id)
                ->orderBy('id', 'desc')
                ->first();
            $saldo_actual = $lastPayment ? floatval($lastPayment->monto_general) : floatval($revenue->valor_total);

            $monto_vehiculo_recibido = floatval(str_replace(',', '', $request->monto_vehiculo_recibido ?? 0));
            $monto_efectivo = floatval(str_replace(',', '', $request->monto_efectivo ?? 0));
            $monto_reconocimiento = floatval(str_replace(',', '', $request->monto_reconocimiento ?? 0));
            $precio_nuevo_vehiculo = floatval(str_replace(',', '', $request->precio_nuevo_vehiculo));
            $nuevo_vehiculo_id = $request->nuevo_vehiculo_id;
            $plazo = $request->plazo;
            $tasa = $request->tasa;
            $cuota = floatval(str_replace(',', '', $request->cuota));

            $total_descuentos = $monto_vehiculo_recibido + $monto_efectivo + $monto_reconocimiento;
            $saldo_luego = round($saldo_actual - $total_descuentos, 2);

            if ($saldo_luego > 0) {
                return response()->json([
                    'success' => false,
                    'msg' => 'No es posible realizar la nueva venta. El saldo pendiente después de los descuentos es de ' . number_format($saldo_luego, 2, '.', ',')
                ]);
            }

            $sobrante = round(abs($saldo_luego), 2);
            $nuevo_financiamiento = round($precio_nuevo_vehiculo - $sobrante, 2);

            if ($nuevo_financiamiento < 0) {
                $nuevo_financiamiento = 0;
            }

            $today = Carbon::now('America/Costa_Rica');

            // Line 1: cancels current debt
            $ref1 = $this->transactionUtil->getInvoiceNumber($business_id, 'final', '');
            PaymentRevenue::create([
                'revenue_id'    => $revenue_id,
                'referencia'    => $ref1,
                'created_at'    => $today,
                'fecha_interes' => $today,
                'detalle'       => 'Cancela deuda por nueva venta',
                'paga'          => 0,
                'amortiza'      => 0,
                'interes_c'     => 0,
                'monto_general' => 0,
            ]);

            // Line 2: new debt balance
            $ref2 = $this->transactionUtil->getInvoiceNumber($business_id, 'final', '');
            PaymentRevenue::create([
                'revenue_id'    => $revenue_id,
                'referencia'    => $ref2,
                'created_at'    => $today,
                'fecha_interes' => $today,
                'detalle'       => 'Nueva deuda',
                'paga'          => 0,
                'amortiza'      => 0,
                'interes_c'     => 0,
                'monto_general' => $nuevo_financiamiento,
            ]);

            // Update plan_venta
            $plan = \App\Models\PlanVenta::where('id', $revenue->plan_venta_id)->firstOrFail();
            $plan->update([
                'vehiculo_venta_id' => $nuevo_vehiculo_id,
                'monto_recibo'      => $monto_vehiculo_recibido,
                'monto_efectivo'    => $monto_efectivo,
                'total_recibido'    => $total_descuentos,
                'total_financiado'  => $nuevo_financiamiento,
                'venta_sin_rebajos' => $precio_nuevo_vehiculo,
            ]);

            // Update revenue (CxC)
            $revenue->update([
                'tasa'        => $tasa,
                'cuota'       => $cuota,
                'plazo'       => $plazo,
                'valor_total' => $nuevo_financiamiento,
                'status'      => 0,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Nueva venta registrada correctamente. La tabla se actualizará.']);
        } catch (Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Ocurrió un error: ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('cxc.delete')) {
            $output = [
                'success' => false,
                'msg' => __("No cuentas con permisos para eliminar cuentas")
            ];
            return $output;
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $revenue = Revenue::where('business_id', $business_id)
                    ->where('id', $id)
                    ->first();
                $revenue->delete();


                $output = [
                    'success' => true,
                    'msg' => 'cuenta por cobrar removida.'
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }
    public function receive($id, $rev_id, Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $item = DB::table('revenues as rev')
            ->join('plan_ventas as pv', 'rev.plan_venta_id', '=', 'pv.id')
            ->leftJoin('products as vv', 'pv.vehiculo_venta_id', '=', 'vv.id')
            ->join('contacts as cli', 'rev.contact_id', '=', 'cli.id')
            ->select(
                'vv.name as veh_venta',
                'vv.model as modelo',
                'vv.placa as placa',
                'vv.id as vehiculo_id',
                'pv.numero as numero',
                'rev.id as id',
                'rev.plan_venta_id as plan_venta_id',
                'rev.created_at as created_at',
                'rev.tipo_prestamo as tipo_prestamo',
                'rev.moneda as moneda',
                'rev.cuota as cuota',
                'rev.status as status',
                'rev.tasa as tasa',
                'rev.valor_total as valor_total',
                'rev.detalle as detalle',
                'rev.plazo as plazo',
                'cli.id as cliente_id',
                'cli.name as name',
                'cli.identificacion as identificacion',
                'cli.tipo_identificacion as tipo_identificacion',
                'cli.landline as telephone',
                'cli.mobile as celular',
                'cli.email as email',
                'cli.landmark as direccion',
                'pv.tipo_registro as tipo_registro'
            )
            ->where('cli.id', $id)
            ->where('rev.id', $rev_id)
            ->first();

        $contact = Contact::where('business_id', $business_id)
            ->find($item->cliente_id);
        $contacts = Contact::contactDropdownCustomer($business_id, true, true, $id, $rev_id);

        $canUpdate = true;
        if (!auth()->user()->can('cxc.update')) {
            $canUpdate = false;
        }

        if (request()->ajax()) {
            $payment_revenues = PaymentRevenue::where('payment_revenues.revenue_id', $item->id)
                ->select([
                    'payment_revenues.id as id',
                    'payment_revenues.created_at as created_at',
                    'payment_revenues.fecha_interes as fecha_interes',
                    'payment_revenues.referencia as referencia',
                    'payment_revenues.detalle as detalle',
                    'payment_revenues.paga as paga',
                    'payment_revenues.amortiza as amortiza',
                    DB::raw('null as empty'),
                    DB::raw('null as empty_email'),
                    'payment_revenues.interes_c as interes_c',
                    'payment_revenues.monto_general as monto_general',
                    'payment_revenues.revenue_id as rev_id'
                ])->orderBy('created_at', 'asc');

            /*  if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $payment_revenues->whereDate('payment_revenues.created_at', '>=', $start);
            } */
            return Datatables::of($payment_revenues)
                ->addColumn(
                    'action',
                    '<a href="{{ action(\'RevenueController@viewPayment\', [$id,$rev_id]) }}" class="btn btn-xs btn-info view-payment"><i class="glyphicon glyphicon-print"></i></a>
                     @can("cxc.delete")
                        <button data-href="{{ action(\'RevenueController@destroyRow\', [$id]) }}" class="btn btn-xs btn-danger delete_row_button"><i class="glyphicon glyphicon-trash"></i></button>
                    @endcan
                    '
                )
                ->addColumn(
                    'calcular',
                    '
                     @can("cxc.update")
                        <button data-href="{{ action(\'RevenueController@updateCalc\', [$id]) }}" class="btn btn-xs btn-success update_row_button text-center"><i class="fas fa-calculator"></i></button>
                    @endcan
                    '
                )
                ->editColumn(
                    'created_at',
                    '@can("cxc.update")
                    {!! Form::text("created_at", @format_date($created_at), array_merge(["class" => "form-control fecha"])) !!}
                    @else
                    {!! Form::text("created_at", @format_date($created_at), array_merge(["class" => "form-control"], ["readonly"])) !!}
                    @endcan'
                )
                ->editColumn(
                    'fecha_interes',
                    '@can("cxc.update")
                    {!! Form::text("fecha_interes", @format_date($fecha_interes), array_merge(["class" => "form-control fecha"])) !!}
                    @else
                    {!! Form::text("fecha_interes", @format_date($fecha_interes), array_merge(["class" => "form-control"], ["readonly"])) !!}
                    @endcan'
                )
                ->editColumn(
                    'referencia',
                    '
                    @can("cxc.update")
                    {!! Form::text("referencia", $referencia, array_merge(["class" => "form-control"])) !!}
                    @else
                    {!! Form::text("referencia", $referencia, array_merge(["class" => "form-control"],  ["readonly"])) !!}
                    @endcan
                    
                '
                )
                ->editColumn(
                    'detalle',
                    '
                    @can("cxc.update")
                    {!! Form::text("detalle", $detalle, array_merge(["class" => "form-control"])) !!}
                    @else
                    {!! Form::text("detalle", $detalle, array_merge(["class" => "form-control"],  ["readonly"])) !!}
                    @endcan                    
                '
                )
                ->editColumn(
                    'paga',
                    '@can("cxc.update")
                    {!! Form::text("paga", number_format($paga, 2, ".", ","), array_merge(["class" => "form-control number"])) !!}
                    @else
                    {!! Form::text("paga", number_format($paga, 2, ".", ","), array_merge(["class" => "form-control"], ["readonly"])) !!}
                    @endcan'
                )
                ->editColumn(
                    'amortiza',
                    '@can("cxc.update")
                    {!! Form::text("amortiza", number_format($amortiza, 2, ".", ","), array_merge(["class" => "form-control number"])) !!}
                    @else
                    {!! Form::text("amortiza", number_format($amortiza, 2, ".", ","), array_merge(["class" => "form-control"], ["readonly"])) !!}
                    @endcan'
                )
                ->editColumn(
                    'interes_c',
                    '@can("cxc.update")
                    {!! Form::text("interes_c", number_format($interes_c, 2, ".", ","), array_merge(["class" => "form-control number"])) !!}
                    @else
                    {!! Form::text("interes_c", number_format($interes_c, 2, ".", ","), array_merge(["class" => "form-control"], ["readonly"])) !!}
                    @endcan'
                )
                ->editColumn(
                    'monto_general',
                    '@can("cxc.update")
                    {!! Form::text("monto_general", number_format($monto_general, 2, ".", ","), array_merge(["class" => "form-control saldo number"])) !!}
                    @else
                    {!! Form::text("monto_general", number_format($monto_general, 2, ".", ","), array_merge(["class" => "form-control saldo"], ["readonly"])) !!}
                    @endcan'
                )
                ->rawColumns(['action', 'calcular', 'fecha_interes', 'created_at', 'referencia', 'detalle',  'monto_general', 'amortiza', 'paga', 'interes_c', 'monto_general'])
                ->make(true);
        }

        $saldoActual = PaymentRevenue::where('revenue_id', $item->id)
            ->orderBy('id', 'desc')
            ->value('monto_general') ?? $item->valor_total;

        $paymentCount = PaymentRevenue::where('revenue_id', $item->id)->count();

        $business = \App\Models\Business::find($business_id);
        $enableNuevaVenta = $business->enable_nueva_venta ?? false;

        $revenueModel = \App\Models\Revenue::find($item->id);
        $publicToken  = $revenueModel ? $revenueModel->ensurePublicToken() : null;

        // Token del portal consolidado del cliente
        $contactModel = $revenueModel ? $revenueModel->contact : null;
        $clienteToken = $contactModel ? $contactModel->ensureClienteToken() : null;

        $esPrestamo = isset($item->tipo_registro) && $item->tipo_registro == 2;

        return view('revenues.receive', compact('item', 'id', 'canUpdate', 'contacts', 'contact', 'saldoActual', 'paymentCount', 'enableNuevaVenta', 'publicToken', 'clienteToken', 'esPrestamo'));
    }
    public function updatePayment(Request $request, $id, $revenue_id)
    {
        try {
            DB::beginTransaction();
            $detalle_planilla = PaymentRevenue::findOrFail($id);
            $revenue_main = Revenue::findOrFail($revenue_id);
            $column = $request->input('column');
            $value = $request->input('value');
            $saldo_anterior = $request->input('saldo_anterior');
            $fecha_interes_anterior = $request->input('fecha_pago_anterior');
            $fecha_interes_act = $request->input('fecha_interes_act');
            $fechaAnteriorInteresRequest = $this->convertDates($fecha_interes_anterior);
            $fechaActInteresRequest = $this->convertDates($fecha_interes_act);
            if (Carbon::parse($fechaActInteresRequest)->greaterThanOrEqualTo($fechaAnteriorInteresRequest)) {
                $fechaActual = Carbon::parse($fechaActInteresRequest)->startOfDay();
                $diasCalc = $fechaAnteriorInteresRequest->diffInDays($fechaActual);
            } else {
                if ($column != 'created_at' && $column != 'detalle' && $column != 'referencia') {
                    return response()->json(['success' => true, 'msg' => -1, 'fecha_interes' => $fechaAnteriorInteresRequest . ' - ' . $fechaActInteresRequest]);
                }
            }
            $detalle[$column] = $value;
            $data = null;
            $data[$column] = is_numeric($value) ? number_format($value, 2, '.', ',') : null;
            if ($column == "created_at" || $column == "fecha_interes") {
                $fechaFormateada = $this->convertDates($value);

                if ($fechaFormateada == null) {
                    return response()->json(['success' => true, 'msg' => 'Formato de fecha inválido, formato correcto (dd/MM/yyyy o dd/MM/yy)']);
                }
                // Verifica que la fecha formateada coincide exactamente con el valor ingresado
                if ($fechaFormateada && ($fechaFormateada->format('d/m/Y') === $value || $fechaFormateada->format('d/m/y') === $value)) {
                    // Aquí puedes continuar con la lógica deseada si el formato es válido
                } else {
                    return response()->json(['success' => true, 'msg' => 'Formato de fecha inválido']);
                }

                $detalle[$column] = $fechaFormateada;
                $data[$column] = $fechaFormateada->format('d/m/Y');
            }
            $detalle_planilla->update($detalle);
            if ($column === "paga") {
                $record = PaymentRevenue::where('payment_revenues.revenue_id', $revenue_id)
                    ->where('payment_revenues.id', '!=', $id)
                    ->join('revenues', 'payment_revenues.revenue_id', '=', 'revenues.id')
                    ->select(
                        'payment_revenues.id',
                        'payment_revenues.created_at',
                        'payment_revenues.fecha_interes',
                        'payment_revenues.monto_general',
                        'revenues.tasa',
                        'revenues.valor_total as monto_general_first',
                        'revenues.cuota',
                        'revenues.status'
                    )
                    ->orderBy('payment_revenues.monto_general', 'asc')
                    ->first();
                $lastRecord = PaymentRevenue::where('payment_revenues.revenue_id', $revenue_id)
                    ->orderBy('id', 'desc')->first();

                //Validar si el siguiente pago lleva meses atrasados                
                $pago_diario = $this->calcPagoDiario($saldo_anterior, $record->tasa);

                if ($id == $lastRecord->id) {
                    //$monto_general = isset($record->monto_general) ? $record->monto_general : $record->monto_general_first;
                    $interes = $record->tasa == 0 ? 0 : round($pago_diario * $diasCalc, 2);
                    $cxc_pay = null;
                    if ($revenue_main->auto_calc == 1) {
                        $cxc_pay['monto_general'] = round($saldo_anterior - ($value - $interes), 2);
                        //$cxc_pay['fecha_interes'] = Carbon::createFromFormat('d/m/Y', $fecha_interes_cero);
                        $cxc_pay['interes_c'] =  round($interes, 2);
                        $cxc_pay['amortiza'] = round($value - $interes, 2);
                    }
                    if (isset($cxc_pay)) {
                        $detalle_planilla->update($cxc_pay);
                        $data = $cxc_pay;
                        // Aplicar formato a los números
                        $data['monto_general'] = number_format($data['monto_general'], 2, '.', ',');
                        $data['interes_c'] = number_format($data['interes_c'], 2, '.', ',');
                        $data['amortiza'] = number_format($data['amortiza'], 2, '.', ',');
                    }
                    $data['paga'] = number_format($value, 2, '.', ',');
                    //Actualizar estado de la cuenta por cobrar
                    $status["status"] = $detalle_planilla['monto_general'] == 0 ? 1 : 0;
                    if ($status["status"] != $record->status) {
                        $revenue_to_update = Revenue::findOrFail($revenue_id);
                        $revenue_to_update->update($status);
                    }
                    //Actualizar estado de la cuenta por cobrar            
                }
            }
            DB::commit();
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false, 'msg' => $th->getMessage(), 'data' => $data]);
        }
        return response()->json(['success' => true, 'msg' => null, 'data' => $data, 'auto_calc' => $revenue_main]);
    }
    public function convertDates($date)
    {

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            // Formato dd/MM/yyyy
            $fechaFormateada = Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{2}$/', $date)) {
            // Formato dd/MM/yy
            $fechaFormateada = Carbon::createFromFormat('d/m/y', $date)->endOfDay();
        } else {
            $fechaFormateada = null;
        }
        return $fechaFormateada;
    }
    public function calcPagoDiario($saldo_anterior, $tasa)
    {
        $mes_dias = 30;
        $calc_interes = $tasa == 0 ? $saldo_anterior : $saldo_anterior * ($tasa / 100);
        $pago_diario = $calc_interes / $mes_dias;
        return $pago_diario;
    }
    public function updateCalc(Request $request, $id)
    {
        $detalle_planilla = PaymentRevenue::findOrFail($id);
        $saldo = $request->input('saldo');
        $paga = $request->input('paga');
        $tasa = $request->input('tasa');
        //Se validan fechas anteriores        
        $fecha_interes_anterior = $request->input('fecha_anterior_int');
        $fecha_actual = $request->input('fecha_actual');
        //Nueva logica
        $fechaAnteriorInteresRequest = $this->convertDates($fecha_interes_anterior);
        $fechaActInteresRequest = $this->convertDates($fecha_actual);
        $pago_diario = $this->calcPagoDiario($saldo, $tasa);
        if (Carbon::parse($fechaActInteresRequest)->greaterThanOrEqualTo($fechaAnteriorInteresRequest)) {
            $fechaActual = Carbon::parse($fechaActInteresRequest)->startOfDay();
            $diasCalc = $fechaAnteriorInteresRequest->diffInDays($fechaActual);
        } else {
            return response()->json(['success' => true, 'msg' => -1]);
        }
        //Se validan fechas anteriores
        $interes = $tasa == 0 ? 0 : round($pago_diario * $diasCalc, 2);
        $detalle['monto_general'] = round($saldo - ($paga - $interes), 2);
        $detalle['interes_c'] =  round($interes, 2);
        $detalle['amortiza'] =  round($paga - $interes, 2);
        $detalle_planilla->update($detalle);
        $data = $detalle_planilla;
        // Aplicar formato a los números
        $data['monto_general'] = number_format($data['monto_general'], 2, '.', ',');
        $data['paga'] = number_format($paga, 2, '.', ',');
        $data['interes_c'] = number_format($data['interes_c'], 2, '.', ',');
        $data['amortiza'] = number_format($data['amortiza'], 2, '.', ',');
        return response()->json(['success' => true, 'request' => $saldo, 'data' => $data]);
    }
    public function destroyRow($id)
    {
        if (request()->ajax()) {
            try {
                $payment = PaymentRevenue::where('id', $id)->first();
                $rev_id = $payment->revenue_id;
                $count = PaymentRevenue::where('revenue_id', $payment->revenue_id)->count();
                if ($count == 1) {
                    $output = [
                        'success' => false,
                        'msg' => __("No puedes eliminar la primer linea de pago, puede causar inconsistencias, si deseas editar los montos debes hacerlo desde el plan de ventas")
                    ];
                    return $output;
                }
                $payment->delete();
                $queryPayment = PaymentRevenue::where('revenue_id', $rev_id)
                    ->join('revenues', 'payment_revenues.revenue_id', '=', 'revenues.id')
                    ->select(
                        'payment_revenues.id',
                        'payment_revenues.monto_general',
                        'revenues.status'
                    )
                    ->orderBy('id', 'desc')->first();
                $status["status"] = 0;
                if ($queryPayment->monto_general != 0 && $status["status"] != $queryPayment->status) {
                    $revenue_to_update = Revenue::findOrFail($rev_id);
                    $revenue_to_update->update($status);
                }
                $output = [
                    'success' => true,
                    'msg' => 'Linea eliminada con éxito'
                ];
            } catch (\Exception $e) {
                Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => $e->getMessage()
                ];
            }

            return $output;
        }
    }
    public function viewPayment($id, $revenue_id)
    {
        try {
            $item = DB::table('revenues as rev')
                ->join('plan_ventas as pv', 'rev.plan_venta_id', '=', 'pv.id')
                ->join('payment_revenues as pr', 'rev.id', '=', 'pr.revenue_id')
                ->leftJoin('products as vv', 'pv.vehiculo_venta_id', '=', 'vv.id')
                ->join('contacts as cli', 'rev.contact_id', '=', 'cli.id')
                ->select(
                    'vv.name as veh_venta',
                    'vv.model as modelo',
                    'vv.placa as placa',
                    'vv.combustible as combustible',
                    'vv.color as color',
                    'pv.tipo_registro as tipo_registro',
                    'rev.id as id',
                    'rev.created_at as created_at',
                    'rev.tipo_prestamo as tipo_prestamo',
                    'rev.moneda as moneda',
                    'rev.cuota as cuota',
                    'rev.tasa as tasa',
                    'rev.valor_total as valor_total',
                    'rev.detalle as detalle',
                    'rev.plazo as plazo',
                    'cli.name as name',
                    'cli.identificacion as identificacion',
                    'cli.tipo_identificacion as tipo_identificacion',
                    'cli.landline as telephone',
                    'cli.mobile as celular',
                    'cli.email as email',
                    'cli.landmark as direccion',
                    'pr.paga as paga',
                    'pr.interes_c as interes_c',
                    'pr.amortiza as amortiza',
                    DB::raw("DATE_FORMAT(pr.fecha_interes, '%d/%m/%Y') as fecha_interes"),
                    DB::raw("DATE_FORMAT(pr.created_at, '%d/%m/%Y') as fecha_pago"),
                    'pr.referencia as referencia',
                    'pr.monto_general as monto_general'
                )
                ->where('rev.id', $revenue_id)
                ->where('pr.id', $id)
                ->first();

            return view('revenues.view-modal')->with(compact(
                'item',
                'id'
            ));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
        }
    }
    public function sendPaymentsWhatsDetallado($id, $revenue_id, $type, $email)
    {
        try {
            // Obtener los datos necesarios
            $item = DB::table('revenues as rev')
                ->join('plan_ventas as pv', 'rev.plan_venta_id', '=', 'pv.id')
                ->join('payment_revenues as pr', 'rev.id', '=', 'pr.revenue_id')
                ->leftJoin('products as vv', 'pv.vehiculo_venta_id', '=', 'vv.id')
                ->join('contacts as cli', 'rev.contact_id', '=', 'cli.id')
                ->select(
                    'vv.name as veh_venta',
                    'vv.model as modelo',
                    'vv.placa as placa',
                    'vv.combustible as combustible',
                    'vv.color as color',
                    'pv.tipo_registro as tipo_registro',
                    'rev.id as id',
                    'rev.created_at as created_at',
                    'rev.tipo_prestamo as tipo_prestamo',
                    'rev.moneda as moneda',
                    'rev.cuota as cuota',
                    'rev.tasa as tasa',
                    'rev.valor_total as valor_total',
                    'rev.detalle as detalle',
                    'rev.plazo as plazo',
                    'cli.name as name',
                    'cli.identificacion as identificacion',
                    'cli.tipo_identificacion as tipo_identificacion',
                    'cli.landline as telephone',
                    'cli.mobile as celular',
                    'cli.email as email',
                    'cli.landmark as direccion',
                    'pr.paga as paga',
                    'pr.interes_c as interes_c',
                    'pr.fecha_interes as fecha_interes',
                    'pr.created_at as fecha_pago',
                    'pr.amortiza as amortiza',
                    'pr.referencia as referencia',
                    'pr.monto_general as monto_general'
                )
                ->where('rev.id', $revenue_id)
                ->where('pr.id', $id)
                ->first();

            // Nombre del PDF y generación
            $namePdf = "Comprobante_de_recibo_de_pago_" . time() . ".pdf";
            $for_pdf = true;

            // Leer el archivo de imagen y codificar en base64
            $logo_path = public_path('images/logo_ag_cor.png');
            $logo_data = base64_encode(file_get_contents($logo_path));
            $logo = 'data:image/png;base64,' . $logo_data;

            // Renderización del HTML para PDF
            $html = view('revenues.whats')->with(compact('item', 'for_pdf', 'logo'))->render();
            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);

            // Definir la ruta para guardar el archivo en una ubicación pública
            $file = public_path('pdfs/') . $namePdf;
            $mpdf->Output($file, 'F');

            // Crear el enlace de WhatsApp
            $publicFileUrl = url('pdfs/' . $namePdf);
            $whatsappLink = "";
            if ($type === "whats") {
                $whatsappMessage = urlencode("Hola, en la siguiente URL puedes encontrar el recibo del pago realizado " . $publicFileUrl);
                $whatsappNumber = $item->celular;
                $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
            } else {
                if ($item->email != "" && $email) {
                    $data = [
                        'to_email' => $email != "" ? $email : $item->email,
                        'subject' => "Recibo de dinero del día " . $item->fecha_pago . " - " . $item->name,
                        'email_body' => 'Adjunto encuentra el PDF del recibo de pago'
                    ];

                    $data['email_settings'] = request()->session()->get('business.email_settings');

                    $data['attachment'] =  $file;
                    $data['attachment_name'] =  $namePdf;
                    Notification::route('mail', $data['to_email'])->notify(new CustomerNotification($data));

                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            }

            // Retornar el enlace en la respuesta AJAX
            $output = [
                'success' => true,
                'msg' => __('Se generó el enlace para compartir por WhatsApp, se abrió una nueva ventana'),
                'type' => $type,
                'whatsapp_link' => $whatsappLink
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => "File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage()
            ];
        }

        return $output;
    }
    public function sendReportToClient(Request $request)
    {
        try {
            // Obtener los datos del request
            $htmlContent = $request->input('html_content');
            $name = $request->input('name');
            $dates = $request->input('dates');
            $vehiculo = $request->input('vehiculo');
            $modelo = $request->input('modelo');
            $placa = $request->input('placa');
            $toEmail = $request->input('email');

            // Nombre del PDF a generar
            $namePdf = "Estado_de_Cuenta_" . time() . ".pdf";
            $for_pdf = true;

            // Leer el archivo de imagen y codificar en base64
            $logo_path = public_path('images/logo_ag_cor.png');
            $logo = 'data://text/plain;base64,' . base64_encode(file_get_contents(
                public_path('images/logo_ag_cor.png')
            ));

            // Renderizar el HTML con el logo y otros detalles
            $html = view('revenues.report')->with(compact('htmlContent', 'name', 'dates', 'vehiculo', 'modelo', 'placa', 'logo'))->render();
            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);

            // Guardar el archivo PDF
            $file = public_path('pdfs/') . $namePdf;
            $mpdf->Output($file, 'F');

            // Verificar si hay un correo al cual enviar
            if ($toEmail) {
                $data = [
                    'to_email' => $toEmail,
                    'subject' => "Estado de Cuenta de: " . $name,
                    'email_body' => 'Adjunto encontrarás el PDF con el estado de cuenta solicitado.',
                    'attachment' => $file,
                    'attachment_name' => $namePdf
                ];
                $data['email_settings'] = request()->session()->get('business.email_settings');

                Notification::route('mail', $data['to_email'])->notify(new CustomerNotification($data));

                // Eliminar el archivo después de enviarlo
                // if (file_exists($file)) {
                //     unlink($file);
                // }
            }

            // Retornar el resultado en la respuesta AJAX
            $output = [
                'success' => true,
                'msg' => __('El estado de cuenta se ha enviado por correo.')
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => "File:" . $e->getFile() . " Line:" . $e->getLine() . " Message:" . $e->getMessage()
            ];
        }

        return $output;
    }
    public function updateCheckSms(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $rev_id = $request->rev_id;
            $checked = $request->checked;

            $revenue = Revenue::where('id', $rev_id)
                ->where('business_id', $business_id)
                ->firstOrFail();

            $data = [
                'check_sms' => $checked
            ];

            $revenue->update($data);

            return response()->json(['result' => $revenue->id]);
        } catch (\Exception $th) {
            // Manejo de errores
            return response()->json(['result' => $th->getMessage()]);
        }
    }
    public function sendMassSms(Request $request)
    {
        if (!auth()->user()->can('cxc.view')) {
            return response()->json([
                'message' => 'Acción no autorizada.',
            ], 403);
        }

        $request->validate([
            'message' => 'required|string|max:160', // ajusta si tu proveedor soporta más
            'expense_payment_status' => 'required',
            'location_id' => 'nullable|string',
        ]);

        $message = $request->input('message');
        $paymentStatus = $request->input('expense_payment_status');
        $locationId = $request->input('location_id');

        // Seguridad: solo Cobrado (1)
        if (($paymentStatus == 3 && $paymentStatus == '3') || ($paymentStatus == 4 && $paymentStatus == '4')) {
            return response()->json([
                'message' => 'Solo se pueden enviar SMS a clientes con estado COBRADO y PENDIENTE.',
            ], 422);
        }

        $business_id = $request->session()->get('user.business_id');

        // Base de la consulta: muy similar a index(), pero enfocada a lo que necesitamos
        $revenues = Revenue::where('revenues.business_id', $business_id)
            ->leftJoin('contacts AS ct', 'revenues.contact_id', '=', 'ct.id')
            ->leftJoin('payment_revenues AS pr', 'revenues.id', '=', 'pr.revenue_id')
            ->select([
                'revenues.id as rev_id',
                'revenues.status',
                'revenues.check_sms',
                'revenues.sucursal',
                'ct.id as cliente_id',
                'ct.name',
                DB::raw("
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(
                                    COALESCE(NULLIF(ct.mobile, ''), ct.landline),
                                    '-', ''
                                ),
                                ' ', ''
                            ),
                            '.', ''
                        ),
                        '_', ''
                    ) AS telefono
                "),
                DB::raw('COALESCE(MIN(pr.monto_general),-1) as min_general_amount'),
            ])
            ->groupBy('revenues.id', 'ct.id', 'ct.name', 'ct.mobile', 'revenues.status', 'revenues.check_sms', 'revenues.sucursal');

        // Filtro de estado Cobrado (por coherencia con index)
        // En tu index, "Cobrado" es: status == 1 o min_general_amount <= 0
        // Aquí vamos a exigir status = 1 para ser estrictos con el filtro Cobrado
        $revenues->whereNotIn('revenues.status', [2, 3]);

        // Filtro sucursal (location_id select)
        if (!empty($locationId) && $locationId != 'TODAS') {
            $revenues->where('revenues.sucursal', $locationId);
        }

        // Solo los marcados con check_sms = 1
        $revenues->where('revenues.check_sms', 1);

        // Solo contactos con teléfono no nulo
        $revenues->whereRaw("
            COALESCE(NULLIF(ct.mobile, ''), ct.landline) IS NOT NULL 
            AND COALESCE(NULLIF(ct.mobile, ''), ct.landline) != ''
        ");

        // Ejecutamos consulta
        $rows = $revenues->get();

        if ($rows->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron cuentas COBRADAS marcadas para envío de SMS con teléfono válido.',
            ], 422);
        }

        // Creamos campaña
        $campaign = SmsCampaign::create([
            'name'    => 'Campaña CxC ' . now()->format('Y-m-d H:i'),
            'message' => $message,
            'total_recipients' => 0,
        ]);

        $totalRecipients = 0;
        $revenueIds = [];

        // Si quieres evitar SMS duplicados por cliente en varias CxC, deduplica por cliente_id
        // Aquí haremos un mapa por cliente_id
        $byCustomer = $rows->groupBy('cliente_id');

        foreach ($byCustomer as $clienteId => $revenuesByCustomer) {
            $first = $revenuesByCustomer->first();
            if (empty($first->telefono)) {
                continue;
            }

            // Podrías decidir qué revenue_id asociar (el más reciente, etc.) Aquí uso el primero.
            $revenueId = $first->rev_id;

            $recipient = SmsCampaignRecipient::create([
                'sms_campaign_id' => $campaign->id,
                'cliente_id'      => $clienteId,
                'revenue_id'      => $revenueId,
                'telefono'        => $first->telefono,
                'status'          => 'pending',
            ]);

            $totalRecipients++;
            $revenueIds[] = $revenueId;

            // Encolamos el job
            SendCampaignSmsJob::dispatch($recipient->id);
        }

        // Actualizar total de destinatarios
        $campaign->update([
            'total_recipients' => $totalRecipients,
        ]);

        // Opcional: resetear check_sms de los revenues usados
        if (!empty($revenueIds)) {
            Revenue::whereIn('id', $revenueIds)->update(['check_sms' => 0]);
        }

        if ($totalRecipients === 0) {
            return response()->json([
                'message' => 'No se pudo crear ningún destinatario. Verifique que los clientes tengan teléfono.',
            ], 422);
        }

        return response()->json([
            'message' => "Campaña creada. Destinatarios en cola: {$totalRecipients}",
        ]);
    }
    public function massUpdateCheckSms(Request $request)
    {
        if (!auth()->user()->can('cxc.view')) {
            return response()->json(['message' => 'Acción no autorizada.'], 403);
        }

        $request->validate([
            'check_sms' => 'required|integer|in:0,1',
            'expense_payment_status' => 'required',
            'location_id' => 'nullable|string',
        ]);

        $checkSms = (int) $request->input('check_sms');
        $statusFilter = $request->input('expense_payment_status');
        $locationId = $request->input('location_id');

        $business_id = $request->session()->get('user.business_id');

        $revenues = Revenue::where('revenues.business_id', $business_id);

        // === Mismo mapeo de estado que en index() ===
        if (!empty($statusFilter) && $statusFilter != 4) {
            if ($statusFilter == 1) {
                $revenues->where('revenues.status', 1); // Cobrado
            }
            if ($statusFilter == 2) {
                $revenues->where('revenues.status', 0); // Pendiente
            }
            if ($statusFilter == 3) {
                $revenues->where('revenues.status', 2); // Judicial
            }
            if ($statusFilter == 5) {
                $revenues->where('revenues.status', 3); // Pérdida
            }
        }

        // Filtro de sucursal (location_id)
        if (!empty($locationId) && $locationId != 'TODAS') {
            $revenues->where('revenues.sucursal', $locationId);
        }

        // ⚠️ Si quieres que solo se marquen cobrados, aunque el filtro sea otro,
        // podrías forzar aquí: $revenues->where('revenues.status', 1);

        // Hacemos el update masivo
        $updated = $revenues->update(['check_sms' => $checkSms]);

        if ($updated === 0) {
            return response()->json([
                'message' => 'No se encontraron registros para actualizar con los filtros aplicados.',
            ], 200);
        }

        $actionText = $checkSms === 1 ? 'marcados' : 'desmarcados';

        return response()->json([
            'message' => "Registros {$actionText} para SMS: {$updated}",
        ]);
    }
}
