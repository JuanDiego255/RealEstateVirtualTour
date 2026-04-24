import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:share_plus/share_plus.dart';
import 'package:space360_flutter/src/models/property_model.dart';
import 'package:space360_flutter/src/services/explore_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';
import 'package:space360_flutter/src/widgets/empty_state.dart';
import 'package:space360_flutter/src/widgets/property_card.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class PropertyDetailPage extends StatefulWidget {
  final int propertyId;

  const PropertyDetailPage({super.key, required this.propertyId});

  @override
  State<PropertyDetailPage> createState() => _PropertyDetailPageState();
}

class _PropertyDetailPageState extends State<PropertyDetailPage> {
  final _service = ExploreService();
  Resource<PropertyModel>? _state;
  int _imageIndex = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _state = null);
    final result = await _service.getProperty(widget.propertyId);
    if (mounted) setState(() => _state = result);
  }

  @override
  Widget build(BuildContext context) {
    final prop = (_state is Success<PropertyModel>) ? (_state as Success<PropertyModel>).data : null;

    return Scaffold(
      backgroundColor: _kBg,
      extendBodyBehindAppBar: true,
      appBar: _buildAppBar(prop),
      body: _buildBody(),
      bottomNavigationBar: prop != null ? _BottomBar(property: prop) : null,
    );
  }

  PreferredSizeWidget _buildAppBar(PropertyModel? prop) {
    return AppBar(
      backgroundColor: Colors.transparent,
      elevation: 0,
      leading: Container(
        margin: const EdgeInsets.all(8),
        decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(20)),
        child: const BackButton(color: Colors.white),
      ),
      actions: [
        if (prop?.webUrl != null)
          Container(
            margin: const EdgeInsets.only(right: 4, top: 8, bottom: 8),
            decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(20)),
            child: IconButton(
              icon: const Icon(Icons.share_rounded, color: Colors.white, size: 20),
              onPressed: () => Share.share(prop!.webUrl!),
              tooltip: 'Compartir',
            ),
          ),
        if (prop?.webUrl != null)
          Container(
            margin: const EdgeInsets.only(right: 8, top: 8, bottom: 8),
            decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(20)),
            child: IconButton(
              icon: const Icon(Icons.open_in_browser_rounded, color: Colors.white, size: 20),
              onPressed: () => _launch(prop!.webUrl!),
              tooltip: 'Ver en web',
            ),
          ),
      ],
    );
  }

  Widget _buildBody() {
    if (_state == null) {
      return const Center(
        child: CircularProgressIndicator(valueColor: AlwaysStoppedAnimation<Color>(_kGold)),
      );
    }

    return switch (_state!) {
      AppError(:final message) => ErrorState(message: message, onRetry: _load),
      Success(:final data) => _Content(
          property: data,
          imageIndex: _imageIndex,
          onPageChanged: (i) => setState(() => _imageIndex = i),
        ),
      _ => const SizedBox(),
    };
  }

  Future<void> _launch(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
  }
}

// ─── Main scrollable content ──────────────────────────────────────────────────

class _Content extends StatelessWidget {
  final PropertyModel property;
  final int imageIndex;
  final ValueChanged<int> onPageChanged;

