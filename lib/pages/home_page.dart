import 'package:flutter/material.dart';

import '../models/category.dart';
import '../models/product.dart';
import '../services/api_service.dart';
import '../theme/redeast_theme.dart';
import '../widgets/product_card.dart';
import '../widgets/redeast_ui.dart';
import '../main.dart' show CartModelScope;
import 'product_detail_page.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  late Future<List<Product>> _productsFuture;
  late Future<List<Category>> _categoriesFuture;
  final TextEditingController _searchController = TextEditingController();
  String? _selectedCategory;
  String? _searchQuery;

  @override
  void initState() {
    super.initState();
    _categoriesFuture = ApiService.fetchCategories();
    _productsFuture = ApiService.fetchProducts();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _refresh() async {
    setState(() {
      _categoriesFuture = ApiService.fetchCategories();
      _productsFuture = ApiService.fetchProducts(
        category: _selectedCategory,
        search: _searchQuery,
      );
    });
    await _productsFuture;
  }

  void _selectCategory(String? slug) {
    setState(() {
      _selectedCategory = slug;
      _productsFuture = ApiService.fetchProducts(
        category: slug,
        search: _searchQuery,
      );
    });
  }

  void _applySearch(String value) {
    setState(() {
      _searchQuery = value.trim().isEmpty ? null : value.trim();
      _productsFuture = ApiService.fetchProducts(
        category: _selectedCategory,
        search: _searchQuery,
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () async {
        _refresh();
        await _productsFuture;
      },
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          const SliverToBoxAdapter(child: _HeroWidget()),
          SliverPadding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
            sliver: SliverToBoxAdapter(
              child: TextField(
                controller: _searchController,
                textInputAction: TextInputAction.search,
                onSubmitted: _applySearch,
                decoration: InputDecoration(
                  hintText: 'Ürün ara',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _searchController.text.isEmpty
                      ? null
                      : IconButton(
                          icon: const Icon(Icons.close),
                          onPressed: () {
                            _searchController.clear();
                            _applySearch('');
                          },
                        ),
                ),
              ),
            ),
          ),
          const SliverPadding(
            padding: EdgeInsets.fromLTRB(16, 8, 16, 4),
            sliver: SliverToBoxAdapter(
              child: RedEastSectionHeader(title: 'Ürünler'),
            ),
          ),
          SliverToBoxAdapter(
            child: _CategoryChips(
              categoriesFuture: _categoriesFuture,
              selected: _selectedCategory,
              onSelected: _selectCategory,
            ),
          ),
          FutureBuilder<List<Product>>(
            future: _productsFuture,
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const SliverFillRemaining(
                  child: Center(child: CircularProgressIndicator()),
                );
              }
              if (snapshot.hasError) {
                return SliverFillRemaining(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.wifi_off,
                        size: 48,
                        color: Colors.grey.shade400,
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'Ürünler yüklenemedi',
                        style: TextStyle(
                          color: Colors.grey.shade600,
                          fontSize: 16,
                        ),
                      ),
                      const SizedBox(height: 16),
                      FilledButton.tonalIcon(
                        onPressed: () => _refresh(),
                        icon: const Icon(Icons.refresh, size: 18),
                        label: const Text('Tekrar Dene'),
                      ),
                    ],
                  ),
                );
              }
              final products = snapshot.data!;
              final cart = CartModelScope.of(context);
              final width = MediaQuery.sizeOf(context).width;
              final columns = width >= 1000 ? 4 : (width >= 640 ? 3 : 2);
              return SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                sliver: SliverGrid(
                  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: columns,
                    mainAxisSpacing: 12,
                    crossAxisSpacing: 12,
                    childAspectRatio: columns == 2 ? 0.60 : 0.68,
                  ),
                  delegate: SliverChildBuilderDelegate((context, index) {
                    final product = products[index];
                    return ProductCard(
                      product: product,
                      inCart: cart.isInCart(product.id),
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => ProductDetailPage(product: product),
                          ),
                        );
                      },
                      onAddToCart: () async {
                        final added = await cart.addItem(product);

                        if (!context.mounted) {
                          return;
                        }

                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(
                              added
                                  ? '${product.name} sepete eklendi'
                                  : (cart.error ?? 'Sepete eklenemedi'),
                            ),
                            behavior: SnackBarBehavior.floating,
                            duration: const Duration(seconds: 2),
                          ),
                        );
                      },
                    );
                  }, childCount: products.length),
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}

class _CategoryChips extends StatelessWidget {
  final Future<List<Category>> categoriesFuture;
  final String? selected;
  final ValueChanged<String?> onSelected;

  const _CategoryChips({
    required this.categoriesFuture,
    required this.selected,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 48,
      child: FutureBuilder<List<Category>>(
        future: categoriesFuture,
        builder: (context, snapshot) {
          final categories = snapshot.data ?? const <Category>[];

          return ListView.separated(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            scrollDirection: Axis.horizontal,
            itemCount: categories.length + 1,
            separatorBuilder: (_, _) => const SizedBox(width: 8),
            itemBuilder: (context, index) {
              final category = index == 0 ? null : categories[index - 1];
              final isSelected = selected == category?.slug;

              return ChoiceChip(
                label: Text(index == 0 ? 'Tümü' : category!.name),
                selected: isSelected,
                showCheckmark: false,
                onSelected: (_) => onSelected(category?.slug),
                selectedColor: Theme.of(context).colorScheme.primary,
                backgroundColor: Theme.of(context).colorScheme.surface,
                side: BorderSide(
                  color: Theme.of(
                    context,
                  ).colorScheme.outline.withValues(alpha: 0.4),
                ),
                labelStyle: TextStyle(
                  color: isSelected
                      ? Theme.of(context).colorScheme.onPrimary
                      : Theme.of(context).colorScheme.onSurface,
                  fontFamily: 'CabinetGrotesk',
                  fontWeight: FontWeight.w500,
                  fontSize: 13,
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 8,
                ),
              );
            },
          );
        },
      ),
    );
  }
}

class _HeroWidget extends StatelessWidget {
  const _HeroWidget();

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Container(
      margin: const EdgeInsets.fromLTRB(16, 12, 16, 12),
      height: 210,
      decoration: BoxDecoration(
        color: RedEastColors.charcoal,
        borderRadius: BorderRadius.circular(24),
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        children: [
          Positioned(
            right: -40,
            top: -40,
            child: _Dot(size: 190, color: Colors.white.withValues(alpha: 0.04)),
          ),
          Positioned(
            right: 42,
            bottom: 32,
            child: _Dot(size: 54, color: RedEastColors.red),
          ),
          Positioned(
            left: 24,
            top: 24,
            child: RedEastLogo(height: 26, light: true),
          ),
          Positioned(
            left: 24,
            right: 24,
            bottom: 24,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Yeni Ürünler',
                  style: theme.textTheme.displaySmall?.copyWith(
                    color: Colors.white,
                    fontFamily: 'InstrumentSerif',
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'En yeni teknolojiyi keşfedin',
                  style: theme.textTheme.bodyMedium?.copyWith(
                    color: Colors.white70,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Dot extends StatelessWidget {
  final double size;
  final Color color;

  const _Dot({required this.size, required this.color});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(color: color, shape: BoxShape.circle),
    );
  }
}
