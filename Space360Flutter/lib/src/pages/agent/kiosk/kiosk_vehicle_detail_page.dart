import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/kiosk_vehicle_model.dart';
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

class KioskVehicleDetailPage extends StatefulWidget {
  final int vehicleId;
  final AuthUser user;

  const KioskVehicleDetailPage({super.key, required this.vehicleId, required this.user});

  @override
  State<KioskVehicleDetailPage> createState() => _KioskVehicleDetailPageState();
}

class _KioskVehicleDetailPageState extends State<KioskVehicleDetailPage> {
  final _service = AgentService();
  Resource<KioskVehicleModel>? _state;
  int _imageIndex = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _state = null);
    final result = await _service.getVehicle(widget.vehicleId);
    if (mounted) setState(() => _state = result);
  }

  KioskVehicleModel? get _vehicle =>
      _state is Success<KioskVehicleModel> ? (_state as Success<KioskVehicleModel>).data : null;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: Container(
          margin: const EdgeInsets.all(8),
          decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(20)),
          child: const BackButton(color: Colors.white),
        ),
      ),
      body: _buildBody(),
      bottomNavigationBar: _vehicle != null ? _BottomActions(vehicle: _vehicle!, user: widget.user) : null,
    );
  }

  Widget _buildBody() {
    if (_state == null) {
      return const Center(child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation(_kGold)));
    }
    return switch (_state!) {
      AppError(:final message) => ErrorState(message: message, onRetry: _load),
      Success(:final data) => _Content(
          vehicle: data,
          imageIndex: _imageIndex,
          onPageChanged: (i) => setState(() => _imageIndex = i),
        ),
      _ => const SizedBox(),
    };
  }
}

// ─── Content ─────────────────────────────────────────────────────────────────

class _Content extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final int imageIndex;
  final ValueChanged<int> onPageChanged;

  const _Content({required this.vehicle, required this.imageIndex, required this.onPageChanged});

  @override
  Widget build(BuildContext context) {
    final images = vehicle.images.isNotEmpty ? vehicle.images : [if (vehicle.image != null) vehicle.image!];
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _Gallery(images: images, currentIndex: imageIndex, onPageChanged: onPageChanged),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _StatusRow(vehicle: vehicle),
                const SizedBox(height: 8),
                Text(vehicle.name, style: const TextStyle(color: _kText, fontSize: 22, fontWeight: FontWeight.bold)),
                const SizedBox(height: 6),
                Text(vehicle.price, style: const TextStyle(color: _kGold, fontSize: 24, fontWeight: FontWeight.bold)),
                const SizedBox(height: 20),
                _SpecsGrid(vehicle: vehicle),
                if (vehicle.description != null) ...[
                  const SizedBox(height: 20),
                  const Text('Descripción', style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Text(vehicle.description!, style: const TextStyle(color: _kSubtext, fontSize: 13, height: 1.6)),
                ],
                const SizedBox(height: 32),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ─── Gallery ─────────────────────────────────────────────────────────────────

class _Gallery extends StatelessWidget {
  final List<String> images;
  final int currentIndex;
  final ValueChanged<int> onPageChanged;

  const _Gallery({required this.images, required this.currentIndex, required this.onPageChanged});

  @override
  Widget build(BuildContext context) {
    if (images.isEmpty) {
      return Container(
        height: 280, color: _kSurface,
        child: const Center(child: Icon(Icons.directions_car_rounded, size: 64, color: Color(0xFF444444))),
      );
    }
    return Stack(children: [
      SizedBox(
        height: 280,
        child: PageView.builder(
          itemCount: images.length,
          onPageChanged: onPageChanged,
          itemBuilder: (_, i) => Image.network(images[i], fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => Container(
                color: _kSurface,
                child: const Center(child: Icon(Icons.directions_car_rounded, size: 64, color: Color(0xFF444444))),
              )),
        ),
      ),
      if (images.length > 1)
        Positioned(
          bottom: 10, left: 0, right: 0,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(images.length, (i) => AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: i == currentIndex ? 16 : 6, height: 6,
              decoration: BoxDecoration(
                color: i == currentIndex ? _kGold : Colors.white54,
                borderRadius: BorderRadius.circular(3),
              ),
            )),
          ),
        ),
    ]);
  }
}

// ─── Status row ───────────────────────────────────────────────────────────────

class _StatusRow extends StatelessWidget {
  final KioskVehicleModel vehicle;
  const _StatusRow({required this.vehicle});

