import 'dart:math';
import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/models/kiosk_vehicle_model.dart';
import 'package:space360_flutter/src/services/agent_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

Future<void> showQuoteFormSheet(BuildContext context, {required KioskVehicleModel vehicle}) {
  return showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) => QuoteFormSheet(vehicle: vehicle),
  );
}

class QuoteFormSheet extends StatefulWidget {
  final KioskVehicleModel vehicle;
  const QuoteFormSheet({super.key, required this.vehicle});

  @override
  State<QuoteFormSheet> createState() => _QuoteFormSheetState();
}

class _QuoteFormSheetState extends State<QuoteFormSheet> {
  final _formKey      = GlobalKey<FormState>();
  final _nameCtrl     = TextEditingController();
  final _phoneCtrl    = TextEditingController();
  final _service      = AgentService();

  // Cotización params
  double _downPaymentPct = 20.0;  // % prima
  int    _termMonths     = 48;
  double _interestRate   = 1.0;   // % mensual
  String _frequency      = 'monthly';
  bool   _loading        = false;

  // Computed quote
  double _monthlyPayment = 0;
  double _totalAmount    = 0;
  double _totalInterest  = 0;

  static const _terms = [12, 24, 36, 48, 60, 72, 84];

  @override
  void initState() {
    super.initState();
    _recalculate();
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    super.dispose();
  }

  double get _vehiclePrice => widget.vehicle.priceRaw;
  double get _downPayment  => _vehiclePrice * (_downPaymentPct / 100);
  double get _principal    => _vehiclePrice - _downPayment;

  // Fórmula de amortización francesa — igual que el backend (VehicleQuote::calculateMonthlyPayment)
  void _recalculate() {
    final principal = _principal;
    if (principal <= 0) {
      setState(() { _monthlyPayment = 0; _totalAmount = 0; _totalInterest = 0; });
      return;
    }

    double payment;
    int periods;

    if (_frequency == 'annual') {
      final years = (_termMonths / 12).ceil();
      final annualRate = (_interestRate * 12) / 100;
      periods = years;
      if (annualRate == 0) {
        payment = principal / years;
      } else {
        payment = principal * (annualRate * pow(1 + annualRate, years)) / (pow(1 + annualRate, years) - 1);
      }
    } else {
      final rate = _interestRate / 100;
      periods = _termMonths;
      if (rate == 0) {
        payment = principal / _termMonths;
      } else {
        payment = principal * (rate * pow(1 + rate, _termMonths)) / (pow(1 + rate, _termMonths) - 1);
      }
    }

    payment = (payment * 100).round() / 100;
    final totalInterest = (payment * periods) - principal;
    setState(() {
      _monthlyPayment = payment;
      _totalInterest  = (totalInterest * 100).round() / 100;
      _totalAmount    = (principal + _totalInterest * 100).round() / 100;
    });
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);

    final result = await _service.saveQuote(
      customerName:    _nameCtrl.text.trim(),
      customerPhone:   _phoneCtrl.text.trim(),
      vehicleId:       widget.vehicle.id,
      vehiclePrice:    _vehiclePrice,
      downPayment:     _downPayment,
      termMonths:      _termMonths,
      interestRate:    _interestRate,
      monthlyPayment:  _monthlyPayment,
      totalAmount:     _totalAmount,
      totalInterest:   _totalInterest,
      paymentFrequency: _frequency,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (result is Success<bool>) {
      Navigator.pop(context);
      Fluttertoast.showToast(msg: '¡Cotización guardada!', backgroundColor: const Color(0xFF2E7D32));
    } else if (result is AppError) {
      Fluttertoast.showToast(msg: (result as AppError<bool>).message, backgroundColor: Colors.red[700]);
    }
  }

  String _formatNum(double v) {
    final s = v.toStringAsFixed(0);
    final buffer = StringBuffer();
    int count = 0;
    for (int i = s.length - 1; i >= 0; i--) {
      if (count > 0 && count % 3 == 0) buffer.write(',');
      buffer.write(s[i]);
      count++;
    }
    return buffer.toString().split('').reversed.join();
  }

  String _currency(double v) => (widget.vehicle.currency == 'USD' ? '\$' : '₡') + _formatNum(v);

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    return Container(
      margin: const EdgeInsets.only(top: 40),
      decoration: const BoxDecoration(
        color: Color(0xFF111111),
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.fromLTRB(20, 20, 20, bottom + 20),
      child: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Center(child: Container(width: 40, height: 4,
                  decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2)))),
              const SizedBox(height: 16),
              Row(children: [
                const Icon(Icons.calculate_outlined, color: _kGold, size: 20),
                const SizedBox(width: 8),
                Expanded(child: Text('Cotización — ${widget.vehicle.name}',
                    style: const TextStyle(color: _kText, fontSize: 15, fontWeight: FontWeight.bold),
                    maxLines: 2, overflow: TextOverflow.ellipsis)),
              ]),
              const SizedBox(height: 20),

              // ── Resultado destacado ──────────────────────────
              _QuoteResult(
                label: _frequency == 'monthly' ? 'Cuota mensual estimada' : 'Cuota anual estimada',
                payment: _currency(_monthlyPayment),
                downPayment: _currency(_downPayment),
                total: _currency(_totalAmount),
                interest: _currency(_totalInterest),
              ),
              const SizedBox(height: 20),

              // ── Prima (slider) ───────────────────────────────
              _SliderRow(
                label: 'Prima',
                value: _downPaymentPct,
                display: '${_downPaymentPct.toInt()}% — ${_currency(_downPayment)}',
                min: 10, max: 90, divisions: 16,
                onChanged: (v) { setState(() => _downPaymentPct = v.roundToDouble()); _recalculate(); },
              ),
              const SizedBox(height: 12),

