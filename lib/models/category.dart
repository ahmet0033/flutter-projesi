class Category {
  final int id;
  final String name;
  final String slug;
  final String? description;
  final String? imageUrl;
  final int productsCount;

  const Category({
    required this.id,
    required this.name,
    required this.slug,
    this.description,
    this.imageUrl,
    this.productsCount = 0,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
      description: json['description'] as String?,
      imageUrl: json['image_url'] as String?,
      productsCount: (json['products_count'] as num?)?.toInt() ?? 0,
    );
  }
}