  @override
  Widget build(BuildContext context) {
    return Wrap(spacing: 8, children: [
      if (vehicle.isFeatured) _Chip('Destacado', _kGold, Colors.black),
      if (vehicle.status == 'reserved') _Chip('Reservado', const Color(0xFF3A2800), const Color(0xFFE67E22)),
      if (vehicle.status == 'negotiating') _Chip('En proceso', const Color(0xFF1A2A3A), const Color(0xFF3498DB)),
    ]);
  }
}

class _Chip extends StatelessWidget {
  final String label;
  final Color bg;
  final Color fg;
  const _Chip(this.label, this.bg, this.fg);

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(20)),
        child: Text(label, style: TextStyle(color: fg, fontSize: 11, fontWeight: FontWeight.bold)),
      );
}

// ─── Specs grid ───────────────────────────────────────────────────────────────

class _SpecsGrid extends StatelessWidget {
  final KioskVehicleModel vehicle;
  const _SpecsGrid({required this.vehicle});

  @override
  Widget build(BuildContext context) {
    final specs = <(String, String, IconData)>[
      if (vehicle.fuelType != null)    ('Combustible',  vehicle.fuelType!,                 Icons.local_gas_station_outlined),
      if (vehicle.transmission != null)('Transmisión',  vehicle.transmission!,              Icons.settings_outlined),
      if (vehicle.engineCc != null)    ('Motor',        '${vehicle.engineCc} cc',            Icons.speed_outlined),
      if (vehicle.doors != null)       ('Puertas',      vehicle.doors!,                    Icons.sensor_door_outlined),
      if (vehicle.passengers != null)  ('Pasajeros',    vehicle.passengers!,                Icons.people_outlined),
      if (vehicle.mileageKm != null)   ('Kilometraje',  '${vehicle.mileageKm} km',          Icons.route_outlined),
      if (vehicle.color != null)       ('Color',        vehicle.color!,                    Icons.palette_outlined),
      if (vehicle.condition != null)   ('Condición',    vehicle.condition!,                Icons.star_outline),
      if (vehicle.drivetrain != null)  ('Tracción',     vehicle.drivetrain!,               Icons.drive_eta_outlined),
      if (vehicle.year != null)        ('Año',          vehicle.year!,                     Icons.calendar_today_outlined),
    ];

    if (specs.isEmpty) return const SizedBox();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Ficha técnica', style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 10),
        Container(
          decoration: BoxDecoration(
            color: _kSurface,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: Colors.white.withOpacity(0.06)),
          ),
          child: Column(
            children: specs.map((s) => _SpecRow(label: s.$1, value: s.$2, icon: s.$3)).toList(),
          ),
        ),
      ],
    );
  }
}

class _SpecRow extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  const _SpecRow({required this.label, required this.value, required this.icon});

  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(border: Border(bottom: BorderSide(color: Colors.white.withOpacity(0.04)))),
        child: Row(children: [
          Icon(icon, size: 16, color: _kGold),
          const SizedBox(width: 10),
          SizedBox(width: 100, child: Text(label, style: const TextStyle(color: _kSubtext, fontSize: 13))),
          Expanded(child: Text(value, style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.w600))),
        ]),
      );
}

// ─── Bottom action bar ────────────────────────────────────────────────────────

class _BottomActions extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final AuthUser user;
  const _BottomActions({required this.vehicle, required this.user});

  @override
  Widget build(BuildContext context) {
    final canLead  = user.canAccess('event_dashboard', 'create_lead');
    final canQuote = user.canAccess('kiosk', 'view');

    return Container(
      padding: EdgeInsets.only(
        left: 16, right: 16, top: 12,
        bottom: MediaQuery.of(context).padding.bottom + 12,
      ),
      decoration: BoxDecoration(
        color: _kSurface,
        border: Border(top: BorderSide(color: Colors.white.withOpacity(0.08))),
      ),
      child: Row(children: [
        if (canLead) ...[
          Expanded(
            child: OutlinedButton.icon(
              style: OutlinedButton.styleFrom(
                foregroundColor: _kGold,
                side: const BorderSide(color: _kGold),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.favorite_border_rounded, size: 18),
              label: const Text('Me interesa'),
              onPressed: () => showLeadFormSheet(context, vehicleId: vehicle.id, vehicleName: vehicle.name),
            ),
          ),
          if (canQuote) const SizedBox(width: 10),
        ],
        if (canQuote)
          Expanded(
            child: ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: _kGold,
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              icon: const Icon(Icons.calculate_outlined, size: 18),
              label: const Text('Cotizar', style: TextStyle(fontWeight: FontWeight.bold)),
              onPressed: () => showQuoteFormSheet(context, vehicle: vehicle),
            ),
          ),
      ]),
    );
  }
}
