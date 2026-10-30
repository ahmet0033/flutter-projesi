import 'dart:convert';

import '../config/api_config.dart';
import '../http/http_client.dart';
import '../models/category.dart';
import '../models/product.dart';

class ApiService {
  static Future<List<Product>> fetchProducts({
    String? category,
    String? search,
    int perPage = 50,
  }) async {
    final query = <String, String>{'per_page': '$perPage'};

    if (category != null && category.isNotEmpty) {
      query['category'] = category;
    }

    if (search != null && search.trim().isNotEmpty) {
      query['search'] = search.trim();
    }

    final uri = Uri.parse(
      '${ApiConfig.baseUrl}/products',
    ).replace(queryParameters: query);
    final body = await HttpClientService.getString(uri.toString());
    final json = jsonDecode(body) as Map<String, dynamic>;
    final data = json['data'] as List<dynamic>;

    return data
        .map((item) => Product.fromJson(item as Map<String, dynamic>))
        .toList();
  }

  static Future<Product> fetchProduct(String slug) async {
    final body = await HttpClientService.getString(
      '${ApiConfig.baseUrl}/products/$slug',
    );
    final json = jsonDecode(body) as Map<String, dynamic>;

    return Product.fromJson(json['data'] as Map<String, dynamic>);
  }

  static Future<List<Category>> fetchCategories() async {
    final body = await HttpClientService.getString(
      '${ApiConfig.baseUrl}/categories',
    );
    final json = jsonDecode(body) as Map<String, dynamic>;
    final data = json['data'] as List<dynamic>;

    return data
        .map((item) => Category.fromJson(item as Map<String, dynamic>))
        .toList();
  }
}
