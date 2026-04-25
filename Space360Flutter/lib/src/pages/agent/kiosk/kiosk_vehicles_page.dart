import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/kiosk_vehicle_model.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/compare_vehicles_page.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/kiosk_vehicle_detail_page.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/lead_form_sheet.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/quote_form_sheet.dart';
import 'package:space360_flutter/src/services/agent_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class KioskVehiclesPage extends StatefulWidget {
  final AuthUser user;
  const KioskVehiclesPage({super.key, required this.user});

  @override
  State<KioskVehiclesPage> createState() => _KioskVehiclesPageState();
}

class _KioskVehiclesPageState extends State<KioskVehiclesPage> {
  final _service = AgentService();
  Resource<List<KioskVehicleModel>>? _state;
  String _search       = '';
  bool   _compareMode  = false;
  final  _compareSet   = <int>{};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _state = null);
    final result = await _service.getVehicles();
    if (mounted) setState(() => _state = result);
  }

  List<KioskVehicleModel> _filtered(List<KioskVehicleModel> all) {
    if (_search.trim().isEmpty) return all;
    final q = _search.toLowerCase();
    return all.where((v) =>
      v.name.toLowerCase().contains(q) ||
      (v.brand?.toLowerCase().contains(q) ?? false) ||
      (v.model?.toLowerCase().contains(q) ?? false) ||
      (v.year?.contains(q) ?? false) ||
      (v.fuelType?.toLowerCase().contains(q) ?? false)
    ).toList();
  }

  void _toggleCompareMode() {
    setState(() {
      _compareMode = !_compareMode;
      if (!_compareMode) _compareSet.clear();
    });
  }

  void _toggleSelect(int id) {
    setState(() {
      if (_compareSet.contains(id)) {
        _compareSet.remove(id);
      } else if (_compareSet.length < 4) {
        _compareSet.add(id);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Máximo 4 vehículos para comparar'),
            behavior: SnackBarBehavior.floating,
            duration: Duration(seconds: 2),
          ),
        );
      }
    });
  }

  void _openCompare() {
    if (_compareSet.length < 2) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Seleccioná al menos 2 vehículos para comparar'),
          behavior: SnackBarBehavior.floating,
          backgroundColor: Color(0xFF333333),
        ),
      );
      return;
    }
    final all  = (_state as Success<List<KioskVehicleModel>>).data;
    final sel  = all.where((v) => _compareSet.contains(v.id)).toList();
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => CompareVehiclesPage(vehicles: sel, user: widget.user)),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: _compareMode ? _compareModeAppBar() : _normalAppBar(),
      floatingActionButton: _compareMode ? _CompareFab(
        count: _compareSet.length,
        onTap: _openCompare,
      ) : null,
      body: Column(
        children: [
          if (!_compareMode)
            _SearchBar(onChanged: (v) => setState(() => _search = v)),
          if (_compareMode)
            _CompareBanner(count: _compareSet.length),
          Expanded(child: _buildBody()),
        ],
      ),
    );
  }

  AppBar _normalAppBar() => AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text('Kiosko',
            style: TextStyle(color: _kText, fontWeight: FontWeight.bold, fontSize: 20)),
        actions: [
          IconButton(
            icon: const Icon(Icons.compare_arrows_rounded, color: _kSubtext),
            tooltip: 'Comparar vehículos',
            onPressed: _toggleCompareMode,
          ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: _kSubtext),
            onPressed: _load,
          ),
        ],
      );

  AppBar _compareModeAppBar() => AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.close_rounded, color: _kText),
          onPressed: _toggleCompareMode,
        ),
        title: Text(
          _compareSet.isEmpty
              ? 'Seleccioná vehículos'
              : 'Comparar (${_compareSet.length}/4)',
          style: const TextStyle(color: _kText, fontWeight: FontWeight.bold, fontSize: 17),
        ),
      );

  Widget _buildBody() {
    if (_state == null) {
      return const Center(
        child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)),
      );
    }
    return switch (_state!) {
      AppError(:final message) => ErrorState(message: message, onRetry: _load),
      Success(:final data) when data.isEmpty =>
        const EmptyState(message: 'No hay vehículos en el Kiosko', icon: Icons.directions_car_outlined),
      Success(:final data) => _buildGrid(_filtered(data)),
      _ => const SizedBox(),
    };
  }

  Widget _buildGrid(List<KioskVehicleModel> vehicles) {
    if (vehicles.isEmpty) {
      return EmptyState(
        message: 'Sin resultados para "$_search"',
        icon: Icons.search_off_rounded,
        actionLabel: 'Limpiar',
        onAction: () => setState(() => _search = ''),
      );
    }

    return RefreshIndicator(
      color: _kGold,
      backgroundColor: _kSurface,
      onRefresh: _load,
      child: GridView.builder(
        padding: EdgeInsets.fromLTRB(16, 16, 16, _compareMode ? 96 : 16),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: _compareMode ? 0.72 : 0.63,
        ),
        itemCount: vehicles.length,
        itemBuilder: (context, i) {
          final v = vehicles[i];
          return _VehicleCard(
            vehicle: v,
            user: widget.user,
            compareMode: _compareMode,
            isSelected: _compareSet.contains(v.id),
            onTap: _compareMode
                ? () => _toggleSelect(v.id)
                : () => Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => KioskVehicleDetailPage(vehicleId: v.id, user: widget.user),
                    ),
                  ),
            onToggleSelect: () => _toggleSelect(v.id),
          );
        },
      ),
    );
  }
}

