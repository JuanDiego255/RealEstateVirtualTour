import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:space360_flutter/src/models/auth_response.dart';
import 'package:space360_flutter/src/utils/resource.dart';

const _kHost = 'space360cr.com';

class ApiService {
  static const _tokenKey  = 's360_token';
  static const _userKey   = 's360_user';

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

  Future<void> saveSession(AuthResponse auth) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, auth.token);
    await prefs.setString(_userKey, jsonEncode({
      'id':          auth.user.id,
      'name':        auth.user.name,
      'email':       auth.user.email,
      'role':        auth.user.role,
      'company_id':  auth.user.companyId,
      'permissions': auth.user.permissions,
    }));
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_tokenKey);
  }

  Future<AuthUser?> getSavedUser() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_userKey);
    if (raw == null) return null;
    try {
      return AuthUser.fromJson(jsonDecode(raw) as Map<String, dynamic>);
    } catch (_) {
      return null;
    }
  }

  Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);
  }

  // ─── Authenticated headers ────────────────────────────────────────────────

  Future<Map<String, String>> authHeaders() async {
    final token = await getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null && token.isNotEmpty) 'Authorization': 'Bearer $token',
    };
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
