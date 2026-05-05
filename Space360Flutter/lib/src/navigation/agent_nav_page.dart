import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/pages/agent/crm/crm_page.dart';
import 'package:space360_flutter/src/pages/agent/dashboard/event_dashboard_page.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/kiosk_vehicles_page.dart';
import 'package:space360_flutter/src/services/api_service.dart';

const _kGold    = Color(0xFFD4A843);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);
const _kBg      = Color(0xFF0D0D0D);

class AgentNavPage extends StatefulWidget {
  final AuthUser user;
  const AgentNavPage({super.key, required this.user});

  @override
  State<AgentNavPage> createState() => _AgentNavPageState();
}

class _AgentNavPageState extends State<AgentNavPage> {
  int _index = 0;

  late final List<Widget> _pages;
  late final List<_NavItem> _items;

  @override
  void initState() {
    super.initState();

    final canKiosk     = widget.user.canAccess('kiosk', 'view');
    final canDashboard = widget.user.canAccess('event_dashboard', 'view');
    final canCrm       = canDashboard || widget.user.isAdmin;

    _pages = [
      if (canKiosk)     KioskVehiclesPage(user: widget.user),
      if (canDashboard) EventDashboardPage(user: widget.user),
      if (canCrm)       CrmPage(user: widget.user),
      _ProfileTab(user: widget.user, onLogout: _logout),
    ];

    _items = [
      if (canKiosk)     const _NavItem(icon: Icons.tablet_rounded,          label: 'Kiosko'),
      if (canDashboard) const _NavItem(icon: Icons.bar_chart_rounded,       label: 'Eventos'),
      if (canCrm)       const _NavItem(icon: Icons.people_alt_rounded,      label: 'CRM'),
      const _NavItem(icon: Icons.person_outline_rounded, label: 'Perfil'),
    ];
  }

  Future<void> _logout() async {
    await ApiService().clearSession();
    if (mounted) Navigator.pushReplacementNamed(context, '/home');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _index, children: _pages),
      bottomNavigationBar: _NavBar(
        items: _items,
        currentIndex: _index,
        onTap: (i) => setState(() => _index = i),
      ),
    );
  }
}

// ─── Nav bar ─────────────────────────────────────────────────────────────────

class _NavItem {
  final IconData icon;
  final String label;
  const _NavItem({required this.icon, required this.label});
}

class _NavBar extends StatelessWidget {
  final List<_NavItem> items;
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _NavBar({required this.items, required this.currentIndex, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: _kSurface,
        border: Border(top: BorderSide(color: Colors.white.withOpacity(0.08))),
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: List.generate(items.length, (i) {
            final selected = i == currentIndex;
            final color = selected ? _kGold : _kSubtext;
            return Expanded(
              child: InkWell(
                onTap: () => onTap(i),
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  child: Column(mainAxisSize: MainAxisSize.min, children: [
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                      decoration: BoxDecoration(
                        color: selected ? _kGold.withOpacity(0.12) : Colors.transparent,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Icon(items[i].icon, color: color, size: 22),
                    ),
                    const SizedBox(height: 2),
                    Text(items[i].label,
                        style: TextStyle(
                          color: color, fontSize: 10,
                          fontWeight: selected ? FontWeight.bold : FontWeight.normal,
                        )),
                  ]),
                ),
              ),
            );
          }),
        ),
      ),
    );
  }
}

// ─── Profile tab ─────────────────────────────────────────────────────────────

class _ProfileTab extends StatelessWidget {
  final AuthUser user;
  final VoidCallback onLogout;

  const _ProfileTab({required this.user, required this.onLogout});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text('Perfil', style: TextStyle(color: _kText, fontWeight: FontWeight.bold)),
      ),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Center(
            child: Column(children: [
              Container(
                width: 72, height: 72,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: const LinearGradient(colors: [_kGold, Color(0xFFA07828)]),
                  boxShadow: [BoxShadow(color: _kGold.withOpacity(0.3), blurRadius: 20)],
                ),
                child: Center(
                  child: Text(
                    user.name.isNotEmpty ? user.name[0].toUpperCase() : 'A',
                    style: const TextStyle(color: Colors.black, fontSize: 28, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              Text(user.name, style: const TextStyle(color: _kText, fontSize: 18, fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text(user.email, style: const TextStyle(color: _kSubtext, fontSize: 13)),
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: _kGold.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: _kGold.withOpacity(0.3)),
                ),
                child: Text(
                  user.role == 'agent' ? 'Agente' : user.role == 'company_admin' ? 'Admin empresa' : 'Super Admin',
                  style: const TextStyle(color: _kGold, fontSize: 12, fontWeight: FontWeight.bold),
                ),
              ),
            ]),
          ),
          const SizedBox(height: 32),
          const Divider(color: Color(0xFF2A2A2A)),
          const SizedBox(height: 16),
          const Text('Permisos activos', style: TextStyle(color: _kSubtext, fontSize: 12)),
          const SizedBox(height: 8),
          if (user.isAdmin)
            _PermChip('Acceso total — Admin')
          else ...[
            if (user.canAccess('kiosk'))            _PermChip('Kiosko'),
            if (user.canAccess('event_dashboard'))  _PermChip('Dashboard Evento'),
            if (user.canAccess('event_dashboard', 'create_lead')) _PermChip('Crear leads'),
            if (user.canAccess('event_dashboard', 'mark_contacted')) _PermChip('Marcar contactado'),
          ],
          const SizedBox(height: 32),
          ElevatedButton.icon(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF1A1A1A),
              foregroundColor: Colors.redAccent,
              side: const BorderSide(color: Colors.redAccent),
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            ),
            icon: const Icon(Icons.logout_rounded, size: 18),
            label: const Text('Cerrar sesión', style: TextStyle(fontWeight: FontWeight.bold)),
            onPressed: onLogout,
          ),
        ],
      ),
    );
  }
}

class _PermChip extends StatelessWidget {
  final String label;
  const _PermChip(this.label);

  @override
  Widget build(BuildContext context) => Container(
        margin: const EdgeInsets.only(bottom: 6),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.white12),
        ),
        child: Row(children: [
          const Icon(Icons.check_rounded, color: Color(0xFF2E7D32), size: 14),
          const SizedBox(width: 8),
          Text(label, style: const TextStyle(color: _kText, fontSize: 13)),
        ]),
      );
}