// ─── Compare banner ───────────────────────────────────────────────────────────

class _CompareBanner extends StatelessWidget {
  final int count;
  const _CompareBanner({required this.count});

  @override
  Widget build(BuildContext context) => Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        color: _kGold.withOpacity(0.08),
        child: Text(
          count == 0
              ? 'Toca los vehículos para seleccionarlos (mín. 2, máx. 4)'
              : '$count seleccionado${count != 1 ? 's' : ''} — toca Comparar para continuar',
          style: const TextStyle(color: _kGold, fontSize: 12),
          textAlign: TextAlign.center,
        ),
      );
}

// ─── Compare FAB ──────────────────────────────────────────────────────────────

class _CompareFab extends StatelessWidget {
  final int count;
  final VoidCallback onTap;
  const _CompareFab({required this.count, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final ready = count >= 2;
    return FloatingActionButton.extended(
      onPressed: onTap,
      backgroundColor: ready ? _kGold : Colors.grey[700],
      label: Text(
        count == 0 ? 'Selecciona 2+' : 'Comparar ($count)',
        style: TextStyle(
          color: ready ? Colors.black : Colors.white54,
          fontWeight: FontWeight.bold,
        ),
      ),
      icon: Icon(Icons.compare_arrows_rounded,
          color: ready ? Colors.black : Colors.white54),
    );
  }
}

// ─── Search bar ───────────────────────────────────────────────────────────────

class _SearchBar extends StatelessWidget {
  final ValueChanged<String> onChanged;
  const _SearchBar({required this.onChanged});

  @override
  Widget build(BuildContext context) => Padding(
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
        child: TextField(
          onChanged: onChanged,
          style: const TextStyle(color: _kText),
          cursorColor: _kGold,
          decoration: InputDecoration(
            hintText: 'Marca, modelo, año...',
            hintStyle: const TextStyle(color: _kSubtext),
            prefixIcon: const Icon(Icons.search_rounded, color: _kSubtext),
            filled: true,
            fillColor: _kSurface,
            border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: _kGold, width: 1.5),
            ),
            contentPadding: const EdgeInsets.symmetric(vertical: 14),
          ),
        ),
      );
}

// ─── Vehicle card ─────────────────────────────────────────────────────────────

class _VehicleCard extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final AuthUser user;
  final bool compareMode;
  final bool isSelected;
  final VoidCallback onTap;
  final VoidCallback onToggleSelect;

  const _VehicleCard({
    required this.vehicle,
    required this.user,
    required this.compareMode,
    required this.isSelected,
    required this.onTap,
    required this.onToggleSelect,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: isSelected
                ? _kGold
                : vehicle.isFeatured
                    ? _kGold.withOpacity(0.4)
                    : Colors.white.withOpacity(0.06),
            width: isSelected ? 2 : 1,
          ),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _CardImage(vehicle: vehicle, compareMode: compareMode, isSelected: isSelected),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(vehicle.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                          color: _kText, fontSize: 13, fontWeight: FontWeight.bold, height: 1.3)),
                  const SizedBox(height: 4),
                  Text(vehicle.price,
                      style: const TextStyle(color: _kGold, fontSize: 14, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 6),
                  _Specs(vehicle: vehicle),
                ],
              ),
            ),
            // quick actions — only in normal mode
            if (!compareMode) _QuickActions(vehicle: vehicle, user: user),
          ],
        ),
      ),
    );
  }
}

