import 'package:flutter/material.dart';

import '../models/product.dart';

class ProductCard extends StatelessWidget {
  final Product product;
  final bool inCart;
  final VoidCallback onTap;
  final VoidCallback onAddToCart;

  const ProductCard({
    super.key,
    required this.product,
    this.inCart = false,
    required this.onTap,
    required this.onAddToCart,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final category = product.categoryName ?? product.brandName;

    return Material(
      color: theme.colorScheme.surface,
      borderRadius: BorderRadius.circular(18),
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: onTap,
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: theme.colorScheme.outline.withValues(alpha: 0.2),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Container(
                  width: double.infinity,
                  color: theme.colorScheme.surfaceContainerHighest,
                  child: Stack(
                    children: [
                      Padding(
                        padding: const EdgeInsets.all(16),
                        child: Center(
                          child: Image.network(
                            product.imageUrl,
                            fit: BoxFit.contain,
                            errorBuilder: (_, _, _) => Icon(
                              Icons.inventory_2_outlined,
                              size: 40,
                              color: theme.colorScheme.outline,
                            ),
                          ),
                        ),
                      ),
                      if (product.isFeatured)
                        Positioned(
                          left: 10,
                          top: 10,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 8,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: theme.colorScheme.secondary,
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Text(
                              'Yeni',
                              style: theme.textTheme.labelSmall?.copyWith(
                                color: theme.colorScheme.onSecondary,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (category != null)
                      Text(
                        category.toUpperCase(),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: theme.textTheme.labelSmall?.copyWith(
                          color: theme.colorScheme.outline,
                          letterSpacing: 0.8,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    const SizedBox(height: 4),
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
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            product.price,
                            style: theme.textTheme.titleLarge,
                          ),
                        ),
                        if (product.rating != null)
                          Icon(
                            Icons.star,
                            size: 14,
                            color: theme.colorScheme.secondary,
                          ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      width: double.infinity,
                      height: 34,
                      child: FilledButton(
                        onPressed: onAddToCart,
                        style: FilledButton.styleFrom(
                          backgroundColor: theme.colorScheme.primary,
                          foregroundColor: theme.colorScheme.onPrimary,
                          padding: EdgeInsets.zero,
                          textStyle: const TextStyle(
                            fontFamily: 'CabinetGrotesk',
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              inCart ? Icons.check : Icons.add_shopping_cart,
                              size: 14,
                            ),
                            const SizedBox(width: 5),
                            Text(inCart ? 'Eklendi' : 'Sepete Ekle'),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
