import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:space360_flutter/src/pages/home/contact_form.dart';

const _kGold = Color(0xFFD4A843);
const _kGoldDark = Color(0xFFA07828);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kSurface2 = Color(0xFF141414);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      extendBodyBehindAppBar: true,
      appBar: _appBar(context),
      body: SingleChildScrollView(
        child: Column(
          children: [
            _HeroSection(),
            _ServicesSection(),
            _WhyUsSection(),
            _HowItWorksSection(),
            _ContactSection(),
            _Footer(),
          ],
        ),
      ),
    );
  }

  PreferredSizeWidget _appBar(BuildContext context) {
    return AppBar(
      backgroundColor: Colors.transparent,
      elevation: 0,
      flexibleSpace: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [Colors.black.withOpacity(0.6), Colors.transparent],
          ),
        ),
      ),
      title: Row(
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              gradient: LinearGradient(colors: [_kGold, _kGoldDark]),
            ),
            child: const Icon(Icons.three_sixty_rounded, size: 18, color: Colors.black),
          ),
          const SizedBox(width: 10),
          const Text(
            'Space 360 CR',
            style: TextStyle(
              color: _kGold,
              fontSize: 17,
              fontWeight: FontWeight.w800,
              letterSpacing: 0.5,
            ),
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pushNamed(context, '/login'),
          child: const Text(
            'Admin',
            style: TextStyle(color: _kGold, fontWeight: FontWeight.w600, fontSize: 13),
          ),
        ),
        const SizedBox(width: 6),
      ],
    );
  }
}

// ─── Hero ─────────────────────────────────────────────────────────────────────

class _HeroSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final h = MediaQuery.of(context).size.height;
    return Container(
      constraints: BoxConstraints(minHeight: h * 0.82),
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [Color(0xFF050505), _kBg],
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            top: -60,
            right: -60,
            child: _GoldOrb(size: 300, opacity: 0.10),
          ),
          Positioned(
            bottom: 30,
            left: -40,
            child: _GoldOrb(size: 200, opacity: 0.06),
          ),
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(28, 50, 28, 40),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _badge('Virtual Tour 360°'),
                  const SizedBox(height: 24),
                  const Text(
                    'Mostrá tu\nauto como\nnunca antes',
                    style: TextStyle(
                      color: _kText,
                      fontSize: 42,
                      fontWeight: FontWeight.w900,
                      height: 1.08,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 18),
                  Text(
                    'Tours virtuales 360° de alta calidad para\nautomóviles, motos y propiedades.\nDeja que tus clientes exploren cada detalle.',
                    style: TextStyle(
                      color: _kText.withOpacity(0.6),
                      fontSize: 14,
                      height: 1.65,
                    ),
                  ),
                  const SizedBox(height: 32),
                  Row(
                    children: [
                      _GoldButton(
                        label: 'Cotizar ahora',
                        onTap: () {
                          // scroll to contact — no scroll controller needed,
                          // tapping navigates to same page's contact section
                        },
                      ),
                      const SizedBox(width: 14),
                      _OutlineButton(
                        label: 'Ver tours',
                        onTap: () async {
                          final uri = Uri.parse('https://space360cr.com');
                          if (await canLaunchUrl(uri)) launchUrl(uri);
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 48),
                  _statsRow(),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _statsRow() {
    return Row(
      children: [
        _Stat('360°', 'Cobertura total'),
        _vDivider(),
        _Stat('4K', 'Resolución HD'),
        _vDivider(),
        _Stat('CR', 'Costa Rica'),
      ],
    );
  }

  Widget _vDivider() => Container(
        width: 1,
        height: 32,
        margin: const EdgeInsets.symmetric(horizontal: 18),
        color: Colors.white.withOpacity(0.1),
      );
}

class _GoldOrb extends StatelessWidget {
  final double size;
  final double opacity;
  const _GoldOrb({required this.size, required this.opacity});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: RadialGradient(
          colors: [_kGold.withOpacity(opacity), _kGold.withOpacity(0)],
        ),
      ),
    );
  }
}

// ─── Services ─────────────────────────────────────────────────────────────────

