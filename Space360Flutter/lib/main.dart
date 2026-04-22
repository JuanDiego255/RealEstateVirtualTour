import 'package:flutter/material.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:space360_flutter/src/pages/dashboard/dashboard_page.dart';
import 'package:space360_flutter/src/pages/home/home_page.dart';
import 'package:space360_flutter/src/pages/login/login_page.dart';
import 'package:space360_flutter/src/services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const Space360App());
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
        '/home': (_) => const HomePage(),
        '/login': (_) => const LoginPage(),
        '/dashboard': (_) => const DashboardPage(),
      },
    );
  }
}

/// Splash — checks for a saved token and routes to /dashboard or /home.
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
    final token = await ApiService().getToken();
    if (!mounted) return;
    Navigator.pushReplacementNamed(
      context,
      (token != null && token.isNotEmpty) ? '/dashboard' : '/home',
    );
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
