import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:space360_flutter/src/models/category_model.dart';
import 'package:space360_flutter/src/models/property_model.dart';
import 'package:space360_flutter/src/models/sector_model.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kHost = 'space360cr.com';
const _kHeaders = {'Accept': 'application/json'};

class ExploreService {
  Future<Resource<List<SectorModel>>> getSectors() async {
    try {
      final res = await http.get(Uri.https(_kHost, '/api/sectors'), headers: _kHeaders);
      if (res.statusCode == 200) {
        final list = jsonDecode(res.body) as List<dynamic>;
        return Success(list.map((e) => SectorModel.fromJson(e as Map<String, dynamic>)).toList());
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<SectorModel>> getSector(String slug) async {
    try {
      final res = await http.get(Uri.https(_kHost, '/api/sectors/$slug'), headers: _kHeaders);
      if (res.statusCode == 200) {
        return Success(SectorModel.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<CategoryModel>> getCategory(String slug) async {
    try {
      final res = await http.get(Uri.https(_kHost, '/api/categories/$slug'), headers: _kHeaders);
      if (res.statusCode == 200) {
        return Success(CategoryModel.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<Map<String, dynamic>>> getPublications(
    String categorySlug,
    String subcategorySlug,
  ) async {
    try {
      final res = await http.get(
        Uri.https(_kHost, '/api/categories/$categorySlug/subcategories/$subcategorySlug'),
        headers: _kHeaders,
      );
      if (res.statusCode == 200) {
        return Success(jsonDecode(res.body) as Map<String, dynamic>);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<PropertyModel>> getProperty(int id) async {
    try {
      final res = await http.get(Uri.https(_kHost, '/api/properties/$id'), headers: _kHeaders);
      if (res.statusCode == 200) {
        return Success(PropertyModel.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<List<PropertyModel>>> search({
    String? q,
    int? sectorId,
    int? categoryId,
    String? propertyType,
  }) async {
    try {
      final params = <String, String>{};
      if (q != null && q.isNotEmpty) params['q'] = q;
      if (sectorId != null) params['sector_id'] = sectorId.toString();
      if (categoryId != null) params['category_id'] = categoryId.toString();
      if (propertyType != null) params['property_type'] = propertyType;

      final res = await http.get(
        Uri.https(_kHost, '/api/search', params),
        headers: _kHeaders,
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final results = (data['results'] as List<dynamic>)
            .map((e) => PropertyModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(results);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<List<PropertyModel>>> getFeatured() async {
    try {
      final res = await http.get(Uri.https(_kHost, '/api/featured'), headers: _kHeaders);
      if (res.statusCode == 200) {
        final list = jsonDecode(res.body) as List<dynamic>;
        return Success(list.map((e) => PropertyModel.fromJson(e as Map<String, dynamic>)).toList());
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }
}