class _ServicesSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 56, 24, 56),
      child: Column(
        children: [
          _badge('Servicios'),
          const SizedBox(height: 12),
          const Text(
            'Lo que hacemos',
            style: TextStyle(color: _kText, fontSize: 26, fontWeight: FontWeight.w800),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            'Transformamos vehículos y espacios\nen experiencias inmersivas 360°',
            style: TextStyle(color: _kText.withOpacity(0.5), fontSize: 13, height: 1.5),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          _ServiceCard(
            icon: Icons.directions_car_rounded,
            title: 'Tour de Automóviles',
            description:
                'Mostrá el interior y exterior de tu vehículo con una experiencia interactiva 360° de alta definición. Ideal para agencias y vendedores particulares.',
            highlight: true,
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _ServiceCard(
                  icon: Icons.two_wheeler_rounded,
                  title: 'Motos',
                  description: 'Cada ángulo de tu moto en una experiencia 360° interactiva.',
                  compact: true,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _ServiceCard(
                  icon: Icons.home_work_outlined,
                  title: 'Propiedades',
                  description: 'Tour virtual para casas, apartamentos y naves industriales.',
                  compact: true,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _ServiceCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String description;
  final bool highlight;
  final bool compact;

  const _ServiceCard({
    required this.icon,
    required this.title,
    required this.description,
    this.highlight = false,
    this.compact = false,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(compact ? 18 : 22),
      decoration: BoxDecoration(
        gradient: highlight
            ? const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFF1C1600), Color(0xFF261E00)],
              )
            : null,
        color: highlight ? null : _kSurface,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: highlight ? _kGold.withOpacity(0.35) : Colors.white.withOpacity(0.06),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: compact ? 38 : 46,
            height: compact ? 38 : 46,
            decoration: BoxDecoration(
              color: _kGold.withOpacity(highlight ? 0.18 : 0.1),
              borderRadius: BorderRadius.circular(11),
            ),
            child: Icon(icon, color: _kGold, size: compact ? 20 : 24),
          ),
          SizedBox(height: compact ? 10 : 14),
          Text(
            title,
            style: TextStyle(
              color: _kText,
              fontSize: compact ? 13 : 16,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            description,
            style: TextStyle(
              color: _kSubtext,
              fontSize: compact ? 11 : 13,
              height: 1.55,
            ),
          ),
          if (highlight) ...[
            const SizedBox(height: 14),
            const Row(
              children: [
                Text(
                  'Ver más',
                  style: TextStyle(color: _kGold, fontSize: 13, fontWeight: FontWeight.w600),
                ),
                SizedBox(width: 4),
                Icon(Icons.arrow_forward_rounded, color: _kGold, size: 14),
              ],
            ),
          ],
        ],
      ),
    );
  }
}

// ─── Why Us ───────────────────────────────────────────────────────────────────

class _WhyUsSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(24, 56, 24, 56),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF0F0D00), Color(0xFF0A0900)],
        ),
      ),
      child: Column(
        children: [
          _badge('¿Por qué nosotros?'),
          const SizedBox(height: 12),
          const Text(
            'La diferencia Space 360',
            style: TextStyle(color: _kText, fontSize: 24, fontWeight: FontWeight.w800),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 36),
          _feature(Icons.hd_rounded, 'Calidad 4K',
              'Imágenes de ultra alta resolución que muestran cada detalle con claridad.'),
          _feature(Icons.bolt_rounded, 'Entrega rápida',
              'Tu tour virtual listo en 24–48 horas después de la sesión.'),
          _feature(Icons.devices_rounded, 'Multi-plataforma',
              'Compatible con cualquier dispositivo: móvil, tablet, web y VR.'),
          _feature(Icons.support_agent_rounded, 'Soporte local',
              'Equipo costarricense con atención personalizada y rápida respuesta.',
              last: true),
        ],
      ),
    );
  }

  Widget _feature(IconData icon, String title, String sub, {bool last = false}) {
    return Padding(
      padding: EdgeInsets.only(bottom: last ? 0 : 22),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: _kGold.withOpacity(0.1),
              borderRadius: BorderRadius.circular(11),
              border: Border.all(color: _kGold.withOpacity(0.2)),
            ),
            child: Icon(icon, color: _kGold, size: 20),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(
                        color: _kText, fontSize: 14, fontWeight: FontWeight.w700)),
                const SizedBox(height: 3),
                Text(sub,
                    style: const TextStyle(color: _kSubtext, fontSize: 12, height: 1.5)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── How It Works ─────────────────────────────────────────────────────────────

class _HowItWorksSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(24, 56, 24, 56),
      child: Column(
        children: [
          _badge('Proceso'),
          const SizedBox(height: 12),
          const Text(
            'Así funciona',
            style: TextStyle(color: _kText, fontSize: 24, fontWeight: FontWeight.w800),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 36),
          _step('01', 'Agendás tu cita',
              'Completás el formulario o nos escribís por WhatsApp.'),
          _step('02', 'Fotografiamos tu vehículo',
              'Nuestro equipo llega al lugar y hace la captura 360°.'),
          _step('03', 'Procesamos las imágenes',
              'Editamos y montamos tu tour virtual interactivo en 24h.'),
          _step('04', '¡Publicás y vendés!',
              'Compartís el link o lo embebés en tu sitio web.', last: true),
        ],
      ),
    );
  }

  Widget _step(String num, String title, String sub, {bool last = false}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Column(
          children: [
            Container(
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: const LinearGradient(colors: [_kGold, _kGoldDark]),
                boxShadow: [
                  BoxShadow(color: _kGold.withOpacity(0.22), blurRadius: 10),
                ],
              ),
              child: Center(
                child: Text(
                  num,
                  style: const TextStyle(
                      color: Colors.black, fontSize: 12, fontWeight: FontWeight.w800),
                ),
              ),
            ),
            if (!last)
              Container(width: 1, height: 44, color: _kGold.withOpacity(0.18)),
          ],
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Padding(
            padding: EdgeInsets.only(top: 9, bottom: last ? 0 : 44),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(
                        color: _kText, fontSize: 14, fontWeight: FontWeight.w700)),
                const SizedBox(height: 3),
                Text(sub,
                    style: const TextStyle(color: _kSubtext, fontSize: 12, height: 1.5)),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

// ─── Contact ──────────────────────────────────────────────────────────────────

class _ContactSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      color: _kSurface2,
      padding: const EdgeInsets.fromLTRB(24, 56, 24, 56),
      child: Column(
        children: [
          _badge('Contacto'),
          const SizedBox(height: 12),
          const Text(
            'Iniciemos tu proyecto',
            style: TextStyle(color: _kText, fontSize: 24, fontWeight: FontWeight.w800),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            'Llená el formulario y te contactamos\npor WhatsApp o correo',
            style: TextStyle(color: _kText.withOpacity(0.5), fontSize: 13, height: 1.5),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 32),
          const ContactForm(),
          const SizedBox(height: 24),
          const Divider(color: Colors.white12, height: 1),
          const SizedBox(height: 20),
          Text(
            'También podés contactarnos por',
            style: TextStyle(color: _kSubtext, fontSize: 12),
          ),
          const SizedBox(height: 14),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              _chip(Icons.chat_rounded, 'WhatsApp', () async {
                final uri = Uri.parse('https://wa.me/50600000000');
                if (await canLaunchUrl(uri)) launchUrl(uri);
              }),
              const SizedBox(width: 10),
              _chip(Icons.language_rounded, 'space360cr.com', () async {
                final uri = Uri.parse('https://space360cr.com');
                if (await canLaunchUrl(uri)) launchUrl(uri);
              }),
            ],
          ),
        ],
      ),
    );
  }

  Widget _chip(IconData icon, String label, VoidCallback onTap) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
        decoration: BoxDecoration(
          border: Border.all(color: _kGold.withOpacity(0.3)),
          borderRadius: BorderRadius.circular(22),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: _kGold, size: 15),
            const SizedBox(width: 7),
            Text(label,
                style: const TextStyle(
                    color: _kGold, fontSize: 12, fontWeight: FontWeight.w600)),
          ],
        ),
      ),
    );
  }
}

