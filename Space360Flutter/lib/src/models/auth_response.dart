class AuthUser {
  final int id;
  final String name;
  final String email;
  final String role;
  final int? companyId;
  final Map<String, dynamic>? permissions;

  const AuthUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.companyId,
    this.permissions,
  });

  bool get isSuperAdmin => role == 'super_admin';
  bool get isCompanyAdmin => role == 'company_admin';
  bool get isAgent => role == 'agent';
  bool get isAdmin => isSuperAdmin || isCompanyAdmin;

  bool canAccess(String module, [String action = 'view']) {
    if (isAdmin) return true;
    final mod = permissions?[module];
    if (mod == null) return false;
    return (mod as Map<String, dynamic>)[action] == true;
  }

  factory AuthUser.fromJson(Map<String, dynamic> j) => AuthUser(
        id: j['id'] as int? ?? 0,
        name: j['name'] as String? ?? '',
        email: j['email'] as String? ?? '',
        role: j['role'] as String? ?? 'agent',
        companyId: j['company_id'] as int?,
        permissions: j['permissions'] as Map<String, dynamic>?,
      );
}

class AuthResponse {
  final AuthUser user;
  final String token;

  const AuthResponse({required this.user, required this.token});

  factory AuthResponse.fromJson(Map<String, dynamic> j) => AuthResponse(
        user: AuthUser.fromJson(j['user'] as Map<String, dynamic>? ?? {}),
        token: j['access_token'] as String? ?? j['token'] as String? ?? '',
      );
}
