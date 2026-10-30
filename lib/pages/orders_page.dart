import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../services/api_client.dart';
import '../services/auth_scope.dart';
import '../widgets/redeast_ui.dart';
import 'order_detail_page.dart';

class OrdersPage extends StatefulWidget {
  const OrdersPage({super.key});

  @override
  State<OrdersPage> createState() => _OrdersPageState();
}

class _OrdersPageState extends State<OrdersPage> {
  late Future<List<Map<String, dynamic>>> _ordersFuture;

  @override
  void initState() {
    super.initState();
    _ordersFuture = _load();
  }

  Future<List<Map<String, dynamic>>> _load() async {
    final response = await ApiClient.instance.get('/orders');
    final data = response is Map ? response['data'] : null;

    if (data is! List) {
      return const [];
    }

    return data
        .whereType<Map>()
        .map((order) => order.cast<String, dynamic>())
        .toList();
  }

  Future<void> _refresh() async {
    setState(() => _ordersFuture = _load());
    await _ordersFuture;
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthScope.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Siparişlerim')),
      body: !auth.isAuthenticated
          ? const RedEastEmptyState(
              icon: Icons.lock_outline,
              title: 'Giriş gerekli',
              message: 'Siparişlerinizi görmek için giriş yapın.',
            )
          : FutureBuilder<List<Map<String, dynamic>>>(
              future: _ordersFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (snapshot.hasError) {
                  return RedEastEmptyState(
                    icon: Icons.wifi_off,
                    title: 'Siparişler yüklenemedi',
                    message: snapshot.error.toString(),
                  );
                }

                final orders = snapshot.data ?? const [];

                if (orders.isEmpty) {
                  return const RedEastEmptyState(
                    icon: Icons.receipt_long_outlined,
                    title: 'Henüz siparişiniz yok',
                    message: 'İlk siparişinizi vermek için mağazaya göz atın.',
                  );
                }

                return RefreshIndicator(
                  onRefresh: _refresh,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: orders.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 10),
                    itemBuilder: (context, index) {
                      final order = orders[index];

                      return _OrderCard(
                        order: order,
                        onTap: () async {
                          await Navigator.push<bool>(
                            context,
                            MaterialPageRoute(
                              builder: (_) => OrderDetailPage(order: order),
                            ),
                          );

                          await _refresh();
                        },
                      );
                    },
                  ),
                );
              },
            ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  final Map<String, dynamic> order;
  final VoidCallback onTap;

  const _OrderCard({required this.order, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final status = order['status']?.toString() ?? 'pending';
    final total = (order['total_cents'] as num?)?.toInt() ?? 0;
    final items = order['items'] is List ? order['items'] as List : const [];
    final placedAt = DateTime.tryParse(order['placed_at']?.toString() ?? '');

    return RedEastSurface(
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(
                  order['number']?.toString() ?? '',
                  style: theme.textTheme.titleMedium,
                ),
              ),
              RedEastStatusPill(
                label: orderStatusLabel(status),
                color: orderStatusColor(status),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Text(
                '\$${(total / 100).toStringAsFixed(2)}',
                style: theme.textTheme.titleLarge,
              ),
              const Spacer(),
              if (placedAt != null)
                Text(
                  DateFormat('dd.MM.yyyy HH:mm').format(placedAt),
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: theme.colorScheme.outline,
                  ),
                ),
            ],
          ),
          if (items.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              items
                  .map((item) {
                    final map = item as Map;
                    return '${map['quantity']}× ${map['product_name']}';
                  })
                  .join(', '),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: theme.textTheme.bodySmall?.copyWith(
                color: theme.colorScheme.outline,
              ),
            ),
          ],
        ],
      ),
    );
  }
}
