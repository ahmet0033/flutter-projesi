import 'package:flutter/material.dart';

import '../main.dart';
import '../models/cart_item.dart';
import '../models/cart_model.dart';
import '../services/auth_scope.dart';
import '../widgets/redeast_ui.dart';
import 'checkout_page.dart';
import 'login_page.dart';
import 'product_detail_page.dart';

class CartPage extends StatelessWidget {
  final VoidCallback? onSwitchToHome;

  const CartPage({super.key, this.onSwitchToHome});

  @override
  Widget build(BuildContext context) {
    final cart = CartModelScope.of(context);

    return Scaffold(
      body: ListenableBuilder(
        listenable: cart,
        builder: (context, _) {
          if (cart.items.isEmpty) {
            return RedEastEmptyState(
              icon: Icons.shopping_bag_outlined,
              title: 'Sepetiniz boş',
              message: 'Ürünlerimize göz atın, sevdiklerinizi ekleyin.',
              actionLabel: 'Alışverişe Başla',
              onAction: () => onSwitchToHome?.call(),
            );
          }

          return CustomScrollView(
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 12),
                sliver: SliverToBoxAdapter(
                  child: RedEastPageHeader(
                    title: 'Sepet',
                    subtitle: '${cart.totalItems} ürün',
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.symmetric(horizontal: 20),
                sliver: SliverList.separated(
                  itemCount: cart.items.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 10),
                  itemBuilder: (context, index) {
                    final item = cart.items[index];

                    return _CartItemCard(
                      item: item,
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) =>
                              ProductDetailPage(product: item.product),
                        ),
                      ),
                      onIncrement: () =>
                          cart.incrementQuantity(item.product.id),
                      onDecrement: () =>
                          cart.decrementQuantity(item.product.id),
                      onRemove: () => cart.removeItem(item.product.id),
                    );
                  },
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                sliver: SliverToBoxAdapter(child: _CheckoutCard(cart: cart)),
              ),
            ],
          );
        },
      ),
    );
  }
}

class _CartItemCard extends StatelessWidget {
  final CartItem item;
  final VoidCallback onTap;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;
  final VoidCallback onRemove;

  const _CartItemCard({
    required this.item,
    required this.onTap,
    required this.onIncrement,
    required this.onDecrement,
    required this.onRemove,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final product = item.product;

    return RedEastSurface(
      padding: const EdgeInsets.all(12),
      onTap: onTap,
      child: Row(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: Container(
              width: 84,
              height: 84,
              color: theme.colorScheme.surfaceContainerHighest,
              padding: const EdgeInsets.all(8),
              child: Image.network(
                product.imageUrl,
                fit: BoxFit.contain,
                errorBuilder: (_, _, _) => Icon(
                  Icons.inventory_2_outlined,
                  size: 28,
                  color: theme.colorScheme.outline,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  product.name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: theme.textTheme.titleMedium,
                ),
                const SizedBox(height: 2),
                Text(
                  product.tagline,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: theme.colorScheme.outline,
                  ),
                ),
                const SizedBox(height: 6),
                Text(product.price, style: theme.textTheme.titleLarge),
                const SizedBox(height: 8),
                Row(
                  children: [
                    RedEastQuantityStepper(
                      quantity: item.quantity,
                      onIncrement: onIncrement,
                      onDecrement: onDecrement,
                    ),
                    const Spacer(),
                    IconButton(
                      tooltip: 'Kaldır',
                      onPressed: onRemove,
                      icon: const Icon(Icons.delete_outline, size: 20),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _CheckoutCard extends StatelessWidget {
  final CartModel cart;

  const _CheckoutCard({required this.cart});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return RedEastSurface(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          Row(
            children: [
              Text('Ara toplam', style: theme.textTheme.bodyLarge),
              const Spacer(),
              Text(cart.totalPriceFormatted, style: theme.textTheme.bodyLarge),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              Text(
                'Kargo ve indirimler ödeme adımında hesaplanır.',
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.outline,
                ),
              ),
            ],
          ),
          const Divider(height: 24),
          Row(
            children: [
              Text('Toplam', style: theme.textTheme.titleMedium),
              const Spacer(),
              Text(
                cart.totalPriceFormatted,
                style: theme.textTheme.headlineSmall,
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 52,
            child: FilledButton(
              onPressed: () {
                final auth = AuthScope.of(context);
                final target = auth.isAuthenticated
                    ? const CheckoutPage()
                    : const LoginPage();

                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => target),
                );
              },
              child: const Text('Satın Al'),
            ),
          ),
        ],
      ),
    );
  }
}
