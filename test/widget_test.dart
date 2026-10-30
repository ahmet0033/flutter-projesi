import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:flutter_project/models/product.dart';
import 'package:flutter_project/theme/redeast_theme.dart';
import 'package:flutter_project/widgets/product_card.dart';

void main() {
  test('RedEast theme exposes brand tokens', () {
    final theme = RedEastTheme.light();

    expect(theme.colorScheme.secondary, RedEastColors.red);
    expect(theme.textTheme.displayLarge?.fontFamily, 'InstrumentSerif');
    expect(theme.textTheme.bodyMedium?.fontFamily, 'CabinetGrotesk');
  });

  testWidgets('product card renders name, price, and action', (tester) async {
    const product = Product(
      id: 1,
      name: 'iPhone 15 Pro',
      tagline: 'Titanium.',
      description: 'A phone.',
      price: '\$999.00',
      currency: 'USD',
      image: 'https://example.com/iphone.png',
      specs: {},
      inStock: true,
    );

    await tester.pumpWidget(
      MaterialApp(
        theme: RedEastTheme.light(),
        home: Scaffold(
          body: ProductCard(product: product, onTap: () {}, onAddToCart: () {}),
        ),
      ),
    );

    expect(find.text('iPhone 15 Pro'), findsOneWidget);
    expect(find.text('\$999.00'), findsOneWidget);
    expect(find.text('Sepete Ekle'), findsOneWidget);
  });
}