// ─── Footer ───────────────────────────────────────────────────────────────────

class _Footer extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 24),
      color: const Color(0xFF080808),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 26,
                height: 26,
                decoration: const BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: LinearGradient(colors: [_kGold, _kGoldDark]),
                ),
                child: const Icon(Icons.three_sixty_rounded, size: 15, color: Colors.black),
              ),
              const SizedBox(width: 8),
              const Text(
                'Space 360 CR',
                style: TextStyle(color: _kGold, fontSize: 14, fontWeight: FontWeight.w700),
              ),
            ],
          ),
          const SizedBox(height: 8),
          const Text(
            'Tours virtuales 360° · Costa Rica',
            style: TextStyle(color: _kSubtext, fontSize: 11),
          ),
          const SizedBox(height: 6),
          Text(
            '© ${DateTime.now().year} Space 360 CR. Todos los derechos reservados.',
            style: const TextStyle(color: Color(0xFF444444), fontSize: 10),
          ),
        ],
      ),
    );
  }
}

// ─── Dashboard stub ───────────────────────────────────────────────────────────

class _GoldButton extends StatelessWidget {
  final String label;
  final VoidCallback onTap;
  const _GoldButton({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 13),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [_kGold, _kGoldDark]),
          borderRadius: BorderRadius.circular(11),
          boxShadow: [
            BoxShadow(color: _kGold.withOpacity(0.28), blurRadius: 14, offset: const Offset(0, 4)),
          ],
        ),
        child: Text(
          label,
          style: const TextStyle(
              color: Colors.black, fontSize: 14, fontWeight: FontWeight.w700),
        ),
      ),
    );
  }
}

class _OutlineButton extends StatelessWidget {
  final String label;
  final VoidCallback onTap;
  const _OutlineButton({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 13),
        decoration: BoxDecoration(
          border: Border.all(color: _kGold.withOpacity(0.4)),
          borderRadius: BorderRadius.circular(11),
        ),
        child: Text(
          label,
          style: const TextStyle(
              color: _kGold, fontSize: 14, fontWeight: FontWeight.w600),
        ),
      ),
    );
  }
}

class _Stat extends StatelessWidget {
  final String value;
  final String label;
  const _Stat(this.value, this.label);

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(value,
            style: const TextStyle(
                color: _kGold, fontSize: 20, fontWeight: FontWeight.w800)),
        Text(label,
            style: const TextStyle(color: _kSubtext, fontSize: 10)),
      ],
    );
  }
}

// ─── Shared helpers ───────────────────────────────────────────────────────────

Widget _badge(String label) {
  return Container(
    padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 5),
    decoration: BoxDecoration(
      border: Border.all(color: _kGold.withOpacity(0.4)),
      borderRadius: BorderRadius.circular(18),
    ),
    child: Text(
      label.toUpperCase(),
      style: const TextStyle(
        color: _kGold,
        fontSize: 10,
        fontWeight: FontWeight.w700,
        letterSpacing: 1.5,
      ),
    ),
  );
}
