import 'dart:async';
import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/navigation/agent_nav_page.dart';
import 'package:space360_flutter/src/navigation/bottom_nav_page.dart';
import 'package:space360_flutter/src/pages/legal/privacy_page.dart';
import 'package:space360_flutter/src/pages/legal/support_page.dart';
import 'package:space360_flutter/src/pages/login/login_page.dart';
import 'package:space360_flutter/src/services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runZonedGuarded(
    () => runApp(const Space360App()),
    (error, stack) {
      debugPrint('Uncaught error: $error\n$stack');
    },
  );
}

class Space360App extends StatelessWidget {
  const Space360App({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      builder: FToastBuilder(),
      debugShowCheckedModeBanner: false,
      title: 'Space 360 CR',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFFD4A843),
          brightness: Brightness.dark,
        ),
        useMaterial3: true,
      ),
      initialRoute: '/',
      routes: {
        '/': (_) => const _SplashRoute(),
        '/home': (_) => const BottomNavPage(),
        '/login': (_) => const LoginPage(),
        '/privacidad': (_) => const PrivacyPage(),
        '/soporte':    (_) => const SupportPage(),
      },
    );
  }
}

/// Splash — checks for a saved session and routes accordingly.
class _SplashRoute extends StatefulWidget {
  const _SplashRoute();

  @override
  State<_SplashRoute> createState() => _SplashRouteState();
}

class _SplashRouteState extends State<_SplashRoute> {
  @override
  void initState() {
    super.initState();
    _check();
  }

  Future<void> _check() async {
    final api   = ApiService();
    final token = await api.getToken();
    if (!mounted) return;

    if (token != null && token.isNotEmpty) {
      final user = await api.getSavedUser();
      if (!mounted) return;
      if (user != null) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (_) => AgentNavPage(user: user)),
        );
        return;
      }
    }

    Navigator.pushReplacementNamed(context, '/home');
  }

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFF0D0D0D),
      body: Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(Color(0xFFD4A843)),
        ),
      ),
    );
  }
}