  const _Content({
    required this.property,
    required this.imageIndex,
    required this.onPageChanged,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _Gallery(images: property.images.isNotEmpty ? property.images : [if (property.image != null) property.image!],
            currentIndex: imageIndex, onPageChanged: onPageChanged),
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _StatusRow(property: property),
                const SizedBox(height: 8),
                Text(property.name,
                    style: const TextStyle(color: _kText, fontSize: 20, fontWeight: FontWeight.bold)),
                const SizedBox(height: 6),
                Text(property.price,
                    style: const TextStyle(color: _kGold, fontSize: 22, fontWeight: FontWeight.bold)),
                if (property.location != null) ...[
                  const SizedBox(height: 8),
                  Row(children: [
                    const Icon(Icons.location_on_outlined, size: 14, color: _kSubtext),
                    const SizedBox(width: 4),
                    Expanded(child: Text(property.location!, style: const TextStyle(color: _kSubtext, fontSize: 13))),
                  ]),
                ],
                const SizedBox(height: 20),
                if (property.isVehicle)
                  _VehicleDetails(property: property)
                else
                  _RealEstateDetails(property: property),
                if (property.description != null) ...[
                  const SizedBox(height: 20),
                  _Section(title: 'Descripción', child: Text(property.description!, style: const TextStyle(color: _kSubtext, fontSize: 13, height: 1.6))),
                ],
                if (property.similar.isNotEmpty) ...[
                  const SizedBox(height: 24),
                  const Text('Similares', style: TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
                  const SizedBox(height: 12),
                  SizedBox(
                    height: 220,
                    child: ListView.separated(
                      scrollDirection: Axis.horizontal,
                      itemCount: property.similar.length,
                      separatorBuilder: (_, __) => const SizedBox(width: 12),
                      itemBuilder: (context, i) => SizedBox(
                        width: 160,
                        child: PropertyCard(property: property.similar[i], compact: true),
                      ),
                    ),
                  ),
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

// ─── Gallery ──────────────────────────────────────────────────────────────────

class _Gallery extends StatelessWidget {
  final List<String> images;
  final int currentIndex;
  final ValueChanged<int> onPageChanged;

  const _Gallery({required this.images, required this.currentIndex, required this.onPageChanged});

  @override
  Widget build(BuildContext context) {
    if (images.isEmpty) {
      return Container(
        height: 280,
        color: _kSurface,
        child: const Center(child: Icon(Icons.image_not_supported_outlined, size: 48, color: Color(0xFF444444))),
      );
    }

    return Stack(
      children: [
        SizedBox(
          height: 280,
          child: PageView.builder(
            itemCount: images.length,
            onPageChanged: onPageChanged,
            itemBuilder: (_, i) => Image.network(
              images[i],
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => Container(
                color: _kSurface,
                child: const Center(child: Icon(Icons.image_not_supported_outlined, size: 48, color: Color(0xFF444444))),
              ),
            ),
          ),
        ),
        if (images.length > 1)
          Positioned(
            bottom: 10,
            left: 0,
            right: 0,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(
                images.length,
                (i) => AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: const EdgeInsets.symmetric(horizontal: 3),
                  width: i == currentIndex ? 16 : 6,
                  height: 6,
                  decoration: BoxDecoration(
                    color: i == currentIndex ? _kGold : Colors.white54,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
            ),
          ),
      ],
    );
  }
}

// ─── Status chips ─────────────────────────────────────────────────────────────

class _StatusRow extends StatelessWidget {
  final PropertyModel property;
  const _StatusRow({required this.property});

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      children: [
        if (property.hasVirtualTour)
          _Chip(label: 'Tour 360°', color: _kGold, textColor: _kBg, icon: Icons.view_in_ar_rounded),
        if (property.isFeatured)
          _Chip(label: 'Destacado', color: const Color(0xFF2A2A2A), textColor: _kGold, icon: Icons.star_rounded),
        _Chip(
          label: property.status == 'available' ? 'Disponible' : property.status,
          color: property.status == 'available' ? const Color(0xFF1A3A1A) : const Color(0xFF3A1A1A),
          textColor: property.status == 'available' ? Colors.green[300]! : Colors.red[300]!,
        ),
      ],
    );
  }
}

class _Chip extends StatelessWidget {
  final String label;
  final Color color;
  final Color textColor;
  final IconData? icon;
  const _Chip({required this.label, required this.color, required this.textColor, this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(20)),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[Icon(icon, size: 12, color: textColor), const SizedBox(width: 4)],
          Text(label, style: TextStyle(color: textColor, fontSize: 11, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }
}

// ─── Vehicle details ──────────────────────────────────────────────────────────

class _VehicleDetails extends StatelessWidget {
  final PropertyModel property;
  const _VehicleDetails({required this.property});

  @override
  Widget build(BuildContext context) {
    return _Section(
      title: 'Ficha técnica',
      child: _Grid(rows: [
        if (property.brand != null) ('Marca', property.brand!),
        if (property.model != null) ('Modelo', property.model!),
        if (property.year != null) ('Año', property.year!),
        if (property.color != null) ('Color', property.color!),
        if (property.mileageKm != null) ('Kilometraje', '${property.mileageKm} km'),
        if (property.fuelType != null) ('Combustible', property.fuelType!),
        if (property.transmission != null) ('Transmisión', property.transmission!),
        if (property.engineCc != null) ('Motor', '${property.engineCc} cc'),
        if (property.doors != null) ('Puertas', property.doors!),
        if (property.passengers != null) ('Pasajeros', property.passengers!),
        if (property.condition != null) ('Condición', property.condition!),
        if (property.drivetrain != null) ('Tracción', property.drivetrain!),
      ]),
    );
  }
}

// ─── Real estate details ──────────────────────────────────────────────────────

class _RealEstateDetails extends StatelessWidget {
  final PropertyModel property;
  const _RealEstateDetails({required this.property});

  @override
  Widget build(BuildContext context) {
    return _Section(
      title: 'Características',
      child: _Grid(rows: [
        if (property.code != null) ('Código', property.code!),
        if (property.rooms != null) ('Habitaciones', property.rooms!),
        if (property.bathrooms != null) ('Baños', property.bathrooms!),
        if (property.garage != null) ('Parqueo', property.garage!),
        if (property.construction != null) ('Construcción', '${property.construction} m²'),
        if (property.land != null) ('Terreno', '${property.land} m²'),
        if (property.constructionYear != null) ('Año const.', property.constructionYear!),
        if (property.maintenance != null) ('Mantenimiento', property.maintenance!),
        if (property.address != null) ('Dirección', property.address!),
      ]),
    );
  }
}

// ─── Generic detail grid ──────────────────────────────────────────────────────

typedef _Row = (String, String);

class _Grid extends StatelessWidget {
  final List<_Row> rows;
  const _Grid({required this.rows});

  @override
  Widget build(BuildContext context) {
    if (rows.isEmpty) return const SizedBox();
    return Column(
      children: rows.map((r) => _DetailRow(label: r.$1, value: r.$2)).toList(),
    );
  }
}

class _DetailRow extends StatelessWidget {
  final String label;
  final String value;
  const _DetailRow({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.white.withOpacity(0.05))),
      ),
      child: Row(
        children: [
          SizedBox(
            width: 120,
            child: Text(label, style: const TextStyle(color: _kSubtext, fontSize: 13)),
          ),
          Expanded(
            child: Text(value, style: const TextStyle(color: _kText, fontSize: 13, fontWeight: FontWeight.w500)),
          ),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  final String title;
  final Widget child;
  const _Section({required this.title, required this.child});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: const TextStyle(color: _kText, fontSize: 16, fontWeight: FontWeight.bold)),
        const SizedBox(height: 10),
        Container(
          decoration: BoxDecoration(
            color: _kSurface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.white.withOpacity(0.06)),
          ),
          child: child,
        ),
      ],
    );
  }
}

// ─── Bottom action bar ────────────────────────────────────────────────────────

class _BottomBar extends StatelessWidget {
  final PropertyModel property;
  const _BottomBar({required this.property});

  @override
  Widget build(BuildContext context) {
    final hasWa = property.categoryWhatsapp != null;
    final hasPhone = property.categoryPhone != null;

    if (!hasWa && !hasPhone) return const SizedBox.shrink();

    return Container(
      padding: EdgeInsets.only(
        left: 16, right: 16, top: 12,
        bottom: MediaQuery.of(context).padding.bottom + 12,
      ),
      decoration: BoxDecoration(
        color: _kSurface,
        border: Border(top: BorderSide(color: Colors.white.withOpacity(0.08))),
      ),
      child: Row(
        children: [
          if (hasPhone) ...[
            Expanded(
              child: OutlinedButton.icon(
                style: OutlinedButton.styleFrom(
                  foregroundColor: _kGold,
                  side: const BorderSide(color: _kGold),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.phone_rounded, size: 18),
                label: const Text('Llamar'),
                onPressed: () => _launch('tel:${property.categoryPhone}'),
              ),
            ),
            if (hasWa) const SizedBox(width: 10),
          ],
          if (hasWa)
            Expanded(
              flex: hasPhone ? 1 : 2,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF25D366),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
                icon: const Icon(Icons.chat_rounded, size: 18),
                label: const Text('WhatsApp', style: TextStyle(fontWeight: FontWeight.bold)),
                onPressed: () => _launch(property.categoryWhatsapp!),
              ),
            ),
        ],
      ),
    );
  }

  Future<void> _launch(String url) async {
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
  }
}
