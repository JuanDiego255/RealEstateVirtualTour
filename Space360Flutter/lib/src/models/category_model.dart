import 'subcategory_model.dart';

class CategoryModel {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final String? location;
  final String? address;
  final String? logo;
  final String? coverImage;
  final String? contactName;
  final String? contactPhone;
  final String? contactWhatsapp;
  final String? contactEmail;
  final String? website;
  final int subcategoriesCount;
  final Map<String, dynamic>? sector;
  final List<SubcategoryModel> subcategories;

  const CategoryModel({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.location,
    this.address,
    this.logo,
    this.coverImage,
    this.contactName,
    this.contactPhone,
    this.contactWhatsapp,
    this.contactEmail,
    this.website,
    this.subcategoriesCount = 0,
    this.sector,
    this.subcategories = const [],
  });

  factory CategoryModel.fromJson(Map<String, dynamic> j) => CategoryModel(
        id: j['id'] as int,
        name: j['name'] as String,
        slug: j['slug'] as String,
        description: j['description'] as String?,
        location: j['location'] as String?,
        address: j['address'] as String?,
        logo: j['logo'] as String?,
        coverImage: j['cover_image'] as String?,
        contactName: j['contact_name'] as String?,
        contactPhone: j['contact_phone'] as String?,
        contactWhatsapp: j['contact_whatsapp'] as String?,
        contactEmail: j['contact_email'] as String?,
        website: j['website'] as String?,
        subcategoriesCount: (j['subcategories_count'] as num?)?.toInt() ?? 0,
        sector: j['sector'] as Map<String, dynamic>?,
        subcategories: (j['subcategories'] as List<dynamic>?)
                ?.map((e) => SubcategoryModel.fromJson(e as Map<String, dynamic>))
                .toList() ??
            [],
      );

  String? get whatsappUrl {
    if (contactWhatsapp == null || contactWhatsapp!.isEmpty) return null;
    final num = contactWhatsapp!.replaceAll(RegExp(r'[^\d+]'), '');
    return 'https://wa.me/$num';
  }
}
