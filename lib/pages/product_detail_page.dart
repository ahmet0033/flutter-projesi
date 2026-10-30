import 'package:flutter/material.dart';

import '../main.dart';
import '../models/product.dart';
import '../services/api_client.dart';
import '../services/auth_scope.dart';
import '../widgets/redeast_ui.dart';
import 'login_page.dart';
import 'reviews_page.dart';

class ProductDetailPage extends StatefulWidget {
  final Product product;

  const ProductDetailPage({super.key, required this.product});

  @override
  State<ProductDetailPage> createState() => _ProductDetailPageState();
}

class _ProductDetailPageState extends State<ProductDetailPage> {
  int _selectedImage = 0;

  Product get product => widget.product;

  List<String> get images {
    final primary = product.imageUrl;
    final gallery = <String>[
      if (primary.isNotEmpty) primary,
      ...product.images.where((url) => url.isNotEmpty && url != primary),
    ];

    return gallery.isEmpty ? [''] : gallery;
  }

  Future<void> _addToCart() async {
    final cart = CartModelScope.of(context);
    final added = await cart.addItem(product);

    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          added
              ? '${product.name} sepete eklendi'
              : (cart.error ?? 'Sepete eklenemedi'),
        ),
      ),
    );
  }

  Future<void> _addToWishlist() async {
    final auth = AuthScope.of(context);

    if (!auth.isAuthenticated) {
      Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const LoginPage()),
      );
      return;
    }

    try {
      await ApiClient.instance.post('/wishlist/${product.slug}');

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Favorilere eklendi.')));
    } catch (error) {
      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = CartModelScope.of(context);
    final theme = Theme.of(context);
    final inCart = cart.isInCart(product.id);
    final quantity = cart.quantityOf(product.id);

    return Scaffold(
      appBar: AppBar(
        title: Text(product.name),
        actions: [
          IconButton(
            tooltip: 'Favorilere ekle',
            icon: const Icon(Icons.favorite_border),
            onPressed: _addToWishlist,
          ),
        ],
      ),
      body: ListView(
        children: [
          Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 900),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    height: 340,
                    margin: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                    decoration: BoxDecoration(
                      color: theme.colorScheme.surfaceContainerHighest,
                      borderRadius: BorderRadius.circular(24),
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Padding(
                      padding: const EdgeInsets.all(28),
                      child: Image.network(
                        images[_selectedImage],
                        fit: BoxFit.contain,
                        webHtmlElementStrategy: WebHtmlElementStrategy.prefer,
                        errorBuilder: (_, _, _) => Icon(
                          Icons.inventory_2_outlined,
                          size: 72,
                          color: theme.colorScheme.outline,
                        ),
                      ),
                    ),
                  ),
                  if (images.length > 1)
                    SizedBox(
                      height: 76,
                      child: ListView.separated(
                        padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                        scrollDirection: Axis.horizontal,
                        itemCount: images.length,
                        separatorBuilder: (_, _) => const SizedBox(width: 8),
                        itemBuilder: (context, index) {
                          final selected = index == _selectedImage;

                          return GestureDetector(
                            onTap: () => setState(() => _selectedImage = index),
                            child: Container(
                              width: 64,
                              height: 64,
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                color:
                                    theme.colorScheme.surfaceContainerHighest,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: selected
                                      ? theme.colorScheme.primary
                                      : Colors.transparent,
                                  width: 1.5,
                                ),
                              ),
                              child: Image.network(
                                images[index],
                                fit: BoxFit.contain,
                                errorBuilder: (_, _, _) => const SizedBox(),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  Padding(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (product.categoryName != null)
                          Text(
                            product.categoryName!.toUpperCase(),
                            style: theme.textTheme.labelSmall?.copyWith(
                              color: theme.colorScheme.outline,
                              letterSpacing: 1,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        const SizedBox(height: 4),
                        Text(product.name, style: theme.textTheme.displaySmall),
                        const SizedBox(height: 6),
                        Text(
                          product.tagline,
                          style: theme.textTheme.bodyLarge?.copyWith(
                            color: theme.colorScheme.outline,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            RedEastRating(
                              rating: product.rating,
                              count: product.reviewsCount,
                              size: 18,
                            ),
                            const SizedBox(width: 12),
                            TextButton(
                              onPressed: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ReviewsPage(product: product),
                                ),
                              ),
                              child: const Text('Yorumları Gör'),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          product.price,
                          style: theme.textTheme.headlineMedium,
                        ),
                        if (product.specs.isNotEmpty) ...[
                          const SizedBox(height: 22),
                          RedEastSectionHeader(title: 'Özellikler'),
                          const SizedBox(height: 8),
                          RedEastSurface(
                            child: Column(
                              children: product.specs.entries
                                  .map(
                                    (entry) => Padding(
                                      padding: const EdgeInsets.only(
                                        bottom: 10,
                                      ),
                                      child: Row(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          SizedBox(
                                            width: 130,
                                            child: Text(
                                              _formatKey(entry.key),
                                              style: theme.textTheme.bodyMedium
                                                  ?.copyWith(
                                                    color: theme
                                                        .colorScheme
                                                        .outline,
                                                  ),
                                            ),
                                          ),
                                          Expanded(
                                            child: Text(
                                              entry.value.toString(),
                                              style: theme.textTheme.bodyMedium
                                                  ?.copyWith(
                                                    fontWeight: FontWeight.w600,
                                                  ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  )
                                  .toList(),
                            ),
                          ),
                        ],
                        const SizedBox(height: 22),
                        RedEastSectionHeader(title: 'Açıklama'),
                        const SizedBox(height: 8),
                        Text(
                          product.description,
                          style: theme.textTheme.bodyLarge?.copyWith(
                            height: 1.6,
                            color: theme.colorScheme.onSurface,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: theme.colorScheme.surface,
          border: Border(
            top: BorderSide(
              color: theme.colorScheme.outline.withValues(alpha: 0.2),
            ),
          ),
        ),
        child: SafeArea(
          top: false,
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Fiyat',
                        style: theme.textTheme.labelSmall?.copyWith(
                          color: theme.colorScheme.outline,
                        ),
                      ),
                      Text(product.price, style: theme.textTheme.titleLarge),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: _addToWishlist,
                  icon: const Icon(Icons.favorite_border),
                ),
                const SizedBox(width: 4),
                SizedBox(
                  height: 50,
                  child: FilledButton(
                    onPressed: _addToCart,
                    child: Text(inCart ? 'Sepette · $quantity' : 'Sepete Ekle'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  String _formatKey(String key) {
    final words = key.split(RegExp(r'[_\s]'));

    return words
        .map(
          (word) => word.isNotEmpty
              ? '${word[0].toUpperCase()}${word.substring(1)}'
              : '',
        )
        .join(' ');
  }
}
