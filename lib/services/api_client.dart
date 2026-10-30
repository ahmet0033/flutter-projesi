import 'package:dio/dio.dart';

import '../config/api_config.dart';

class ApiException implements Exception {
  const ApiException(this.message);

  final String message;

  @override
  String toString() => message;
}

class ApiClient {
  ApiClient._() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConfig.baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 20),
        headers: const {'Accept': 'application/json'},
      ),
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          if (authToken != null && authToken!.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $authToken';
          }
          if (cartToken != null && cartToken!.isNotEmpty) {
            options.headers['X-Cart-Token'] = cartToken;
          }
          handler.next(options);
        },
      ),
    );
  }

  static final ApiClient instance = ApiClient._();

  late final Dio _dio;
  String? authToken;
  String? cartToken;

  Future<dynamic> get(String path, {Map<String, dynamic>? query}) async {
    return _run(() => _dio.get<dynamic>(path, queryParameters: query));
  }

  Future<dynamic> post(String path, {Object? data}) async {
    return _run(() => _dio.post<dynamic>(path, data: data));
  }

  Future<dynamic> put(String path, {Object? data}) async {
    return _run(() => _dio.put<dynamic>(path, data: data));
  }

  Future<dynamic> delete(String path, {Object? data}) async {
    return _run(() => _dio.delete<dynamic>(path, data: data));
  }

  Future<dynamic> _run(Future<Response<dynamic>> Function() request) async {
    try {
      final response = await request();
      return response.data;
    } on DioException catch (error) {
      throw ApiException(_messageFor(error));
    }
  }

  String _messageFor(DioException error) {
    final data = error.response?.data;

    if (data is Map) {
      final errors = data['errors'];
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) {
          return first.first.toString();
        }
      }

      if (data['message'] != null) {
        return data['message'].toString();
      }
    }

    if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout) {
      return 'The server took too long to respond.';
    }

    if (error.response == null) {
      return 'Could not reach the server.';
    }

    return 'Request failed (${error.response?.statusCode}).';
  }
}
