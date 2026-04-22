class AuthUser {
  final int id;
  final String name;
  final String email;

  const AuthUser({required this.id, required this.name, required this.email});

  factory AuthUser.fromJson(Map<String, dynamic> j) => AuthUser(
        id: j['id'] ?? 0,
        name: j['name'] ?? '',
        email: j['email'] ?? '',
      );
}

class AuthResponse {
  final AuthUser user;
  final String token;

  const AuthResponse({required this.user, required this.token});

  factory AuthResponse.fromJson(Map<String, dynamic> j) => AuthResponse(
        user: AuthUser.fromJson(j['user'] ?? {}),
        token: j['access_token'] ?? j['token'] ?? '',
      );
}
