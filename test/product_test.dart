import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_project/models/category.dart';
import 'package:flutter_project/models/product.dart';

void main() {
  test('Product parses backend catalog JSON', () {
    final product = Product.fromJson({
      'id': 7,
      'name': 'iPhone 15 Pro',
      'slug': 'iphone-15-pro',
      'tagline': 'Titanium.',
      'description': 'A phone.',
      'price': '\$999.00',
      'price_cents': 99900,
      'currency': 'USD',
      'image': 'https://example.com/iphone.png',
      'images': [
        {'id': 1, 'url': 'https://example.com/iphone-2.png', 'alt': 'side'},
      ],
      'specs': {'chip': 'A17 Pro'},
      'category': {'id': 1, 'name': 'Phones', 'slug': 'phones'},
      'brand': {'id': 1, 'name': 'Apple', 'slug': 'apple'},
      'in_stock': true,
      'is_featured': true,
      'rating': 4.5,
      'reviews_count': 2,
      'variants': [
        {
          'id': 11,
          'name': 'Default',
          'sku': 'RE-IPHONE-15-PRO',
          'price_cents': null,
          'stock_quantity': 8,
          'options': {},
        },
      ],
    });

    expect(product.name, 'iPhone 15 Pro');
    expect(product.numericPrice, 999.0);
    expect(product.defaultVariantId, 11);
    expect(product.categoryName, 'Phones');
    expect(product.rating, 4.5);
    expect(product.reviewsCount, 2);
    expect(product.images, ['https://example.com/iphone-2.png']);
  });

  test('Product falls back to parsing the price string', () {
    final product = Product.fromJson({
      'id': 1,
      'name': 'Legacy',
      'tagline': '',
      'description': '',
      'price': '\$1,299',
      'currency': 'USD',
      'image': '',
      'specs': {},
    });

    expect(product.numericPrice, 1299.0);
    expect(product.defaultVariantId, isNull);
  });

  test('Category parses product counts', () {
    final category = Category.fromJson({
      'id': 2,
      'name': 'Computers',
      'slug': 'computers',
      'products_count': 5,
    });

    expect(category.name, 'Computers');
    expect(category.productsCount, 5);
  });
}
