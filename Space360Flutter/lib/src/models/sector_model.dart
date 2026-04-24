import 'category_model.dart';

class SectorModel {
  final int id;
  final String name;
  final String slug;
  final String? icon;
  final String? description;
  final String? image;
  final int categoriesCount;
  final List<CategoryModel> categories;

  const SectorModel({
    required this.id,
    required this.name,
    required this.slug,
    this.icon,
    this.description,
    this.image,
    this.categoriesCount = 0,
    this.categories = const [],
  });

  factory SectorModel.fromJson(Map<String, dynamic> j) => SectorModel(
        id: j['id'] as int,
        name: j['name'] as String,
        slug: j['slug'] as String,
        icon: j['icon'] as String?,
        description: j['description'] as String?,
        image: j['image'] as String?,
        categoriesCount: (j['categories_count'] as num?)?.toInt() ?? 0,
        categories: (j['categories'] as List<dynamic>?)
                ?.map((e) => CategoryModel.fromJson(e as Map<String, dynamic>))
                .toList() ??
            [],
      );
}
