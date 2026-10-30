import 'package:shared_preferences/shared_preferences.dart';

import '../models/cart_item.dart';
import 'api_client.dart';

class CartSnapshot {
  final String? token;
  final List<CartItem> items;

  const CartSnapshot({this.token, required this.items});
}

class CartApi {
  static const _guestTokenKey = 'redeast_cart_token';

  final ApiClient _client = ApiClient.instance;

  Future<CartSnapshot> fetch() async {
    await _restoreGuestToken();
    final data = await _client.get('/cart');
    return _result(_data(data));
  }

  Future<CartSnapshot> add(int variantId, int quantity) async {
    final data = await _client.post(
      '/cart/items',
      data: {'product_variant_id': variantId, 'quantity': quantity},
    );
    return _result(_data(data));
  }

  Future<CartSnapshot> update(int itemId, int quantity) async {
    final data = await _client.put(
      '/cart/items/$itemId',
      data: {'quantity': quantity},
    );
    return _result(_data(data));
  }

  Future<CartSnapshot> remove(int itemId) async {
    final data = await _client.delete('/cart/items/$itemId');
    return _result(_data(data));
  }

  Future<void> mergeGuestCart() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_guestTokenKey);

    if (token == null || token.isEmpty) {
      return;
    }

    try {
      await _client.post('/cart/merge', data: {'guest_token': token});
    } finally {
      await prefs.remove(_guestTokenKey);
      _client.cartToken = null;
    }
  }

  Future<void> clear() async {
    await _client.delete('/cart');
  }

  Future<void> _restoreGuestToken() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_guestTokenKey);

    if (token != null && token.isNotEmpty) {
      _client.cartToken = token;
    }
  }

  Map<String, dynamic> _data(dynamic response) {
    final data = response is Map ? response['data'] : null;

    if (data is Map<String, dynamic>) {
      return data;
    }

    if (data is Map) {
      return data.cast<String, dynamic>();
    }

    return const {};
  }

  CartSnapshot _snapshot(Map<String, dynamic> data) {
    final token = data['token'] as String?;
    final items = <CartItem>[];

    if (data['items'] is List) {
      for (final item in data['items'] as List) {
        if (item is Map) {
          items.add(CartItem.fromJson(item.cast<String, dynamic>()));
        }
      }
    }

    return CartSnapshot(token: token, items: items);
  }

  Future<CartSnapshot> _result(Map<String, dynamic> data) async {
    final snapshot = _snapshot(data);

    if (snapshot.token != null && snapshot.token!.isNotEmpty) {
      _client.cartToken = snapshot.token;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_guestTokenKey, snapshot.token!);
    }

    return snapshot;
  }
}
