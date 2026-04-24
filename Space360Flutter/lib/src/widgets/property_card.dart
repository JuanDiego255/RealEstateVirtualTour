import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/property_model.dart';
import 'package:space360_flutter/src/pages/property/property_detail_page.dart';

const _kGold = Color(0xFFD4A843);
const _kBg = Color(0xFF0D0D0D);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class PropertyCard extends StatelessWidget {
  final PropertyModel property;
  final bool compact;

  const PropertyCard({super.key, required this.property, this.compact = false});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => PropertyDetailPage(propertyId: property.id)),
      ),
      child: Container(
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white.withOpacity(0.06)),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _Image(url: property.image, hasVirtualTour: property.hasVirtualTour),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    property.name,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: _kText,
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    property.price,
                    style: const TextStyle(color: _kGold, fontSize: 15, fontWeight: FontWeight.bold),
                  ),
                  if (property.location != null && !compact) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.location_on_outlined, size: 13, color: _kSubtext),
                        const SizedBox(width: 3),
                        Expanded(
                          child: Text(
                            property.location!,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(color: _kSubtext, fontSize: 12),
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _Image extends StatelessWidget {
  final String? url;
  final bool hasVirtualTour;

  const _Image({this.url, required this.hasVirtualTour});

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        AspectRatio(
          aspectRatio: 4 / 3,
          child: url != null
              ? Image.network(
                  url!,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => _placeholder(),
                )
              : _placeholder(),
        ),
        if (hasVirtualTour)
          Positioned(
            top: 8,
            left: 8,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
              decoration: BoxDecoration(
                color: _kGold,
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.view_in_ar_rounded, size: 12, color: _kBg),
                  SizedBox(width: 3),
                  Text('360°', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: _kBg)),
                ],
              ),
            ),
          ),
      ],
    );
  }

  Widget _placeholder() => Container(
        color: const Color(0xFF222222),
        child: const Center(child: Icon(Icons.image_not_supported_outlined, color: Color(0xFF444444), size: 32)),
      );
}
