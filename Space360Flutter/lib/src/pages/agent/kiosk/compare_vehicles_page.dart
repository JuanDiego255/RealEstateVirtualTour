import 'dart:math';
import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/models/kiosk_vehicle_model.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/lead_form_sheet.dart';
import 'package:space360_flutter/src/pages/agent/kiosk/quote_form_sheet.dart';

const _kGold    = Color(0xFFD4A843);
const _kBg      = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText    = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

// Row heights (must match between label column and vehicle columns)
const _kHeaderH = 190.0;
const _kRowH    = 50.0;
const _kActionsH = 92.0;

// Spec rows: (label, getter from KioskVehicleModel)
const _kSpecs = [
  'Precio',
  'Combustible',
  'Transmisión',
  'Motor',
  'Puertas',
  'Pasajeros',
  'Kilometraje',
  'Tracción',
  'Color',
  'Condición',
];

class CompareVehiclesPage extends StatelessWidget {
  final List<KioskVehicleModel> vehicles;
  final AuthUser user;

  const CompareVehiclesPage({super.key, required this.vehicles, required this.user});

  String _specValue(KioskVehicleModel v, String label) => switch (label) {
    'Precio'       => v.price,
    'Combustible'  => v.fuelType ?? '—',
    'Transmisión'  => v.transmission ?? '—',
    'Motor'        => v.engineCc != null ? '${v.engineCc} cc' : '—',
    'Puertas'      => v.doors ?? '—',
    'Pasajeros'    => v.passengers ?? '—',
    'Kilometraje'  => v.mileageKm != null ? '${v.mileageKm} km' : '—',
    'Tracción'     => v.drivetrain ?? '—',
    'Color'        => v.color ?? '—',
    'Condición'    => v.condition ?? '—',
    _              => '—',
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _kBg,
      appBar: AppBar(
        backgroundColor: _kBg,
        elevation: 0,
        iconTheme: const IconThemeData(color: _kText),
        title: Text(
          'Comparar ${vehicles.length} vehículos',
          style: const TextStyle(color: _kText, fontWeight: FontWeight.bold, fontSize: 17),
        ),
      ),
      body: LayoutBuilder(
        builder: (context, constraints) {
          final labelW  = 90.0;
          final colW    = vehicles.length == 2
              ? (constraints.maxWidth - labelW) / 2
              : min(155.0, (constraints.maxWidth - labelW) / 1.8);

          final totalW  = labelW + vehicles.length * colW;
          final needsHScroll = totalW > constraints.maxWidth;

          Widget table = SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            physics: needsHScroll
                ? const BouncingScrollPhysics()
                : const NeverScrollableScrollPhysics(),
            child: SizedBox(
              width: max(constraints.maxWidth, totalW),
              child: SingleChildScrollView(
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // ── Label column ─────────────────────────────────
                    _LabelsColumn(labelW: labelW),
                    // ── Vehicle columns ───────────────────────────────
                    ...vehicles.map((v) => _VehicleColumn(
                      vehicle: v,
                      colW: colW,
                      user: user,
                      getSpec: (label) => _specValue(v, label),
                    )),
                  ],
                ),
              ),
            ),
          );

          if (needsHScroll) {
            return Column(
              children: [
                _ScrollHint(),
                Expanded(child: table),
              ],
            );
          }
          return table;
        },
      ),
    );
  }
}

// ─── Scroll hint banner ───────────────────────────────────────────────────────

class _ScrollHint extends StatelessWidget {
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        color: _kSurface,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: const [
            Icon(Icons.swipe_rounded, color: _kSubtext, size: 14),
            SizedBox(width: 6),
            Text('Deslizá horizontalmente para ver más',
                style: TextStyle(color: _kSubtext, fontSize: 11)),
          ],
        ),
      );
}

// ─── Label column ─────────────────────────────────────────────────────────────

class _LabelsColumn extends StatelessWidget {
  final double labelW;
  const _LabelsColumn({required this.labelW});

  @override
  Widget build(BuildContext context) => SizedBox(
        width: labelW,
        child: Column(
          children: [
            // header placeholder
            SizedBox(height: _kHeaderH),
            const Divider(height: 1, color: Colors.white10),
            ..._kSpecs.map((label) => _LabelCell(label: label)),
            SizedBox(height: _kActionsH),
          ],
        ),
      );
}

class _LabelCell extends StatelessWidget {
  final String label;
  const _LabelCell({required this.label});

  @override
  Widget build(BuildContext context) => Container(
        height: _kRowH,
        alignment: Alignment.centerLeft,
        padding: const EdgeInsets.symmetric(horizontal: 10),
        decoration: const BoxDecoration(
          border: Border(bottom: BorderSide(color: Colors.white10)),
        ),
        child: Text(label,
            style: const TextStyle(color: _kSubtext, fontSize: 12, fontWeight: FontWeight.w500)),
      );
}

