import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/kiosk_vehicle_model.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/kiosk_vehicle_detail_page.dart';
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
  String _search = '';

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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        automaticallyImplyLeading: false,
        title: const Text('Kiosko', style: TextStyle(color: _kText, fontWeight: FontWeight.bold, fontSize: 20)),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: _kSubtext),
            onPressed: _load,
          ),
        ],
      ),
      body: Column(
        children: [
          _SearchBar(onChanged: (v) => setState(() => _search = v)),
          Expanded(child: _buildBody()),
        ],
      ),
    );
  }

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
        padding: const EdgeInsets.all(16),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 0.72,
        ),
        itemCount: vehicles.length,
        itemBuilder: (context, i) => _VehicleCard(
          vehicle: vehicles[i],
          user: widget.user,
          onTap: () => Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => KioskVehicleDetailPage(vehicleId: vehicles[i].id, user: widget.user),
            ),
          ),
        ),
      ),
    );
  }
}

// ─── Search bar ───────────────────────────────────────────────────────────────

class _SearchBar extends StatelessWidget {
  final ValueChanged<String> onChanged;
  const _SearchBar({required this.onChanged});

  @override
  Widget build(BuildContext context) {
    return Padding(
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
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: _kGold, width: 1.5),
          ),
          contentPadding: const EdgeInsets.symmetric(vertical: 14),
        ),
      ),
    );
  }
}

// ─── Vehicle card ─────────────────────────────────────────────────────────────

class _VehicleCard extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final AuthUser user;
  final VoidCallback onTap;

  const _VehicleCard({required this.vehicle, required this.user, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: vehicle.isFeatured ? _kGold.withOpacity(0.4) : Colors.white.withOpacity(0.06),
          ),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _CardImage(vehicle: vehicle),
            Padding(
              padding: const EdgeInsets.all(10),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    vehicle.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.bold, height: 1.3),
                  ),
                  const SizedBox(height: 4),
                  Text(vehicle.price, style: const TextStyle(color: _kGold, fontSize: 14, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 6),
                  _Specs(vehicle: vehicle),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _CardImage extends StatelessWidget {
  final KioskVehicleModel vehicle;
  const _CardImage({required this.vehicle});

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
          if (vehicle.isFeatured)
            Positioned(
              top: 6, left: 6,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(color: _kGold, borderRadius: BorderRadius.circular(6)),
                child: const Text('Destacado', style: TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.black)),
              ),
            ),
          if (vehicle.status == 'reserved' || vehicle.status == 'negotiating')
            Positioned(
              top: 6, right: 6,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(color: const Color(0xFFE67E22), borderRadius: BorderRadius.circular(6)),
                child: Text(vehicle.status == 'reserved' ? 'Reservado' : 'En proceso',
                    style: const TextStyle(fontSize: 9, fontWeight: FontWeight.bold, color: Colors.white)),
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
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 6,
      runSpacing: 4,
      children: [
        if (vehicle.fuelType != null) _SpecChip(vehicle.fuelType!),
        if (vehicle.transmission != null) _SpecChip(vehicle.transmission!),
        if (vehicle.doors != null) _SpecChip('${vehicle.doors} p.'),
      ],
    );
  }
}

class _SpecChip extends StatelessWidget {
  final String label;
  const _SpecChip(this.label);

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.06),
          borderRadius: BorderRadius.circular(5),
        ),
        child: Text(label, style: const TextStyle(color: _kSubtext, fontSize: 10)),
      );
}
