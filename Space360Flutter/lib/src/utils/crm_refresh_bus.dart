import 'package:flutter/foundation.dart';

/// Singleton event bus that coordinates data refresh across all CRM tabs.
///
/// Usage:
///   signal() — something mutated (status change, create, delete); all CRM
///              tabs that listen will silently re-fetch their data.
class CrmRefreshBus extends ChangeNotifier {
  CrmRefreshBus._();
  static final instance = CrmRefreshBus._();

  void signal() => notifyListeners();
}
