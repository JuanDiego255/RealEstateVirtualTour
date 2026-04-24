import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/services/agent_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

Future<void> showLeadFormSheet(
  BuildContext context, {
  int? vehicleId,
  String? vehicleName,
}) {
  return showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) => LeadFormSheet(vehicleId: vehicleId, vehicleName: vehicleName),
  );
}

class LeadFormSheet extends StatefulWidget {
  final int? vehicleId;
  final String? vehicleName;

  const LeadFormSheet({super.key, this.vehicleId, this.vehicleName});

  @override
  State<LeadFormSheet> createState() => _LeadFormSheetState();
}

class _LeadFormSheetState extends State<LeadFormSheet> {
  final _formKey     = GlobalKey<FormState>();
  final _nameCtrl    = TextEditingController();
  final _phoneCtrl   = TextEditingController();
  final _emailCtrl   = TextEditingController();
  final _notesCtrl   = TextEditingController();
  final _service     = AgentService();

  String _interestLevel = 'medium';
  bool _loading = false;

  static const _levels = [
    ('low',    'Bajo',     Color(0xFF2E7D32)),
    ('medium', 'Medio',    Color(0xFFF57F17)),
    ('high',   'Alto',     Color(0xFFE65100)),
    ('hot',    '🔥 Hot',   Color(0xFFC62828)),
  ];

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);

    final result = await _service.captureLead(
      name:          _nameCtrl.text.trim(),
      phone:         _phoneCtrl.text.trim(),
      email:         _emailCtrl.text.trim().isEmpty ? null : _emailCtrl.text.trim(),
      vehicleId:     widget.vehicleId,
      interestLevel: _interestLevel,
      notes:         _notesCtrl.text.trim().isEmpty ? null : _notesCtrl.text.trim(),
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (result is Success<bool>) {
      Navigator.pop(context);
      Fluttertoast.showToast(
        msg: '¡Lead guardado correctamente!',
        backgroundColor: const Color(0xFF2E7D32),
      );
    } else if (result is AppError) {
      Fluttertoast.showToast(
        msg: (result as AppError<bool>).message,
        backgroundColor: Colors.red[700],
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottom = MediaQuery.of(context).viewInsets.bottom;
    return Container(
      margin: const EdgeInsets.only(top: 60),
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
              Center(
                child: Container(width: 40, height: 4,
                    decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(2))),
              ),
              const SizedBox(height: 16),
              Row(children: [
                const Icon(Icons.favorite_rounded, color: _kGold, size: 20),
                const SizedBox(width: 8),
                Text(
                  widget.vehicleName != null ? 'Me interesa — ${widget.vehicleName}' : 'Registrar interés',
                  style: const TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold),
                ),
              ]),
              const SizedBox(height: 20),
              _Field(controller: _nameCtrl, label: 'Nombre *', icon: Icons.person_outline,
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null),
              const SizedBox(height: 12),
              _Field(controller: _phoneCtrl, label: 'Teléfono *', icon: Icons.phone_outlined,
                  inputType: TextInputType.phone,
                  validator: (v) => (v == null || v.trim().isEmpty) ? 'Requerido' : null),
              const SizedBox(height: 12),
              _Field(controller: _emailCtrl, label: 'Email (opcional)', icon: Icons.email_outlined,
                  inputType: TextInputType.emailAddress),
              const SizedBox(height: 16),
              const Text('Nivel de interés', style: TextStyle(color: _kSubtext, fontSize: 13)),
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                children: _levels.map((l) => ChoiceChip(
                  label: Text(l.$2),
                  selected: _interestLevel == l.$1,
                  selectedColor: l.$3,
                  labelStyle: TextStyle(
                    color: _interestLevel == l.$1 ? Colors.white : _kSubtext,
                    fontWeight: _interestLevel == l.$1 ? FontWeight.bold : FontWeight.normal,
                    fontSize: 12,
                  ),
                  backgroundColor: _kSurface,
                  side: BorderSide(color: _interestLevel == l.$1 ? l.$3 : Colors.white12),
                  onSelected: (_) => setState(() => _interestLevel = l.$1),
                )).toList(),
              ),
              const SizedBox(height: 12),
              _Field(controller: _notesCtrl, label: 'Notas (opcional)', icon: Icons.notes_outlined, maxLines: 2),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _kGold,
                    foregroundColor: Colors.black,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  onPressed: _loading ? null : _submit,
                  child: _loading
                      ? const SizedBox(width: 22, height: 22,
                          child: CircularProgressIndicator(strokeWidth: 2.5, valueColor: AlwaysStoppedAnimation(Colors.black)))
                      : const Text('Guardar lead', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Field extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final IconData icon;
  final TextInputType inputType;
  final String? Function(String?)? validator;
  final int maxLines;

  const _Field({
    required this.controller,
    required this.label,
    required this.icon,
    this.inputType = TextInputType.text,
    this.validator,
    this.maxLines = 1,
  });

  @override
  Widget build(BuildContext context) => TextFormField(
        controller: controller,
        keyboardType: inputType,
        validator: validator,
        maxLines: maxLines,
        style: const TextStyle(color: _kText),
        cursorColor: _kGold,
        decoration: InputDecoration(
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
        ),
      );
}
