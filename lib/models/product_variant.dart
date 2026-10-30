class ProductVariant {
  final int id;
  final String name;
  final String sku;
  final int? priceCents;
  final int stockQuantity;
  final Map<String, dynamic> options;

  const ProductVariant({
    required this.id,
    required this.name,
    required this.sku,
    required this.stockQuantity,
    this.priceCents,
    this.options = const {},
  });

  factory ProductVariant.fromJson(Map<String, dynamic> json) {
    return ProductVariant(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      sku: json['sku'] as String? ?? '',
      priceCents: (json['price_cents'] as num?)?.toInt(),
      stockQuantity: (json['stock_quantity'] as num?)?.toInt() ?? 0,
      options: json['options'] is Map
          ? (json['options'] as Map).cast<String, dynamic>()
          : const {},
    );
  }
}
