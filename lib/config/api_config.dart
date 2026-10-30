import 'package:flutter/foundation.dart';

abstract final class ApiConfig {
  static const _override = String.fromEnvironment('API_BASE_URL');

  static String get baseUrl {
    if (_override.isNotEmpty) {
      return _override;
    }

    if (kIsWeb) {
      return 'http://127.0.0.1:8000/api/v1';
    }

    return 'http://10.0.2.2:8000/api/v1';
  }
}
