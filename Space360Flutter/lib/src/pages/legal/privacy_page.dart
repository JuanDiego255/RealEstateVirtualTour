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
                  'Space 360 CR · Versión 2.0 · Mayo 2026',
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
                    'Esta política explica qué información recopilamos cuando usás nuestra aplicación y sitio web, cómo la usamos, con quién la compartimos y cuáles son tus derechos según la Ley N.° 8968 de Costa Rica.',
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
            icon: Icons.people_alt_rounded,
            title: '2. Tipos de usuarios y alcance',
            content:
                'Esta política aplica a dos tipos de usuarios:\n\n'
                '• Usuarios públicos — personas que visitan el sitio web o envían el formulario de contacto para solicitar un Tour Virtual 360°.\n\n'
                '• Agentes / usuarios internos — colaboradores que acceden al panel CRM, módulo Kiosko o herramientas administrativas de Space 360 CR para gestionar clientes, citas, propiedades y vehículos.',
          ),

          _Section(
            icon: Icons.storage_rounded,
            title: '3. Datos que recopilamos',
            content:
                'A. Usuarios públicos (formulario de contacto)\n\n'
                '• Nombre completo\n'
                '• Teléfono / WhatsApp\n'
                '• Correo electrónico (opcional)\n'
                '• Descripción del vehículo o mensaje\n'
                '• Tipo de tour solicitado (opcional)\n'
                '• Dirección IP (registro automático de seguridad)\n\n'
                'B. Agentes / usuarios internos (CRM y Kiosko)\n\n'
                '• Credenciales: correo electrónico y contraseña (almacenada con hash).\n'
                '• Datos de clientes gestionados: nombre, teléfono, WhatsApp, correo, dirección, redes sociales, intereses y notas de seguimiento.\n'
                '• Datos financieros de clientes: presupuesto (mínimo/máximo), cotizaciones de vehículos (precio, tasa de interés, enganche, plazo).\n'
                '• Citas: coordenadas de ubicación (GPS), dirección, fecha, hora, notas del resultado de la visita.\n'
                '• Kiosko de eventos: nombre, teléfono, correo y fotografía de prospectos captados en eventos.\n'
                '• Actividad CRM: historial de interacciones, llamadas, visitas y seguimientos.',
            highlight: true,
          ),

          _Section(
            icon: Icons.track_changes_rounded,
            title: '4. Para qué usamos tus datos',
            content:
                'Usuarios públicos:\n'
                '• Contactarte para dar seguimiento a tu solicitud de Tour Virtual 360°.\n'
                '• Enviarte información sobre nuestros servicios si lo solicitaste.\n'
                '• Mejorar la calidad de nuestro servicio de atención al cliente.\n\n'
                'Agentes / usuarios internos:\n'
                '• Gestionar el ciclo de ventas y seguimiento de clientes (CRM).\n'
                '• Agendar y controlar citas de visita a propiedades y vehículos.\n'
                '• Generar cotizaciones de financiamiento de vehículos.\n'
                '• Capturar prospectos en eventos presenciales (Kiosko).\n'
                '• Medir el desempeño comercial por agente.\n\n'
                'No usamos datos para publicidad de terceros ni perfilado automatizado.',
          ),

          _Section(
            icon: Icons.attach_money_rounded,
            title: '5. Datos financieros y cotizaciones',
            content:
                'El módulo de cotizaciones del Kiosko y el CRM procesan información financiera de referencia, incluyendo:\n\n'
                '• Precio del vehículo de interés.\n'
                '• Tasa de interés anual y plazo de financiamiento.\n'
                '• Monto de enganche / prima.\n'
                '• Rango de presupuesto declarado por el prospecto.\n\n'
                'Esta información se usa exclusivamente para generar cotizaciones de referencia y facilitar el proceso de venta. No compartimos datos financieros con entidades crediticias sin el consentimiento explícito del cliente.',
          ),

          _Section(
            icon: Icons.phone_android_rounded,
            title: '6. Almacenamiento local en tu dispositivo',
            content:
                'La aplicación móvil almacena en tu dispositivo:\n\n'
                '• Token de sesión (api_token): guardado de forma local para mantener tu sesión activa sin necesidad de autenticarte cada vez.\n\n'
                'Este dato permanece en tu dispositivo hasta que cerrás sesión. Es una cadena cifrada de autenticación, no contiene información personal visible. Podés eliminarlo cerrando sesión en la aplicación.',
          ),

          _Section(
            icon: Icons.gavel_rounded,
            title: '7. Base legal del tratamiento',
            content:
                'Usuarios públicos: tu consentimiento expreso al enviar el formulario de contacto.\n\n'
                'Agentes / usuarios internos: ejecución del contrato laboral o de prestación de servicios con Space 360 CR, y el interés legítimo en gestionar las operaciones comerciales de la empresa.\n\n'
                'Podés retirar tu consentimiento en cualquier momento contactándonos.',
          ),

          _Section(
            icon: Icons.share_rounded,
            title: '8. Compartición con terceros y proveedores',
            content:
                'Compartimos datos con los siguientes proveedores bajo acuerdos de confidencialidad:\n\n'
                '• Proveedor de correo electrónico (Amazon SES / Mailgun / Postmark): recibe direcciones de correo y contenido de mensajes para el envío de notificaciones.\n'
                '• Almacenamiento en la nube (Amazon S3 o similar): almacena imágenes de tours 360°, fotografías de prospectos y archivos multimedia.\n'
                '• Proveedor de hosting / infraestructura: aloja la base de datos y servidores.\n\n'
                'No vendemos, alquilamos ni cedemos datos personales a terceros con fines comerciales propios. Podemos divulgar datos cuando sea requerido por ley o autoridad competente en Costa Rica.',
          ),

          _Section(
            icon: Icons.schedule_rounded,
            title: '9. Retención de datos',
            content:
                '• Formulario de contacto: máximo 2 años desde la última interacción.\n'
                '• Leads y datos de clientes CRM: mientras el cliente sea activo, y hasta 3 años luego del cierre del caso.\n'
                '• Datos financieros y cotizaciones: 5 años por obligaciones de registro comercial.\n'
                '• Fotografías de eventos (Kiosko): máximo 1 año.\n'
                '• Logs de acceso y seguridad: 90 días.\n\n'
                'Podés solicitar la eliminación de tus datos antes de estos plazos.',
          ),

          _Section(
            icon: Icons.lock_rounded,
            title: '10. Seguridad',
            content:
                '• Las comunicaciones entre la app y el servidor se realizan mediante HTTPS (TLS).\n'
                '• Las contraseñas se almacenan con hash seguro (bcrypt).\n'
                '• El token de sesión se almacena únicamente en tu dispositivo.\n'
                '• Los servidores tienen acceso restringido y monitoreo de seguridad.\n\n'
                'Ningún sistema es 100% seguro. En caso de incidente de seguridad que afecte tus datos, te notificaremos conforme a la legislación aplicable.',
          ),

          _Section(
            icon: Icons.verified_user_rounded,
            title: '11. Tus derechos (Ley N.° 8968 CR)',
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
            title: '12. Menores de edad',
            content:
                'Nuestros servicios están dirigidos a personas mayores de 18 años. No recopilamos intencionalmente datos de menores. Si detectamos que un menor nos ha enviado datos, los eliminaremos de inmediato.',
          ),

          _Section(
            icon: Icons.cookie_rounded,
            title: '13. Cookies y tecnologías similares',
            content:
                'La aplicación móvil no utiliza cookies. El sitio web puede usar cookies técnicas esenciales para el funcionamiento de la sesión. No utilizamos cookies de seguimiento ni de publicidad de terceros.',
          ),

          _Section(
            icon: Icons.update_rounded,
            title: '14. Cambios a esta política',
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
            'Política de Privacidad de Space 360 CR\nVersión 2.0 · Mayo 2026 · Costa Rica · Ley N.° 8968',
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
