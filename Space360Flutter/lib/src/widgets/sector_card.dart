import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/sector_model.dart';

const _kGold = Color(0xFFD4A843);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class SectorCard extends StatelessWidget {
  final SectorModel sector;
  final VoidCallback onTap;

  const SectorCard({super.key, required this.sector, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.white.withOpacity(0.07)),
        ),
        clipBehavior: Clip.antiAlias,
        child: Stack(
          children: [
            if (sector.image != null)
              Positioned.fill(
                child: Image.network(
                  sector.image!,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => const SizedBox(),
                ),
              ),
            Positioned.fill(
              child: Container(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [Colors.transparent, Colors.black.withOpacity(0.82)],
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  if (sector.icon != null)
                    Text(sector.icon!, style: const TextStyle(fontSize: 28)),
                  const SizedBox(height: 4),
                  Text(
                    sector.name,
                    style: const TextStyle(
                      color: _kText,
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    '${sector.categoriesCount} sucursal${sector.categoriesCount == 1 ? '' : 'es'}',
                    style: const TextStyle(color: _kGold, fontSize: 12),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