class _CardImage extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final bool compareMode;
  final bool isSelected;
  const _CardImage({required this.vehicle, required this.compareMode, required this.isSelected});

  @override
  Widget build(BuildContext context) {
    return AspectRatio(
      aspectRatio: 4 / 3,
      child: Stack(
        children: [
          vehicle.image != null
              ? Image.network(vehicle.image!, fit: BoxFit.cover, width: double.infinity,
                  errorBuilder: (_, __, ___) => _placeholder())
              : _placeholder(),
          // featured badge (normal mode only)
          if (!compareMode && vehicle.isFeatured)
            Positioned(
              top: 6, left: 6,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(color: _kGold, borderRadius: BorderRadius.circular(6)),
                child: const Text('Destacado',
                    style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.black)),
              ),
            ),
          // status badge (normal mode only)
          if (!compareMode && (vehicle.status == 'reserved' || vehicle.status == 'negotiating'))
            Positioned(
              top: 6, right: 6,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(
                    color: const Color(0xFFE67E22), borderRadius: BorderRadius.circular(6)),
                child: Text(
                    vehicle.status == 'reserved' ? 'Reservado' : 'En proceso',
                    style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white)),
              ),
            ),
          // compare mode overlay
          if (compareMode)
            Positioned.fill(
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 180),
                color: isSelected ? _kGold.withOpacity(0.15) : Colors.transparent,
                child: Align(
                  alignment: Alignment.topRight,
                  child: Padding(
                    padding: const EdgeInsets.all(6),
                    child: Icon(
                      isSelected ? Icons.check_circle_rounded : Icons.radio_button_unchecked_rounded,
                      color: isSelected ? _kGold : Colors.white54,
                      size: 22,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _placeholder() => Container(
        color: const Color(0xFF222222),
        child: const Center(child: Icon(Icons.directions_car_rounded, color: Color(0xFF444444), size: 32)),
      );
}

class _Specs extends StatelessWidget {
  final KioskVehicleModel vehicle;
  const _Specs({required this.vehicle});

  @override
  Widget build(BuildContext context) => Wrap(
        spacing: 6, runSpacing: 4,
        children: [
          if (vehicle.fuelType != null)     _Chip(vehicle.fuelType!),
          if (vehicle.transmission != null) _Chip(vehicle.transmission!),
          if (vehicle.doors != null)        _Chip('${vehicle.doors} p.'),
        ],
      );
}

class _Chip extends StatelessWidget {
  final String label;
  const _Chip(this.label);

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.06), borderRadius: BorderRadius.circular(5)),
        child: Text(label, style: const TextStyle(color: _kSubtext, fontSize: 10)),
      );
}

// ─── Quick action buttons ─────────────────────────────────────────────────────

class _QuickActions extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final AuthUser user;
  const _QuickActions({required this.vehicle, required this.user});

  @override
  Widget build(BuildContext context) {
    final canLead = user.canAccess('event_dashboard', 'create_lead');

    return Container(
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: Colors.white10)),
      ),
      child: Row(
        children: [
          if (canLead) ...[
            Expanded(
              child: _QuickButton(
                icon: Icons.favorite_rounded,
                label: 'Me interesa',
                color: const Color(0xFFE57373),
                onTap: () => showLeadFormSheet(
                  context,
                  vehicleId: vehicle.id,
                  vehicleName: vehicle.name,
                ),
              ),
            ),
            Container(width: 1, height: 36, color: Colors.white10),
          ],
          Expanded(
            child: _QuickButton(
              icon: Icons.calculate_outlined,
              label: 'Cotizar',
              color: _kGold,
              onTap: () => showQuoteFormSheet(context, vehicle: vehicle),
            ),
          ),
        ],
      ),
    );
  }
}

class _QuickButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _QuickButton({required this.icon, required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) => InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 13, color: color),
              const SizedBox(width: 4),
              Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.w600)),
            ],
          ),
        ),
      );
}
