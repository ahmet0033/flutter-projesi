import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/user.dart';
import 'api_client.dart';

class AuthController extends ChangeNotifier {
  static const _tokenKey = 'redeast_auth_token';

  final FlutterSecureStorage _storage = const FlutterSecureStorage();

  User? _user;
  bool _loading = false;
  bool _initialized = false;
  String? _error;

  User? get user => _user;

  bool get isAuthenticated => _user != null;

  bool get loading => _loading;

  bool get initialized => _initialized;

  String? get error => _error;

  Future<void> initialize() async {
    final token = await _readToken();

    if (token != null && token.isNotEmpty) {
      ApiClient.instance.authToken = token;

      try {
        final response = await ApiClient.instance.get('/auth/me');
        _user = User.fromJson(_data(response));
      } catch (_) {
        ApiClient.instance.authToken = null;
        await _deleteToken();
      }
    }

    _initialized = true;
    notifyListeners();
  }

  Future<bool> login(String email, String password) async {
    return _authenticate('/auth/login', {
      'email': email,
      'password': password,
      'device_name': 'redeast-mobile',
    });
  }

  Future<bool> register(String name, String email, String password) async {
    return _authenticate('/auth/register', {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': password,
      'device_name': 'redeast-mobile',
    });
  }

  Future<void> logout() async {
    try {
      await ApiClient.instance.post('/auth/logout');
    } catch (_) {
      // Best effort; clear local state regardless.
    }

    _user = null;
    _error = null;
    ApiClient.instance.authToken = null;
    await _deleteToken();
    notifyListeners();
  }

  Future<bool> _authenticate(String path, Map<String, dynamic> payload) async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await ApiClient.instance.post(path, data: payload);
      final data = _data(response);
      final token = data['token'] as String;
      final user = data['user'];

      await _writeToken(token);
      ApiClient.instance.authToken = token;
      _user = User.fromJson(user as Map<String, dynamic>);
      return true;
    } catch (error) {
      _error = error.toString();
      return false;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<String?> _readToken() async {
    try {
      return await _storage.read(key: _tokenKey);
    } catch (_) {
      return null;
    }
  }

  Future<void> _writeToken(String token) async {
    try {
      await _storage.write(key: _tokenKey, value: token);
    } catch (_) {
      // Storage may be unavailable in tests; the in-memory token still works.
    }
  }

  Future<void> _deleteToken() async {
    try {
      await _storage.delete(key: _tokenKey);
    } catch (_) {
      // Ignore storage errors.
    }
  }

  Map<String, dynamic> _data(dynamic response) {
    final data = response is Map ? response['data'] : null;

    if (data is Map<String, dynamic>) {
      return data;
    }

    if (response is Map<String, dynamic>) {
      return response;
    }

    return const {};
  }
}
