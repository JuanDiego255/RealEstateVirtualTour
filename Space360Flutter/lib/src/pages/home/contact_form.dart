import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/services/api_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kGold = Color(0xFFD4A843);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class ContactForm extends StatefulWidget {
  const ContactForm({super.key});

  @override
  State<ContactForm> createState() => _ContactFormState();
}

class _ContactFormState extends State<ContactForm> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _messageCtrl = TextEditingController();
  String _tourType = 'Automóvil';
  bool _loading = false;

  final _api = ApiService();

  static const _tourTypes = [
    'Automóvil',
    'Moto',
    'Propiedad / Inmueble',
    'Nave industrial',
    'Otro',
  ];

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _emailCtrl.dispose();
    _messageCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _loading = true);

    final result = await _api.sendContactLead(
      name: _nameCtrl.text.trim(),
      phone: _phoneCtrl.text.trim(),
      email: _emailCtrl.text.trim(),
      message: _messageCtrl.text.trim(),
      tourType: _tourType,
    );

    if (!mounted) return;
    setState(() => _loading = false);

    if (result is Success) {
      _nameCtrl.clear();
      _phoneCtrl.clear();
      _emailCtrl.clear();
      _messageCtrl.clear();
      setState(() => _tourType = 'Automóvil');
      Fluttertoast.showToast(
        msg: '¡Mensaje enviado! Nos pondremos en contacto pronto.',
        toastLength: Toast.LENGTH_LONG,
        backgroundColor: _kGold,
        textColor: Colors.black,
      );
    } else if (result is AppError) {
      Fluttertoast.showToast(
        msg: (result as AppError).message,
        toastLength: Toast.LENGTH_LONG,
        backgroundColor: Colors.red[700],
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: _kGold.withOpacity(0.15)),
      ),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 4,
                  height: 24,
                  decoration: BoxDecoration(
                    color: _kGold,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                const SizedBox(width: 12),
                const Text(
                  'Solicitar información',
                  style: TextStyle(color: _kText, fontSize: 18, fontWeight: FontWeight.w700),
                ),
              ],
            ),
            const SizedBox(height: 6),
            const Padding(
              padding: EdgeInsets.only(left: 16),
              child: Text(
                'Cuéntanos sobre tu proyecto y te contactamos',
                style: TextStyle(color: _kSubtext, fontSize: 12),
              ),
            ),
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(child: _field(_nameCtrl, 'Nombre completo', Icons.person_outline)),
                const SizedBox(width: 10),
                Expanded(
                  child: _field(
                    _phoneCtrl,
                    'Teléfono / WhatsApp',
                    Icons.phone_outlined,
                    keyboardType: TextInputType.phone,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            _field(
              _emailCtrl,
              'Correo electrónico (opcional)',
              Icons.email_outlined,
              keyboardType: TextInputType.emailAddress,
              required: false,
            ),
            const SizedBox(height: 12),
            _tourTypeDropdown(),
            const SizedBox(height: 12),
            _field(
              _messageCtrl,
              'Mensaje (opcional)',
              Icons.chat_bubble_outline,
              required: false,
              maxLines: 3,
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton.icon(
                onPressed: _loading ? null : _submit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _kGold,
                  foregroundColor: Colors.black,
                  disabledBackgroundColor: _kGold.withOpacity(0.4),
                  elevation: 0,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                icon: _loading
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.black),
                        ),
                      )
                    : const Icon(Icons.send_rounded, size: 18),
                label: Text(
                  _loading ? 'Enviando...' : 'Enviar solicitud',
                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _field(
    TextEditingController ctrl,
    String label,
    IconData icon, {
    TextInputType keyboardType = TextInputType.text,
    bool required = true,
    int maxLines = 1,
  }) {
    return TextFormField(
      controller: ctrl,
      keyboardType: keyboardType,
      maxLines: maxLines,
      style: const TextStyle(color: _kText, fontSize: 14),
      validator: required
          ? (v) => (v == null || v.trim().isEmpty) ? 'Campo requerido' : null
          : null,
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(color: _kSubtext, fontSize: 12),
        prefixIcon: maxLines == 1 ? Icon(icon, color: _kGold, size: 18) : null,
        filled: true,
        fillColor: const Color(0xFF262626),
        contentPadding: EdgeInsets.symmetric(
          vertical: 14,
          horizontal: maxLines > 1 ? 16 : 0,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.white.withOpacity(0.08)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.white.withOpacity(0.08)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: _kGold, width: 1.5),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Colors.redAccent),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Colors.redAccent),
        ),
        errorStyle: const TextStyle(color: Colors.redAccent, fontSize: 11),
      ),
    );
  }

  Widget _tourTypeDropdown() {
    return DropdownButtonFormField<String>(
      value: _tourType,
      dropdownColor: const Color(0xFF1E1E1E),
      style: const TextStyle(color: _kText, fontSize: 14),
      iconEnabledColor: _kGold,
      decoration: InputDecoration(
        labelText: 'Tipo de tour',
        labelStyle: const TextStyle(color: _kSubtext, fontSize: 12),
        prefixIcon: const Icon(Icons.three_sixty_rounded, color: _kGold, size: 18),
        filled: true,
        fillColor: const Color(0xFF262626),
        contentPadding: const EdgeInsets.symmetric(vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.white.withOpacity(0.08)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: BorderSide(color: Colors.white.withOpacity(0.08)),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: _kGold, width: 1.5),
        ),
      ),
      items: _tourTypes
          .map((t) => DropdownMenuItem(value: t, child: Text(t)))
          .toList(),
      onChanged: (v) => setState(() => _tourType = v ?? _tourType),
    );
  }
}