              // ── Plazo ────────────────────────────────────────
              const Text('Plazo', style: TextStyle(color: _kSubtext, fontSize: 12)),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8, runSpacing: 8,
                children: _terms.map((t) {
                  final sel = t == _termMonths;
                  return GestureDetector(
                    onTap: () { setState(() => _termMonths = t); _recalculate(); },
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                      decoration: BoxDecoration(
                        color: sel ? _kGold : _kSurface,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: sel ? _kGold : Colors.white12),
                      ),
                      child: Text('$t m',
                          style: TextStyle(color: sel ? Colors.black : _kSubtext,
                              fontWeight: sel ? FontWeight.bold : FontWeight.normal, fontSize: 13)),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 12),

              // ── Tasa mensual ──────────────────────────────────
              _SliderRow(
                label: 'Tasa mensual',
                value: _interestRate,
                display: '${_interestRate.toStringAsFixed(1)}%',
                min: 0, max: 5, divisions: 50,
                onChanged: (v) { setState(() => _interestRate = (v * 10).round() / 10); _recalculate(); },
              ),
              const SizedBox(height: 12),

              // ── Frecuencia ────────────────────────────────────
              Row(children: [
                const Text('Frecuencia: ', style: TextStyle(color: _kSubtext, fontSize: 12)),
                const SizedBox(width: 8),
                ...[('monthly', 'Mensual'), ('annual', 'Anual')].map((f) {
                  final sel = _frequency == f.$1;
                  return GestureDetector(
                    onTap: () { setState(() => _frequency = f.$1); _recalculate(); },
                    child: Container(
                      margin: const EdgeInsets.only(right: 8),
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: sel ? _kGold : _kSurface,
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: sel ? _kGold : Colors.white12),
                      ),
                      child: Text(f.$2, style: TextStyle(
                          color: sel ? Colors.black : _kSubtext,
                          fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                          fontSize: 12)),
                    ),
                  );
                }),
              ]),
              const SizedBox(height: 20),

              // ── Datos del cliente ─────────────────────────────
              const Text('Datos del cliente', style: TextStyle(color: _kText, fontSize: 14, fontWeight: FontWeight.bold)),
              const SizedBox(height: 10),
              TextFormField(
                controller: _nameCtrl,
                style: const TextStyle(color: _kText),
                cursorColor: _kGold,
                validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                decoration: _inputDecoration('Nombre *', Icons.person_outline),
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: _phoneCtrl,
                keyboardType: TextInputType.phone,
                style: const TextStyle(color: _kText),
                cursorColor: _kGold,
                validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null,
                decoration: _inputDecoration('Teléfono *', Icons.phone_outlined),
              ),
              const SizedBox(height: 20),

              SizedBox(
                width: double.infinity, height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _kGold, foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _loading ? null : _submit,
                  child: _loading
                      ? const SizedBox(width: 22, height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2.5, valueColor: AlwaysStoppedAnimation(Colors.black)))
                      : const Text('Guardar cotización', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  InputDecoration _inputDecoration(String label, IconData icon) => InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: _kSubtext, fontSize: 13),
        prefixIcon: Icon(icon, color: _kGold, size: 18),
        filled: true,
        fillColor: _kSurface,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: _kGold, width: 1.5)),
        errorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Colors.redAccent)),
        focusedErrorBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Colors.redAccent)),
        errorStyle: const TextStyle(color: Colors.redAccent, fontSize: 11),
      );
}

class _QuoteResult extends StatelessWidget {
  final String label;
  final String payment;
  final String downPayment;
  final String total;
  final String interest;
  const _QuoteResult({required this.label, required this.payment, required this.downPayment, required this.total, required this.interest});

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: _kGold.withOpacity(0.08),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: _kGold.withOpacity(0.3)),
        ),
        child: Column(children: [
          Text(label, style: const TextStyle(color: _kSubtext, fontSize: 12)),
          const SizedBox(height: 4),
          Text(payment, style: const TextStyle(color: _kGold, fontSize: 28, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          Row(mainAxisAlignment: MainAxisAlignment.spaceEvenly, children: [
            _Stat('Prima', downPayment),
            _Stat('Total', total),
            _Stat('Intereses', interest),
          ]),
        ]),
      );
}

class _Stat extends StatelessWidget {
  final String label;
  final String value;
  const _Stat(this.label, this.value);

  @override
  Widget build(BuildContext context) => Column(children: [
        Text(label, style: const TextStyle(color: _kSubtext, fontSize: 10)),
        Text(value, style: const TextStyle(color: _kText, fontSize: 12, fontWeight: FontWeight.w600)),
      ]);
}

class _SliderRow extends StatelessWidget {
  final String label;
  final double value;
  final String display;
  final double min;
  final double max;
  final int divisions;
  final ValueChanged<double> onChanged;

  const _SliderRow({
    required this.label, required this.value, required this.display,
    required this.min, required this.max, required this.divisions, required this.onChanged,
  });

  @override
  Widget build(BuildContext context) => Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
            Text(label, style: const TextStyle(color: _kSubtext, fontSize: 12)),
            Text(display, style: const TextStyle(color: _kGold, fontSize: 12, fontWeight: FontWeight.bold)),
          ]),
          SliderTheme(
            data: SliderTheme.of(context).copyWith(
              activeTrackColor: _kGold,
              inactiveTrackColor: Colors.white12,
              thumbColor: _kGold,
              overlayColor: _kGold.withOpacity(0.2),
              trackHeight: 3,
            ),
            child: Slider(value: value, min: min, max: max, divisions: divisions, onChanged: onChanged),
          ),
        ],
      );
}
