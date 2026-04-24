class PropertyModel {
  final int id;
  final String name;
  final String propertyType;
  final String? description;
  final String price;
  final String? currency;
  final String? location;
  final String? address;
  final String? image;
  final List<String> images;
  final bool hasVirtualTour;
  final String status;
  final bool isFeatured;
  final int viewsCount;
  final String? webUrl;

  // Vehicle fields
  final String? brand;
  final String? model;
  final String? year;
  final String? color;
  final String? mileageKm;
  final String? fuelType;
  final String? transmission;
  final String? engineCc;
  final String? doors;
  final String? passengers;
  final String? condition;
  final String? drivetrain;

  // Real estate fields
  final String? code;
  final String? rooms;
  final String? bathrooms;
  final String? garage;
  final String? construction;
  final String? land;
  final String? constructionYear;
  final String? maintenance;

  // Relations
  final Map<String, dynamic>? category;
  final Map<String, dynamic>? subcategory;
  final List<PropertyModel> similar;

  bool get isVehicle => propertyType == 'vehicle';

  const PropertyModel({
    required this.id,
    required this.name,
    required this.propertyType,
    required this.price,
    required this.status,
    this.description,
    this.currency,
    this.location,
    this.address,
    this.image,
    this.images = const [],
    this.hasVirtualTour = false,
    this.isFeatured = false,
    this.viewsCount = 0,
    this.webUrl,
    this.brand,
    this.model,
    this.year,
    this.color,
    this.mileageKm,
    this.fuelType,
    this.transmission,
    this.engineCc,
    this.doors,
    this.passengers,
    this.condition,
    this.drivetrain,
    this.code,
    this.rooms,
    this.bathrooms,
    this.garage,
    this.construction,
    this.land,
    this.constructionYear,
    this.maintenance,
    this.category,
    this.subcategory,
    this.similar = const [],
  });

  factory PropertyModel.fromJson(Map<String, dynamic> j) => PropertyModel(
        id: j['id'] as int,
        name: j['name'] as String,
        propertyType: j['property_type'] as String? ?? 'other',
        description: j['description'] as String?,
        price: j['price'] as String? ?? '',
        currency: j['currency'] as String?,
        location: j['location'] as String?,
        address: j['address'] as String?,
        image: j['image'] as String?,
        images: (j['images'] as List<dynamic>?)?.map((e) => e as String).toList() ?? [],
        hasVirtualTour: j['has_virtual_tour'] == true,
        status: j['status'] as String? ?? 'available',
        isFeatured: j['is_featured'] == true,
        viewsCount: (j['views_count'] as num?)?.toInt() ?? 0,
        webUrl: j['web_url'] as String?,
        brand: j['brand'] as String?,
        model: j['model'] as String?,
        year: j['year'] as String?,
        color: j['color'] as String?,
        mileageKm: j['mileage_km'] as String?,
        fuelType: j['fuel_type'] as String?,
        transmission: j['transmission'] as String?,
        engineCc: j['engine_cc'] as String?,
        doors: j['doors'] as String?,
        passengers: j['passengers'] as String?,
        condition: j['condition'] as String?,
        drivetrain: j['drivetrain'] as String?,
        code: j['code'] as String?,
        rooms: j['rooms'] as String?,
        bathrooms: j['bathrooms'] as String?,
        garage: j['garage'] as String?,
        construction: j['construction'] as String?,
        land: j['land'] as String?,
        constructionYear: j['construction_year'] as String?,
        maintenance: j['maintenance'] as String?,
        category: j['category'] as Map<String, dynamic>?,
        subcategory: j['subcategory'] as Map<String, dynamic>?,
        similar: (j['similar'] as List<dynamic>?)
                ?.map((e) => PropertyModel.fromJson(e as Map<String, dynamic>))
                .toList() ??
            [],
      );

  String? get categoryWhatsapp {
    final wa = category?['contact_whatsapp'] as String?;
    if (wa == null || wa.isEmpty) return null;
    final num = wa.replaceAll(RegExp(r'[^\d+]'), '');
    return 'https://wa.me/$num';
  }

  String? get categoryPhone => category?['contact_phone'] as String?;
}
