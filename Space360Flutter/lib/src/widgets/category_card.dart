import 'package:flutter/material.dart';
import 'package:space360_flutter/src/models/category_model.dart';

const _kGold = Color(0xFFD4A843);
const _kSurface = Color(0xFF1A1A1A);
const _kText = Color(0xFFF0F0F0);
const _kSubtext = Color(0xFF9E9E9E);

class CategoryCard extends StatelessWidget {
  final CategoryModel category;
  final VoidCallback onTap;

  const CategoryCard({super.key, required this.category, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final imageUrl = category.coverImage ?? category.logo;
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: _kSurface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: Colors.white.withOpacity(0.07)),
        ),
        clipBehavior: Clip.antiAlias,
        child: Row(
          children: [
            if (imageUrl != null)
              SizedBox(
                width: 90,
                height: 90,
                child: Image.network(
                  imageUrl,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => _logoFallback(),
                ),
              )
            else
              SizedBox(width: 90, height: 90, child: _logoFallback()),
            const SizedBox(width: 12),
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 4),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      category.name,
                      style: const TextStyle(
                        color: _kText,
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (category.location != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.location_on_outlined, size: 12, color: _kSubtext),
                          const SizedBox(width: 3),
                          Expanded(
                            child: Text(
                              category.location!,
                              style: const TextStyle(color: _kSubtext, fontSize: 12),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ],
                    const SizedBox(height: 4),
                    Text(
                      '${category.subcategoriesCount} categoría${category.subcategoriesCount == 1 ? '' : 's'}',
                      style: const TextStyle(color: _kGold, fontSize: 12),
                    ),
                  ],
                ),
              ),
            ),
            const Padding(
              padding: EdgeInsets.only(right: 12),
              child: Icon(Icons.chevron_right_rounded, color: _kSubtext),
            ),
          ],
        ),
      ),
    );
  }

  Widget _logoFallback() => Container(
        color: const Color(0xFF222222),
        child: const Center(child: Icon(Icons.store_rounded, color: Color(0xFF444444), size: 32)),
      );
}
