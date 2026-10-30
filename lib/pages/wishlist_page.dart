import 'package:flutter/material.dart';

import '../main.dart' show CartModelScope;
import '../models/product.dart';
import '../services/api_client.dart';
import '../services/auth_scope.dart';
import '../theme/redeast_theme.dart';
import 'product_detail_page.dart';

class WishlistPage extends StatefulWidget {
  const WishlistPage({super.key});

  @override
  State<WishlistPage> createState() => _WishlistPageState();
}

class _WishlistPageState extends State<WishlistPage> {
  late Future<List<Product>> _wishlistFuture;

  @override
  void initState() {
    super.initState();
    _wishlistFuture = _load();
  }

  Future<List<Product>> _load() async {
    final response = await ApiClient.instance.get('/wishlist');
    final data = response is Map ? response['data'] : null;

    if (data is! List) {
      return const [];
    }

    return data
        .whereType<Map>()
        .map((item) => Product.fromJson(item.cast<String, dynamic>()))
        .toList();
  }

  Future<void> _reload() async {
    setState(() => _wishlistFuture = _load());
    await _wishlistFuture;
  }

  Future<void> _remove(Product product) async {
    try {
      await ApiClient.instance.delete('/wishlist/${product.slug}');
      await _reload();
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
    final auth = AuthScope.of(context);
    final cart = CartModelScope.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Favorilerim')),
      body: !auth.isAuthenticated
          ? const Center(child: Text('Favoriler için giriş yapın.'))
          : FutureBuilder<List<Product>>(
              future: _wishlistFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (snapshot.hasError) {
                  return Center(
                    child: Padding(
                      padding: const EdgeInsets.all(24),
                      child: Text(
                        snapshot.error.toString(),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  );
                }

                final products = snapshot.data ?? const [];

                if (products.isEmpty) {
                  return const Center(
                    child: Text('Henüz favori ürününüz yok.'),
                  );
                }

                return RefreshIndicator(
                  onRefresh: _reload,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: products.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      final product = products[index];

                      return Card(
                        child: InkWell(
                          borderRadius: BorderRadius.circular(16),
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) =>
                                  ProductDetailPage(product: product),
                            ),
                          ),
                          child: Padding(
                            padding: const EdgeInsets.all(12),
                            child: Row(
                              children: [
                                ClipRRect(
                                  borderRadius: BorderRadius.circular(12),
                                  child: Container(
                                    width: 84,
                                    height: 84,
                                    color: Theme.of(
                                      context,
                                    ).colorScheme.surfaceContainerHighest,
                                    padding: const EdgeInsets.all(8),
                                    child: Image.network(
                                      product.imageUrl,
                                      fit: BoxFit.contain,
                                      errorBuilder: (_, _, _) => Icon(
                                        Icons.phone_iphone,
                                        color: Theme.of(
                                          context,
                                        ).colorScheme.outline,
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        product.name,
                                        style: Theme.of(
                                          context,
                                        ).textTheme.titleMedium,
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        product.price,
                                        style: Theme.of(
                                          context,
                                        ).textTheme.titleLarge,
                                      ),
                                      const SizedBox(height: 8),
                                      Row(
                                        children: [
                                          FilledButton.tonal(
                                            onPressed: () async {
                                              final added = await cart.addItem(
                                                product,
                                              );
                                              if (!context.mounted) {
                                                return;
                                              }
                                              ScaffoldMessenger.of(
                                                context,
                                              ).showSnackBar(
                                                SnackBar(
                                                  content: Text(
                                                    added
                                                        ? '${product.name} sepete eklendi'
                                                        : (cart.error ??
                                                              'Sepete eklenemedi'),
                                                  ),
                                                ),
                                              );
                                            },
                                            child: const Text('Sepete Ekle'),
                                          ),
                                          const SizedBox(width: 8),
                                          IconButton(
                                            tooltip: 'Kaldır',
                                            onPressed: () => _remove(product),
                                            icon: const Icon(
                                              Icons.favorite,
                                              color: RedEastColors.red,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
    );
  }
}
