import 'package:flutter/material.dart';
import 'package:space360_flutter/src/pages/home/contact_form.dart';
import 'package:space360_flutter/src/pages/home/home_page.dart';
import 'package:space360_flutter/src/pages/explore/sectors_page.dart';
import 'package:space360_flutter/src/pages/search/search_page.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kSubtext = Color(0xFF9E9E9E);

class BottomNavPage extends StatefulWidget {
  const BottomNavPage({super.key});

  @override
  State<BottomNavPage> createState() => _BottomNavPageState();
}

class _BottomNavPageState extends State<BottomNavPage> {
  int _index = 0;

  static const _pages = <Widget>[
    HomePage(),
    SectorsPage(),
    SearchPage(),
    _ContactTab(),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(index: _index, children: _pages),
      bottomNavigationBar: _NavBar(
        currentIndex: _index,
        onTap: (i) => setState(() => _index = i),
      ),
    );
  }
}

class _NavBar extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTap;

  const _NavBar({required this.currentIndex, required this.onTap});

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
          children: [
            _Item(icon: Icons.home_rounded, label: 'Inicio', selected: currentIndex == 0, onTap: () => onTap(0)),
            _Item(icon: Icons.grid_view_rounded, label: 'Explorar', selected: currentIndex == 1, onTap: () => onTap(1)),
            _Item(icon: Icons.search_rounded, label: 'Buscar', selected: currentIndex == 2, onTap: () => onTap(2)),
            _Item(icon: Icons.chat_bubble_outline_rounded, label: 'Contacto', selected: currentIndex == 3, onTap: () => onTap(3)),
          ],
        ),
      ),
    );
  }
}

class _Item extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onTap;

  const _Item({required this.icon, required this.label, required this.selected, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final color = selected ? _kGold : _kSubtext;
    return Expanded(
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 10),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                decoration: BoxDecoration(
                  color: selected ? _kGold.withOpacity(0.12) : Colors.transparent,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Icon(icon, color: color, size: 22),
              ),
              const SizedBox(height: 2),
              Text(label, style: TextStyle(color: color, fontSize: 10, fontWeight: selected ? FontWeight.bold : FontWeight.normal)),
            ],
          ),
        ),
      ),
    );
  }
}

// ─── Contact tab wraps the existing ContactForm in a scaffold ─────────────────

class _ContactTab extends StatelessWidget {
  const _ContactTab();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        title: const Text('Contacto', style: TextStyle(color: Color(0xFFF0F0F0), fontWeight: FontWeight.bold)),
        centerTitle: false,
        automaticallyImplyLeading: false,
      ),
      body: const SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: ContactForm(),
      ),
    );
  }
}
