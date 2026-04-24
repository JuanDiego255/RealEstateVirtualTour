import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:space360_flutter/src/models/event_lead_model.dart';
import 'package:space360_flutter/src/models/kiosk_vehicle_model.dart';
import 'package:space360_flutter/src/services/api_service.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kHost = 'space360cr.com';

class AgentService {
  final _api = ApiService();

  // ─── Kiosko ───────────────────────────────────────────────────────────────

  Future<Resource<List<KioskVehicleModel>>> getVehicles() async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(Uri.https(_kHost, '/api/kiosk/vehicles'), headers: headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (data['vehicles'] as List<dynamic>)
            .map((e) => KioskVehicleModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      if (res.statusCode == 401) return AppError('Sesión expirada. Iniciá sesión nuevamente.');
      if (res.statusCode == 403) return AppError('Sin permiso para acceder al Kiosko.');
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<KioskVehicleModel>> getVehicle(int id) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(Uri.https(_kHost, '/api/kiosk/vehicles/$id'), headers: headers);
      if (res.statusCode == 200) {
        return Success(KioskVehicleModel.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Leads ────────────────────────────────────────────────────────────────

  Future<Resource<bool>> captureLead({
    required String name,
    required String phone,
    String? email,
    int? vehicleId,
    String interestLevel = 'medium',
    String leadCategory = 'prospect',
    String? notes,
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/kiosk/leads'),
        headers: headers,
        body: jsonEncode({
          'name':           name,
          'phone':          phone,
          if (email != null) 'email': email,
          if (vehicleId != null) 'vehicle_id': vehicleId,
          'interest_level': interestLevel,
          'lead_category':  leadCategory,
          if (notes != null) 'notes': notes,
        }),
      );
      if (res.statusCode == 200 || res.statusCode == 201) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al guardar');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> markLeadContacted(int leadId) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/kiosk/leads/$leadId/contacted'),
        headers: headers,
      );
      if (res.statusCode == 200 || res.statusCode == 201) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Cotización ───────────────────────────────────────────────────────────

  Future<Resource<Map<String, dynamic>>> calculateQuote({
    required double vehiclePrice,
    required double downPayment,
    required int termMonths,
    required double interestRate,
    String paymentFrequency = 'monthly',
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/kiosk/quotes/calculate'),
        headers: headers,
        body: jsonEncode({
          'vehicle_price':     vehiclePrice,
          'down_payment':      downPayment,
          'term_months':       termMonths,
          'interest_rate':     interestRate,
          'payment_frequency': paymentFrequency,
        }),
      );
      if (res.statusCode == 200) {
        return Success(jsonDecode(res.body) as Map<String, dynamic>);
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al calcular');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<bool>> saveQuote({
    required String customerName,
    required String customerPhone,
    String? customerEmail,
    int? vehicleId,
    required double vehiclePrice,
    required double downPayment,
    required int termMonths,
    required double interestRate,
    required double monthlyPayment,
    required double totalAmount,
    required double totalInterest,
    String paymentFrequency = 'monthly',
  }) async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.post(
        Uri.https(_kHost, '/api/kiosk/quotes'),
        headers: headers,
        body: jsonEncode({
          'customer_name':     customerName,
          'customer_phone':    customerPhone,
          if (customerEmail != null) 'customer_email': customerEmail,
          if (vehicleId != null) 'vehicle_id': vehicleId,
          'vehicle_price':     vehiclePrice,
          'down_payment':      downPayment,
          'term_months':       termMonths,
          'interest_rate':     interestRate,
          'monthly_payment':   monthlyPayment,
          'total_amount':      totalAmount,
          'total_interest':    totalInterest,
          'payment_frequency': paymentFrequency,
        }),
      );
      if (res.statusCode == 200 || res.statusCode == 201) return Success(true);
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al guardar');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Dashboard del evento ─────────────────────────────────────────────────

  Future<Resource<DashboardStats>> getDashboardStats() async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(Uri.https(_kHost, '/api/kiosk/dashboard'), headers: headers);
      if (res.statusCode == 200) {
        return Success(DashboardStats.fromJson(jsonDecode(res.body) as Map<String, dynamic>));
      }
      if (res.statusCode == 403) return AppError('Sin permiso para ver el dashboard.');
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  Future<Resource<List<EventLeadModel>>> getDashboardLeads() async {
    try {
      final headers = await _api.authHeaders();
      final res = await http.get(Uri.https(_kHost, '/api/kiosk/dashboard/leads'), headers: headers);
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (data['leads'] as List<dynamic>)
            .map((e) => EventLeadModel.fromJson(e as Map<String, dynamic>))
            .toList();
        return Success(list);
      }
      return AppError('Error ${res.statusCode}');
    } catch (e) {
      return AppError(e.toString());
    }
  }
}