// ─── Vehicle column ───────────────────────────────────────────────────────────

class _VehicleColumn extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final double colW;
  final AuthUser user;
  final String Function(String) getSpec;

  const _VehicleColumn({
    required this.vehicle,
    required this.colW,
    required this.user,
    required this.getSpec,
  });

  @override
  Widget build(BuildContext context) => SizedBox(
        width: colW,
        child: Column(
          children: [
            // ── Header ─────────────────────────────────────────────
            _VehicleHeader(vehicle: vehicle, colW: colW),
            const Divider(height: 1, color: Colors.white10),
            // ── Spec rows ──────────────────────────────────────────
            ..._kSpecs.map((label) => _ValueCell(
                  value: getSpec(label),
                  highlight: label == 'Precio',
                )),
            // ── Action buttons ─────────────────────────────────────
            _VehicleActions(vehicle: vehicle, user: user),
          ],
        ),
      );
}

class _VehicleHeader extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final double colW;
  const _VehicleHeader({required this.vehicle, required this.colW});

  @override
  Widget build(BuildContext context) => SizedBox(
        height: _kHeaderH,
        width: colW,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            SizedBox(
              height: 110,
              width: double.infinity,
              child: vehicle.image != null
                  ? Image.network(vehicle.image!, fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => _imgPlaceholder())
                  : _imgPlaceholder(),
            ),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(8, 6, 8, 4),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(vehicle.name,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: _kText, fontSize: 12, fontWeight: FontWeight.bold, height: 1.3)),
                    const SizedBox(height: 2),
                    if (vehicle.status == 'reserved' || vehicle.status == 'negotiating')
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE67E22).withOpacity(0.2),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          vehicle.status == 'reserved' ? 'Reservado' : 'En proceso',
                          style: const TextStyle(color: Color(0xFFE67E22), fontSize: 9, fontWeight: FontWeight.bold),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ],
        ),
      );

  Widget _imgPlaceholder() => Container(
        color: const Color(0xFF222222),
        child: const Center(child: Icon(Icons.directions_car_rounded, color: Color(0xFF444444), size: 28)),
      );
}

class _ValueCell extends StatelessWidget {
  final String value;
  final bool highlight;
  const _ValueCell({required this.value, this.highlight = false});

  @override
  Widget build(BuildContext context) => Container(
        height: _kRowH,
        alignment: Alignment.centerLeft,
        padding: const EdgeInsets.symmetric(horizontal: 10),
        decoration: const BoxDecoration(
          border: Border(
            bottom: BorderSide(color: Colors.white10),
            left: BorderSide(color: Colors.white10),
          ),
        ),
        child: Text(
          value,
          maxLines: 2,
          overflow: TextOverflow.ellipsis,
          style: TextStyle(
            color: highlight ? _kGold : _kText,
            fontSize: highlight ? 13 : 12,
            fontWeight: highlight ? FontWeight.bold : FontWeight.normal,
          ),
        ),
      );
}

class _VehicleActions extends StatelessWidget {
  final KioskVehicleModel vehicle;
  final AuthUser user;
  const _VehicleActions({required this.vehicle, required this.user});

  @override
  Widget build(BuildContext context) => Container(
        height: _kActionsH,
        decoration: const BoxDecoration(
          border: Border(left: BorderSide(color: Colors.white10)),
        ),
        padding: const EdgeInsets.fromLTRB(8, 8, 8, 8),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (user.canAccess('event_dashboard', 'create_lead'))
              _ActionButton(
                icon: Icons.favorite_rounded,
                label: 'Me interesa',
                color: const Color(0xFFC62828),
                onTap: () => showLeadFormSheet(
                  context,
                  vehicleId: vehicle.id,
                  vehicleName: vehicle.name,
                ),
              ),
            if (user.canAccess('event_dashboard', 'create_lead')) const SizedBox(height: 6),
            _ActionButton(
              icon: Icons.calculate_outlined,
              label: 'Cotizar',
              color: _kGold,
              onTap: () => showQuoteFormSheet(context, vehicle: vehicle),
            ),
          ],
        ),
      );
}

class _ActionButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;
  final VoidCallback onTap;
  const _ActionButton({required this.icon, required this.label, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) => GestureDetector(
        onTap: onTap,
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 6),
          decoration: BoxDecoration(
            color: color.withOpacity(0.12),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: color.withOpacity(0.4)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 12, color: color),
              const SizedBox(width: 4),
              Text(label, style: TextStyle(color: color, fontSize: 11, fontWeight: FontWeight.bold)),
            ],
          ),
        ),
      );
}
