import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class PrivacyPage extends StatelessWidget {
  const PrivacyPage({super.key});

  static const _webUrl = 'https://space360cr.com/privacidad';

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
          'Política de Privacidad',
          style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.w700),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.open_in_browser_rounded, color: _kGold, size: 22),
            tooltip: 'Ver en navegador',
            onPressed: () async {
              final uri = Uri.parse(_webUrl);
              if (await canLaunchUrl(uri)) launchUrl(uri, mode: LaunchMode.externalApplication);
            },
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
        children: [
          // Header
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF1a1500), Color(0xFF111100)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              border: Border.all(color: _kGold.withOpacity(0.2)),
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: _kGold.withOpacity(0.12),
                        border: Border.all(color: _kGold.withOpacity(0.3)),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Text(
                        'LEGAL',
                        style: TextStyle(color: _kGold, fontSize: 10, fontWeight: FontWeight.w700, letterSpacing: 2),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                const Text(
                  'Política de Privacidad',
                  style: TextStyle(color: _kText, fontSize: 22, fontWeight: FontWeight.w800),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Space 360 CR · Versión 1.0',
                  style: TextStyle(color: _kSubtext, fontSize: 12),
                ),
                const SizedBox(height: 14),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.04),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Text(
                    'Esta política explica qué información recopilamos cuando usás nuestra aplicación, cómo la usamos y cuáles son tus derechos.',
                    style: TextStyle(color: _kSubtext, fontSize: 13, height: 1.55),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),

          _Section(
            icon: Icons.business_rounded,
            title: '1. Responsable del tratamiento',
            content: 'Space 360 CR\nCorreo: privacidad@space360cr.com\nCosta Rica',
          ),

          _Section(
            icon: Icons.storage_rounded,
            title: '2. Datos que recopilamos',
            content:
                'Únicamente recopilamos los datos que vos nos proporcionás voluntariamente al completar el formulario de contacto:\n\n'
                '• Nombre completo (obligatorio)\n'
                '• Teléfono / WhatsApp (obligatorio)\n'
                '• Correo electrónico (opcional)\n'
                '• Descripción del vehículo o mensaje (obligatorio)\n'
                '• Tipo de tour solicitado (opcional)\n\n'
                'No recopilamos datos de ubicación, contactos, fotos, identificadores de dispositivo ni información de pago.',
            highlight: true,
          ),

          _Section(
            icon: Icons.track_changes_rounded,
            title: '3. Para qué usamos tus datos',
            content:
                '• Contactarte para dar seguimiento a tu solicitud de Tour Virtual 360°.\n'
                '• Enviarte información sobre nuestros servicios si lo solicitaste.\n'
                '• Mejorar la calidad de nuestro servicio de atención al cliente.\n\n'
                'No usamos tus datos para publicidad de terceros ni perfilado automatizado.',
          ),

          _Section(
            icon: Icons.gavel_rounded,
            title: '4. Base legal',
            content:
                'El tratamiento de tus datos se basa en tu consentimiento expreso, que otorgás al completar y enviar el formulario de contacto. Podés retirar este consentimiento en cualquier momento contactándonos.',
          ),

          _Section(
            icon: Icons.share_rounded,
            title: '5. Compartición con terceros',
            content:
                'No vendemos, alquilamos ni compartimos tus datos con terceros, excepto:\n\n'
                '• Proveedores de infraestructura (hosting) bajo acuerdos de confidencialidad.\n'
                '• Cuando sea requerido por ley o autoridad competente en Costa Rica.',
          ),

          _Section(
            icon: Icons.schedule_rounded,
            title: '6. Retención de datos',
            content:
                'Conservamos tus datos por un máximo de 2 años desde la última interacción, o hasta que solicitás su eliminación.',
          ),

          _Section(
            icon: Icons.lock_rounded,
            title: '7. Seguridad',
            content:
                'Todas las comunicaciones entre la app y nuestro servidor se realizan mediante HTTPS (TLS). Los datos se almacenan en servidores con acceso restringido.',
          ),

          _Section(
            icon: Icons.verified_user_rounded,
            title: '8. Tus derechos (Ley N.° 8968 CR)',
            content:
                '• Acceso — conocer qué datos tenemos sobre vos.\n'
                '• Rectificación — corregir datos incorrectos.\n'
                '• Supresión — solicitar la eliminación de tus datos.\n'
                '• Oposición — oponerte al tratamiento para fines específicos.\n'
                '• Portabilidad — recibir tus datos en formato estructurado.\n\n'
                'Para ejercer estos derechos escribí a: privacidad@space360cr.com con el asunto "Ejercicio de derechos ARCO". Respondemos en máximo 15 días hábiles.',
          ),

          _Section(
            icon: Icons.child_friendly_rounded,
            title: '9. Menores de edad',
            content:
                'Nuestros servicios están dirigidos a personas mayores de 18 años. No recopilamos intencionalmente datos de menores.',
          ),

          _Section(
            icon: Icons.update_rounded,
            title: '10. Cambios a esta política',
            content:
                'Podemos actualizar esta política ocasionalmente. Te notificaremos de cambios significativos a través de la app. La fecha de versión al inicio del documento refleja la versión vigente.',
          ),

          // Contact card
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: _kGold.withOpacity(0.07),
              border: Border.all(color: _kGold.withOpacity(0.2)),
              borderRadius: BorderRadius.circular(14),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Contacto de privacidad',
                  style: TextStyle(color: _kGold, fontSize: 13, fontWeight: FontWeight.w700),
                ),
                const SizedBox(height: 10),
                const Text(
                  'Space 360 CR\nprivacidad@space360cr.com\nspace360cr.com',
                  style: TextStyle(color: _kSubtext, fontSize: 13, height: 1.6),
                ),
                const SizedBox(height: 14),
                GestureDetector(
                  onTap: () async {
                    final uri = Uri.parse(_webUrl);
                    if (await canLaunchUrl(uri)) launchUrl(uri, mode: LaunchMode.externalApplication);
                  },
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.open_in_new_rounded, color: _kGold, size: 14),
                      const SizedBox(width: 6),
                      Text(
                        'Ver versión completa en web',
                        style: TextStyle(
                          color: _kGold,
                          fontSize: 12,
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

          const SizedBox(height: 32),
          const Text(
            'Política de Privacidad de Space 360 CR\nVersión 1.0 · Costa Rica · Ley N.° 8968',
            textAlign: TextAlign.center,
            style: TextStyle(color: Color(0xFF3a3a3a), fontSize: 11, height: 1.6),
          ),
          const SizedBox(height: 16),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  final IconData icon;
  final String title;
  final String content;
  final bool highlight;

  const _Section({
    required this.icon,
    required this.title,
    required this.content,
    this.highlight = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: highlight ? _kGold.withOpacity(0.04) : _kSurface,
        border: Border.all(
          color: highlight ? _kGold.withOpacity(0.2) : Colors.white.withOpacity(0.06),
        ),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: _kGold, size: 17),
              const SizedBox(width: 9),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                      color: _kText, fontSize: 14, fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            content,
            style: const TextStyle(color: _kSubtext, fontSize: 13, height: 1.65),
          ),
        ],
      ),
    );
  }
}
