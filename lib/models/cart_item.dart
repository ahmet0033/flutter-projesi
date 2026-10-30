import 'product.dart';

class CartItem {
  final int? id;
  final int? variantId;
  final Product product;
  int quantity;

  CartItem({this.id, this.variantId, required this.product, this.quantity = 1});

  factory CartItem.fromJson(Map<String, dynamic> json) {
    final productJson = json['product'];
    final variantJson = json['variant'];

    return CartItem(
      id: json['id'] as int?,
      variantId: variantJson is Map ? variantJson['id'] as int? : null,
      product: productJson is Map
          ? Product.fromJson(productJson.cast<String, dynamic>())
          : const Product(
              id: 0,
              name: '',
              tagline: '',
              description: '',
              price: '',
              currency: 'USD',
              image: '',
              specs: {},
            ),
      quantity: (json['quantity'] as num?)?.toInt() ?? 1,
    );
  }
}
