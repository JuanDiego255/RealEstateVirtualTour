class SubcategoryModel {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final String? image;
  final int propertiesCount;

  const SubcategoryModel({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.image,
    this.propertiesCount = 0,
  });

  factory SubcategoryModel.fromJson(Map<String, dynamic> j) => SubcategoryModel(
        id: j['id'] as int,
        name: j['name'] as String,
        slug: j['slug'] as String,
        description: j['description'] as String?,
        image: j['image'] as String?,
        propertiesCount: (j['properties_count'] as num?)?.toInt() ?? 0,
      );
}
