import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kHost = 'space360cr.com';

class ApiService {
  static const _tokenKey = 's360_token';

  // ─── Auth ─────────────────────────────────────────────────────────────────

  Future<Resource<AuthResponse>> login(String email, String password) async {
    try {
      final url = Uri.https(_kHost, '/api/login');
      final res = await http.post(
        url,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({'email': email, 'password': password}),
      );
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      if (res.statusCode == 200 || res.statusCode == 201) {
        return Success(AuthResponse.fromJson(data));
      }
      return AppError(data['message']?.toString() ?? 'Credenciales inválidas');
    } catch (e) {
      return AppError(e.toString());
    }
  }

  // ─── Session ──────────────────────────────────────────────────────────────

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  // ─── Contact / Lead capture ───────────────────────────────────────────────

  Future<Resource<bool>> sendContactLead({
    required String name,
    required String phone,
    required String email,
    required String message,
    required String tourType,
  }) async {
    try {
      final url = Uri.https(_kHost, '/api/leads');
      final res = await http.post(
        url,
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: jsonEncode({
          'name': name,
          'phone': phone,
          'email': email,
          'message': message,
          'tour_type': tourType,
        }),
      );
      if (res.statusCode == 200 || res.statusCode == 201) {
        return Success(true);
      }
      final data = jsonDecode(res.body) as Map<String, dynamic>;
      return AppError(data['message']?.toString() ?? 'Error al enviar');
    } catch (e) {
      return AppError(e.toString());
    }
  }
}
