class KioskVehicleModel {
  final int id;
  final String name;
  final String? brand;
  final String? model;
  final String? year;
  final String price;
  final double priceRaw;
  final String? currency;
  final String? fuelType;
  final String? transmission;
  final String? engineCc;
  final String? doors;
  final String? passengers;
  final String? mileageKm;
  final String? color;
  final String? condition;
  final String? drivetrain;
  final String? plate;
  final String? description;
  final String status;
  final bool isFeatured;
  final bool hasSpin;
  final String? image;
  final List<String> images;

  const KioskVehicleModel({
    required this.id,
    required this.name,
    required this.price,
    required this.priceRaw,
    required this.status,
    this.brand,
    this.model,
    this.year,
    this.currency,
    this.fuelType,
    this.transmission,
    this.engineCc,
    this.doors,
    this.passengers,
    this.mileageKm,
    this.color,
    this.condition,
    this.drivetrain,
    this.plate,
    this.description,
    this.isFeatured = false,
    this.hasSpin = false,
    this.image,
    this.images = const [],
  });

  factory KioskVehicleModel.fromJson(Map<String, dynamic> j) => KioskVehicleModel(
        id:           j['id'] as int,
        name:         j['name'] as String? ?? '',
        price:        j['price'] as String? ?? '',
        priceRaw:     (j['price_raw'] as num?)?.toDouble() ?? 0.0,
        status:       j['status'] as String? ?? 'available',
        brand:        j['brand'] as String?,
        model:        j['model'] as String?,
        year:         j['year'] as String?,
        currency:     j['currency'] as String?,
        fuelType:     j['fuel_type'] as String?,
        transmission: j['transmission'] as String?,
        engineCc:     j['engine_cc'] as String?,
        doors:        j['doors'] as String?,
        passengers:   j['passengers'] as String?,
        mileageKm:    j['mileage_km'] as String?,
        color:        j['color'] as String?,
        condition:    j['condition'] as String?,
        drivetrain:   j['drivetrain'] as String?,
        plate:        j['plate'] as String?,
        description:  j['description'] as String?,
        isFeatured:   j['is_featured'] == true,
        hasSpin:      j['has_spin'] == true,
        image:        j['image'] as String?,
        images:       (j['images'] as List<dynamic>?)?.map((e) => e as String).toList() ?? [],
      );
}
