import 'package:flutter/foundation.dart';

import '../services/cart_api.dart';
import 'cart_item.dart';
import 'product.dart';

class CartModel extends ChangeNotifier {
  CartModel({CartApi? api}) : _api = api ?? CartApi();

  final CartApi _api;
  final List<CartItem> _items = [];
  bool _loading = false;
  String? _error;

  List<CartItem> get items => List.unmodifiable(_items);

  bool get loading => _loading;

  String? get error => _error;

  int get totalItems => _items.fold(0, (sum, item) => sum + item.quantity);

  double get totalPrice => _items.fold(
    0.0,
    (sum, item) => sum + item.product.numericPrice * item.quantity,
  );

  String get totalPriceFormatted {
    if (_items.isEmpty) return '\$0.00';
    return '\$${totalPrice.toStringAsFixed(2)}';
  }

  Future<void> load() async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      _apply(await _api.fetch());
    } catch (error) {
      _error = error.toString();
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<bool> addItem(Product product) async {
    final variantId = product.defaultVariantId;

    if (variantId == null) {
      _error = 'This product is not available.';
      notifyListeners();
      return false;
    }

    try {
      _apply(await _api.add(variantId, 1));
      return true;
    } catch (error) {
      _error = error.toString();
      notifyListeners();
      return false;
    }
  }

  Future<void> removeItem(int productId) async {
    final item = _itemForProduct(productId);

    if (item?.id == null) {
      return;
    }

    try {
      _apply(await _api.remove(item!.id!));
    } catch (error) {
      _error = error.toString();
      notifyListeners();
    }
  }

  Future<void> incrementQuantity(int productId) async {
    final item = _itemForProduct(productId);

    if (item?.id == null) {
      return;
    }

    try {
      _apply(await _api.update(item!.id!, item.quantity + 1));
    } catch (error) {
      _error = error.toString();
      notifyListeners();
    }
  }

  Future<void> decrementQuantity(int productId) async {
    final item = _itemForProduct(productId);

    if (item?.id == null) {
      return;
    }

    try {
      if (item!.quantity <= 1) {
        _apply(await _api.remove(item.id!));
      } else {
        _apply(await _api.update(item.id!, item.quantity - 1));
      }
    } catch (error) {
      _error = error.toString();
      notifyListeners();
    }
  }

  bool isInCart(int productId) => _itemForProduct(productId) != null;

  int quantityOf(int productId) => _itemForProduct(productId)?.quantity ?? 0;

  Future<void> mergeGuestCart() async {
    await _api.mergeGuestCart();
    await load();
  }

  Future<void> clear() async {
    try {
      await _api.clear();
    } catch (error) {
      _error = error.toString();
    }

    _items.clear();
    notifyListeners();
  }

  void resetLocal() {
    _items.clear();
    _error = null;
    notifyListeners();
  }

  void _apply(CartSnapshot snapshot) {
    _items
      ..clear()
      ..addAll(snapshot.items);
    _error = null;
    notifyListeners();
  }

  CartItem? _itemForProduct(int productId) {
    for (final item in _items) {
      if (item.product.id == productId) {
        return item;
      }
    }

    return null;
  }
}
