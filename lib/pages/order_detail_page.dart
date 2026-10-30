import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../services/api_client.dart';
import '../theme/redeast_theme.dart';
import '../widgets/redeast_ui.dart';

class OrderDetailPage extends StatefulWidget {
  final Map<String, dynamic> order;

  const OrderDetailPage({super.key, required this.order});

  @override
  State<OrderDetailPage> createState() => _OrderDetailPageState();
}

class _OrderDetailPageState extends State<OrderDetailPage> {
  late Map<String, dynamic> _order = widget.order;
  bool _cancelling = false;

  Future<void> _cancel() async {
    setState(() => _cancelling = true);

    try {
      final response = await ApiClient.instance.post(
        '/orders/${_order['number']}/cancel',
      );
      final data = response is Map ? response['data'] : null;

      if (!mounted) {
        return;
      }

      setState(() {
        _order = data is Map ? data.cast<String, dynamic>() : _order;
        _cancelling = false;
      });

      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Sipariş iptal edildi.')));
    } catch (error) {
      if (!mounted) {
        return;
      }

      setState(() => _cancelling = false);
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final status = _order['status']?.toString() ?? 'pending';
    final paymentStatus = _order['payment_status']?.toString() ?? 'pending';
    final items = _order['items'] is List ? _order['items'] as List : const [];
    final shipping = _order['shipping_address'] is Map
        ? (_order['shipping_address'] as Map).cast<String, dynamic>()
        : <String, dynamic>{};
    final placedAt = DateTime.tryParse(_order['placed_at']?.toString() ?? '');
    final canCancel = status == 'pending' || status == 'processing';
    final total = (_order['total_cents'] as num?)?.toInt() ?? 0;

    return Scaffold(
      appBar: AppBar(title: Text(_order['number']?.toString() ?? 'Sipariş')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          RedEastSurface(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text('Durum', style: theme.textTheme.titleMedium),
                    const Spacer(),
                    RedEastStatusPill(
                      label: orderStatusLabel(status),
                      color: orderStatusColor(status),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Text(
                      'Ödeme',
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: theme.colorScheme.outline,
                      ),
                    ),
                    const Spacer(),
                    Text(
                      paymentStatus == 'paid' ? 'Ödendi' : 'Bekliyor',
                      style: theme.textTheme.bodyMedium,
                    ),
                  ],
                ),
                if (placedAt != null) ...[
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Text(
                        'Tarih',
                        style: theme.textTheme.bodyMedium?.copyWith(
                          color: theme.colorScheme.outline,
                        ),
                      ),
                      const Spacer(),
                      Text(
                        DateFormat('dd.MM.yyyy HH:mm').format(placedAt),
                        style: theme.textTheme.bodyMedium,
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 12),
          RedEastSectionHeader(title: 'Ürünler'),
          const SizedBox(height: 8),
          RedEastSurface(
            child: Column(
              children: items.map((item) {
                final map = item as Map;
                final quantity = (map['quantity'] as num?)?.toInt() ?? 0;
                final lineTotal =
                    (map['line_total_cents'] as num?)?.toInt() ?? 0;

                return Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              map['product_name']?.toString() ?? '',
                              style: theme.textTheme.titleSmall,
                            ),
                            Text(
                              '${map['sku']} · $quantity adet',
                              style: theme.textTheme.bodySmall?.copyWith(
                                color: theme.colorScheme.outline,
                              ),
                            ),
                          ],
                        ),
                      ),
                      Text(
                        '\$${(lineTotal / 100).toStringAsFixed(2)}',
                        style: theme.textTheme.bodyMedium,
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
          const SizedBox(height: 12),
          const RedEastSectionHeader(title: 'Teslimat'),
          const SizedBox(height: 8),
          RedEastSurface(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  shipping['name']?.toString() ?? '',
                  style: theme.textTheme.titleSmall,
                ),
                const SizedBox(height: 2),
                Text(
                  '${shipping['line1'] ?? ''}, ${shipping['city'] ?? ''} ${shipping['postal_code'] ?? ''}',
                  style: theme.textTheme.bodyMedium?.copyWith(
                    color: theme.colorScheme.outline,
                  ),
                ),
                if (shipping['phone'] != null)
                  Text(
                    shipping['phone'].toString(),
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: theme.colorScheme.outline,
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          RedEastSurface(
            child: Row(
              children: [
                Text('Toplam', style: theme.textTheme.titleMedium),
                const Spacer(),
                Text(
                  '\$${(total / 100).toStringAsFixed(2)}',
                  style: theme.textTheme.headlineSmall,
                ),
              ],
            ),
          ),
          if (canCancel) ...[
            const SizedBox(height: 20),
            OutlinedButton.icon(
              onPressed: _cancelling ? null : _cancel,
              icon: const Icon(Icons.close),
              label: Text(
                _cancelling ? 'İptal ediliyor…' : 'Siparişi İptal Et',
              ),
            ),
          ],
        ],
      ),
    );
  }
}

String orderStatusLabel(String status) {
  return switch (status) {
    'pending' => 'Beklemede',
    'paid' => 'Ödendi',
    'processing' => 'Hazırlanıyor',
    'shipped' => 'Kargoda',
    'delivered' => 'Teslim edildi',
    'cancelled' => 'İptal edildi',
    'refunded' => 'İade edildi',
    _ => status,
  };
}

Color orderStatusColor(String status) {
  return switch (status) {
    'paid' || 'delivered' => RedEastColors.success,
    'processing' => RedEastColors.warning,
    'shipped' => RedEastColors.info,
    'cancelled' || 'refunded' => RedEastColors.red,
    _ => RedEastColors.gray,
  };
}
