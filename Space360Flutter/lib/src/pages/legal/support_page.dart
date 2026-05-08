import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

const _kGold     = Color(0xFFD4A843);
const _kGoldDark = Color(0xFFB8922A);
const _kBg       = Color(0xFF0D0D0D);
const _kSurface  = Color(0xFF161616);
const _kText     = Color(0xFFF0F0F0);
const _kSubtext  = Color(0xFF9E9E9E);

const _kWa    = Color(0xFF25D366);
const _kEmail = Color(0xFFD4A843);
const _kWeb   = Color(0xFF63B3FF);

class SupportPage extends StatelessWidget {
  const SupportPage({super.key});

  static const _phone    = '+50686182152';
  static const _phoneFmt = '+506 8618-2152';
  static const _email    = 'virtualtour@space360cr.com';
  static const _web      = 'https://space360cr.com';
  static const _webUrl   = 'https://space360cr.com/soporte';

  Future<void> _launch(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  void _copy(BuildContext context, String text) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Copiado: $text',
            style: const TextStyle(color: Colors.black, fontWeight: FontWeight.w600)),
        backgroundColor: _kGold,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kSurface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, color: _kGold, size: 20),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Soporte',
          style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.w700),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.open_in_browser_rounded, color: _kGold, size: 22),
            tooltip: 'Ver en navegador',
            onPressed: () => _launch(_webUrl),
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 28),
        children: [

          // ── Hero card ──────────────────────────────────────────
          Container(
            padding: const EdgeInsets.fromLTRB(22, 26, 22, 24),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1a1500), Color(0xFF0f0f0f)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              border: Border.all(color: _kGold.withOpacity(0.22)),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Row(
              children: [
                Container(
                  width: 54,
                  height: 54,
                  decoration: BoxDecoration(
                    color: _kGold.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: _kGold.withOpacity(0.3)),
                  ),
                  child: const Icon(Icons.headset_mic_rounded, color: _kGold, size: 26),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        '¿Necesitás ayuda?',
                        style: TextStyle(color: _kText, fontSize: 17, fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 5),
                      Text(
                        'Estamos aquí para vos. Elegí el canal que preferís.',
                        style: TextStyle(color: _kSubtext, fontSize: 12.5, height: 1.45),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 28),

          // ── Section label ──────────────────────────────────────
          const Padding(
            padding: EdgeInsets.only(left: 2, bottom: 14),
            child: Text(
              'CANALES DE CONTACTO',
              style: TextStyle(color: _kSubtext, fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 1.8),
            ),
          ),

          // ── WhatsApp card ──────────────────────────────────────
          _ContactCard(
            icon: Icons.chat_rounded,
            iconBg: _kWa.withOpacity(0.12),
            iconColor: _kWa,
            accentColor: _kWa,
            label: 'WhatsApp',
            value: _phoneFmt,
            hint: 'Lun–Sáb · 8 am – 6 pm',
            buttonLabel: 'Abrir chat',
            buttonIcon: Icons.open_in_new_rounded,
            onButton: () => _launch('https://wa.me/${_phone.replaceAll('+', '')}'),
            onCopy: () => _copy(context, _phoneFmt),
          ),

          const SizedBox(height: 12),

          // ── Email card ─────────────────────────────────────────
          _ContactCard(
            icon: Icons.mail_rounded,
            iconBg: _kEmail.withOpacity(0.12),
            iconColor: _kEmail,
            accentColor: _kEmail,
            label: 'Correo electrónico',
            value: _email,
            hint: 'Respondemos en menos de 24 h',
            buttonLabel: 'Enviar correo',
            buttonIcon: Icons.send_rounded,
            onButton: () => _launch('mailto:$_email'),
            onCopy: () => _copy(context, _email),
          ),

          const SizedBox(height: 12),

          // ── Web card ───────────────────────────────────────────
          _ContactCard(
            icon: Icons.language_rounded,
            iconBg: _kWeb.withOpacity(0.1),
            iconColor: _kWeb,
            accentColor: _kWeb,
            label: 'Sitio web',
            value: 'space360cr.com',
            hint: 'Tours · Vehículos · Información',
            buttonLabel: 'Visitar',
            buttonIcon: Icons.arrow_outward_rounded,
            onButton: () => _launch(_web),
            onCopy: () => _copy(context, _web),
          ),

          const SizedBox(height: 28),

          // ── Hours strip ────────────────────────────────────────
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: _kGold.withOpacity(0.05),
              border: Border.all(color: _kGold.withOpacity(0.18)),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  margin: const EdgeInsets.only(top: 2),
                  child: const Icon(Icons.schedule_rounded, color: _kGold, size: 20),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Horario de atención',
                        style: TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Lunes a Sábado · 8:00 a.m. – 6:00 p.m.\nHora Costa Rica (UTC−6)',
                        style: const TextStyle(color: _kSubtext, fontSize: 12.5, height: 1.6),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Fuera de horario: envianos un correo y te contactamos el siguiente día hábil.',
                        style: TextStyle(color: _kSubtext.withOpacity(0.7), fontSize: 12, height: 1.5),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 36),

          // ── Footer ────────────────────────────────────────────
          Center(
            child: Column(
              children: [
                const Text(
                  'Space 360 CR',
                  style: TextStyle(color: _kGold, fontSize: 13, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 4),
                Text(
                  'Tours Virtuales 360° · Costa Rica',
                  style: const TextStyle(color: _kSubtext, fontSize: 11),
                ),
                const SizedBox(height: 14),
                GestureDetector(
                  onTap: () => _launch(_webUrl),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.open_in_new_rounded, color: _kGold, size: 13),
                      const SizedBox(width: 5),
                      Text(
                        'Ver página de soporte en web',
                        style: TextStyle(
                          color: _kGold,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          decoration: TextDecoration.underline,
                          decorationColor: _kGold.withOpacity(0.5),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}

// ─── Contact card widget ──────────────────────────────────────────────────────

class _ContactCard extends StatelessWidget {
  final IconData icon;
  final Color iconBg;
  final Color iconColor;
  final Color accentColor;
  final String label;
  final String value;
  final String hint;
  final String buttonLabel;
  final IconData buttonIcon;
  final VoidCallback onButton;
  final VoidCallback onCopy;

  const _ContactCard({
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.accentColor,
    required this.label,
    required this.value,
    required this.hint,
    required this.buttonLabel,
    required this.buttonIcon,
    required this.onButton,
    required this.onCopy,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: _kSurface,
        border: Border.all(color: Colors.white.withOpacity(0.07)),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Row(
        children: [
          // Icon
          Container(
            width: 50,
            height: 50,
            decoration: BoxDecoration(
              color: iconBg,
              borderRadius: BorderRadius.circular(14),
            ),
            child: Icon(icon, color: iconColor, size: 24),
          ),
          const SizedBox(width: 14),
          // Text info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: const TextStyle(color: _kSubtext, fontSize: 10.5, fontWeight: FontWeight.w600, letterSpacing: 1),
                ),
                const SizedBox(height: 3),
                Text(
                  value,
                  style: TextStyle(color: _kText, fontSize: 13.5, fontWeight: FontWeight.w700),
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 2),
                Text(
                  hint,
                  style: TextStyle(color: _kSubtext.withOpacity(0.7), fontSize: 11),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          // Action buttons column
          Column(
            children: [
              GestureDetector(
                onTap: onButton,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                  decoration: BoxDecoration(
                    color: accentColor.withOpacity(0.12),
                    border: Border.all(color: accentColor.withOpacity(0.4)),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(buttonIcon, color: accentColor, size: 13),
                      const SizedBox(width: 5),
                      Text(
                        buttonLabel,
                        style: TextStyle(color: accentColor, fontSize: 11.5, fontWeight: FontWeight.w700),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 6),
              GestureDetector(
                onTap: onCopy,
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.copy_rounded, color: _kSubtext, size: 12),
                    const SizedBox(width: 4),
                    Text(
                      'Copiar',
                      style: TextStyle(color: _kSubtext, fontSize: 10.5),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
