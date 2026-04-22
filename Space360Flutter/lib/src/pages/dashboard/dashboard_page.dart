import 'package:flutter/material.dart';
import 'package:space360_flutter/src/services/api_service.dart';

const _kGold = Color(0xFFD4A843);
const _kGoldDark = Color(0xFFA07828);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class DashboardPage extends StatelessWidget {
  const DashboardPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kSurface,
        elevation: 0,
        title: Row(
          children: [
            Container(
              width: 30,
              height: 30,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                gradient: LinearGradient(colors: [_kGold, _kGoldDark]),
              ),
              child: const Icon(Icons.three_sixty_rounded, size: 17, color: Colors.black),
            ),
            const SizedBox(width: 10),
            const Text(
              'Space 360 CR',
              style: TextStyle(color: _kGold, fontSize: 17, fontWeight: FontWeight.w800),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout_rounded, color: _kSubtext, size: 20),
            tooltip: 'Cerrar sesión',
            onPressed: () async {
              await ApiService().clearSession();
              if (context.mounted) {
                Navigator.pushReplacementNamed(context, '/home');
              }
            },
          ),
          const SizedBox(width: 4),
        ],
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 76,
                height: 76,
                decoration: BoxDecoration(
                  color: _kGold.withOpacity(0.08),
                  shape: BoxShape.circle,
                  border: Border.all(color: _kGold.withOpacity(0.25)),
                ),
                child: const Icon(Icons.dashboard_rounded, color: _kGold, size: 36),
              ),
              const SizedBox(height: 20),
              const Text(
                'Panel de administración',
                style: TextStyle(color: _kText, fontSize: 20, fontWeight: FontWeight.w700),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 6),
              const Text(
                'Bienvenido al panel administrativo\nde Space 360 CR.',
                style: TextStyle(color: _kSubtext, fontSize: 13, height: 1.5),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 36),
              _DashCard(
                icon: Icons.photo_library_outlined,
                title: 'Tours',
                subtitle: 'Gestionar tours virtuales',
              ),
              const SizedBox(height: 12),
              _DashCard(
                icon: Icons.people_outline_rounded,
                title: 'Leads / Solicitudes',
                subtitle: 'Ver solicitudes de contacto',
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _DashCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;

  const _DashCard({required this.icon, required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _kSurface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white.withOpacity(0.05)),
      ),
      child: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: _kGold.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: _kGold, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(
                        color: _kText, fontSize: 14, fontWeight: FontWeight.w700)),
                Text(subtitle,
                    style: const TextStyle(color: _kSubtext, fontSize: 12)),
              ],
            ),
          ),
          const Icon(Icons.chevron_right_rounded, color: _kSubtext, size: 18),
        ],
      ),
    );
  }
}
