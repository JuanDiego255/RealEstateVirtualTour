import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/services/crm_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold    = Color(0xFFD4A843);
const _kSurface = Color(0xFF1A1A1A);
const _kCard    = Color(0xFF222222);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

Future<bool?> showCreateLeadSheet(BuildContext context) {
  return showModalBottomSheet<bool>(
    context: context,
    isScrollControlled: true,
    backgroundColor: _kSurface,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (_) => const _CreateLeadSheet(),
  );
}

class _CreateLeadSheet extends StatefulWidget {
  const _CreateLeadSheet();

  @override
  State<_CreateLeadSheet> createState() => _CreateLeadSheetState();
}

class _CreateLeadSheetState extends State<_CreateLeadSheet> {
  final _nameCtrl     = TextEditingController();
  final _phoneCtrl    = TextEditingController();
  final _emailCtrl    = TextEditingController();
  final _whatsappCtrl = TextEditingController();
  final _notesCtrl    = TextEditingController();

  String _source       = 'other';
  String _priority     = 'medium';
  String _interestType = 'buy';
  bool _saving = false;

  static const _interestTypes = [
    ('buy',   'Compra'),
    ('rent',  'Arriendo'),
    ('trade', 'Canje'),
  ];

  static const _sources = [
    ('website',     'Web'),
    ('whatsapp',    'WhatsApp'),
    ('phone',       'Teléfono'),
    ('referral',    'Referido'),
    ('social_media','Redes'),
    ('other',       'Otro'),
  ];

  static const _priorities = [
    ('low',    'Baja',    Color(0xFF2E7D32)),
    ('medium', 'Media',   Color(0xFFF57F17)),
    ('high',   'Alta',    Color(0xFFE65100)),
    ('urgent', 'Urgente', Color(0xFFC62828)),
  ];

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _whatsappCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: EdgeInsets.fromLTRB(20, 20, 20, MediaQuery.of(context).viewInsets.bottom + 20),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(children: [
              const Expanded(
                child: Text('Nuevo lead', style: TextStyle(color: _kText, fontSize: 17, fontWeight: FontWeight.bold)),
              ),
              IconButton(
                icon: const Icon(Icons.close_rounded, color: _kSubtext),
                onPressed: () => Navigator.pop(context),
              ),
            ]),
            const SizedBox(height: 14),
            _Field(controller: _nameCtrl, hint: 'Nombre *', icon: Icons.person_rounded),
            const SizedBox(height: 10),
            _Field(controller: _phoneCtrl, hint: 'Teléfono *', icon: Icons.phone_rounded,
                keyboardType: TextInputType.phone),
            const SizedBox(height: 10),
            _Field(controller: _emailCtrl, hint: 'Email (opcional)', icon: Icons.email_rounded,
                keyboardType: TextInputType.emailAddress),
            const SizedBox(height: 10),
            _Field(controller: _whatsappCtrl, hint: 'WhatsApp (opcional)', icon: Icons.chat_rounded,
                keyboardType: TextInputType.phone),
            const SizedBox(height: 14),
            const Text('Tipo de interés', style: TextStyle(color: _kSubtext, fontSize: 11)),
            const SizedBox(height: 6),
            Row(
              children: _interestTypes.map((t) {
                final sel = _interestType == t.$1;
                return Expanded(child: GestureDetector(
                  onTap: () => setState(() => _interestType = t.$1),
                  child: Container(
                    margin: const EdgeInsets.only(right: 6),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: sel ? _kGold.withOpacity(0.15) : _kCard,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: sel ? _kGold : Colors.white12),
                    ),
                    child: Center(child: Text(t.$2, style: TextStyle(
                      color: sel ? _kGold : _kSubtext, fontSize: 12,
                      fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                    ))),
                  ),
                ));
              }).toList(),
            ),
            const SizedBox(height: 14),
            const Text('Origen', style: TextStyle(color: _kSubtext, fontSize: 11)),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6, runSpacing: 6,
              children: _sources.map((s) {
                final sel = _source == s.$1;
                return GestureDetector(
                  onTap: () => setState(() => _source = s.$1),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: sel ? _kGold.withOpacity(0.15) : _kCard,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(color: sel ? _kGold : Colors.white12),
                    ),
                    child: Text(s.$2,
                        style: TextStyle(
                          color: sel ? _kGold : _kSubtext,
                          fontSize: 12,
                          fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                        )),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 14),
            const Text('Prioridad', style: TextStyle(color: _kSubtext, fontSize: 11)),
            const SizedBox(height: 6),
            Row(
              children: _priorities.map((p) {
                final sel = _priority == p.$1;
                return Expanded(child: GestureDetector(
                  onTap: () => setState(() => _priority = p.$1),
                  child: Container(
                    margin: const EdgeInsets.only(right: 6),
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    decoration: BoxDecoration(
                      color: sel ? p.$3.withOpacity(0.2) : _kCard,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: sel ? p.$3 : Colors.white12),
                    ),
                    child: Center(
                      child: Text(p.$2,
                          style: TextStyle(
                            color: sel ? p.$3 : _kSubtext,
                            fontSize: 12,
                            fontWeight: sel ? FontWeight.bold : FontWeight.normal,
                          )),
                    ),
                  ),
                ));
              }).toList(),
            ),
            const SizedBox(height: 10),
            _Field(controller: _notesCtrl, hint: 'Notas (opcional)', icon: Icons.note_rounded,
                maxLines: 3),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: _kGold,
                  foregroundColor: Colors.black,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                onPressed: _saving ? null : _save,
                child: _saving
                    ? const SizedBox(height: 18, width: 18,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.black))
                    : const Text('Crear lead', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _save() async {
    final name  = _nameCtrl.text.trim();
    final phone = _phoneCtrl.text.trim();
    if (name.isEmpty || phone.isEmpty) {
      Fluttertoast.showToast(msg: 'Nombre y teléfono son requeridos', backgroundColor: Colors.orange[700]);
      return;
    }
    setState(() => _saving = true);
    final r = await CrmService().createLead(
      name:         name,
      phone:        phone,
      email:        _emailCtrl.text.trim().isEmpty    ? null : _emailCtrl.text.trim(),
      whatsapp:     _whatsappCtrl.text.trim().isEmpty ? null : _whatsappCtrl.text.trim(),
      notes:        _notesCtrl.text.trim().isEmpty    ? null : _notesCtrl.text.trim(),
      source:       _source,
      priority:     _priority,
      interestType: _interestType,
    );
    if (!mounted) return;
    if (r is Success<int>) {
      Navigator.pop(context, true);
      Fluttertoast.showToast(msg: 'Lead creado', backgroundColor: const Color(0xFF2E7D32));
    } else if (r is AppError<int>) {
      setState(() => _saving = false);
      Fluttertoast.showToast(msg: r.message, backgroundColor: Colors.red[700]);
    }
  }
}

class _Field extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  final IconData icon;
  final TextInputType? keyboardType;
  final int maxLines;
  const _Field({
    required this.controller,
    required this.hint,
    required this.icon,
    this.keyboardType,
    this.maxLines = 1,
  });

  @override
  Widget build(BuildContext context) => TextField(
        controller: controller,
        style: const TextStyle(color: _kText, fontSize: 13),
        keyboardType: keyboardType,
        maxLines: maxLines,
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: const TextStyle(color: _kSubtext, fontSize: 13),
          prefixIcon: Icon(icon, size: 18, color: _kSubtext),
          filled: true,
          fillColor: _kCard,
          border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10), borderSide: BorderSide.none),
          contentPadding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
        ),
      );
}
