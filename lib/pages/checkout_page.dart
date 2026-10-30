import 'package:flutter/material.dart';

import '../main.dart' show CartModelScope;
import '../services/api_client.dart';
import '../services/auth_scope.dart';
import '../widgets/redeast_ui.dart';
import 'orders_page.dart';

class CheckoutPage extends StatefulWidget {
  const CheckoutPage({super.key});

  @override
  State<CheckoutPage> createState() => _CheckoutPageState();
}

class _CheckoutPageState extends State<CheckoutPage> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _line1Controller = TextEditingController();
  final _cityController = TextEditingController();
  final _postalController = TextEditingController();
  final _couponController = TextEditingController();
  final _notesController = TextEditingController();

  List<Map<String, dynamic>> _shippingMethods = [];
  int? _shippingMethodId;
  String _paymentMethod = 'cod';
  Map<String, dynamic>? _quote;
  bool _loading = true;
  bool _placing = false;
  bool _prefilled = false;

  @override
  void initState() {
    super.initState();
    _loadShippingMethods();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();

    if (!_prefilled) {
      final user = AuthScope.of(context).user;
      _nameController.text = user?.name ?? '';
      _emailController.text = user?.email ?? '';
      _prefilled = true;
    }
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _line1Controller.dispose();
    _cityController.dispose();
    _postalController.dispose();
    _couponController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _loadShippingMethods() async {
    try {
      final response = await ApiClient.instance.get('/shipping/methods');
      final data = response is Map ? response['data'] : null;
      final methods = data is List
          ? data
                .whereType<Map>()
                .map((method) => method.cast<String, dynamic>())
                .toList()
          : <Map<String, dynamic>>[];

      if (!mounted) {
        return;
      }

      setState(() {
        _shippingMethods = methods;
        _shippingMethodId = methods.isNotEmpty
            ? methods.first['id'] as int
            : null;
      });

      await _fetchQuote();
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() => _loading = false);
      _showError(error.toString());
    }
  }

  Future<void> _fetchQuote() async {
    if (_shippingMethodId == null) {
      setState(() => _loading = false);
      return;
    }

    setState(() => _loading = true);

    try {
      final response = await ApiClient.instance.post(
        '/checkout/quote',
        data: {
          'shipping_method_id': _shippingMethodId,
          if (_couponController.text.trim().isNotEmpty)
            'coupon_code': _couponController.text.trim(),
        },
      );
      final quote = response is Map ? response : null;

      if (!mounted) {
        return;
      }

      setState(() {
        _quote = quote?.cast<String, dynamic>();
        _loading = false;
      });
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() => _loading = false);
      _showError(error.toString());
    }
  }

  Future<void> _placeOrder() async {
    if (!_formKey.currentState!.validate() || _placing) {
      return;
    }

    setState(() => _placing = true);
    final cart = CartModelScope.of(context);

    try {
      final response = await ApiClient.instance.post(
        '/orders',
        data: {
          'shipping_method_id': _shippingMethodId,
          if (_couponController.text.trim().isNotEmpty)
            'coupon_code': _couponController.text.trim(),
          'payment_method': _paymentMethod,
          'customer_name': _nameController.text.trim(),
          'customer_email': _emailController.text.trim(),
          'customer_phone': _phoneController.text.trim(),
          'shipping_address': {
            'name': _nameController.text.trim(),
            'phone': _phoneController.text.trim(),
            'line1': _line1Controller.text.trim(),
            'city': _cityController.text.trim(),
            'postal_code': _postalController.text.trim(),
            'country': 'TR',
          },
          'notes': _notesController.text.trim().isEmpty
              ? null
              : _notesController.text.trim(),
          'idempotency_key': DateTime.now().microsecondsSinceEpoch.toString(),
        },
      );

      final order = response is Map ? response['data'] : null;
      final number = order is Map ? order['number'] : null;

      await cart.load();

      if (!mounted) {
        return;
      }

      Navigator.of(
        context,
      ).pushReplacement(MaterialPageRoute(builder: (_) => const OrdersPage()));

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            number == null
                ? 'Siparişiniz alındı.'
                : 'Siparişiniz alındı: $number',
          ),
        ),
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      setState(() => _placing = false);
      _showError(error.toString());
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    final cart = CartModelScope.of(context);

    if (cart.items.isEmpty) {
      return Scaffold(
        appBar: AppBar(title: const Text('Ödeme')),
        body: const Center(child: Text('Sepetiniz boş.')),
      );
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Ödeme')),
      body: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 760),
          child: Form(
            key: _formKey,
            child: ListView(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 120),
              children: [
                const RedEastSectionHeader(title: 'Teslimat bilgileri'),
                const SizedBox(height: 12),
                TextFormField(
                  controller: _nameController,
                  decoration: const InputDecoration(labelText: 'Ad soyad'),
                  validator: _required,
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _emailController,
                  keyboardType: TextInputType.emailAddress,
                  decoration: const InputDecoration(labelText: 'E-posta'),
                  validator: (value) => (value == null || !value.contains('@'))
                      ? 'Geçerli bir e-posta girin.'
                      : null,
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _phoneController,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(labelText: 'Telefon'),
                  validator: _required,
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _line1Controller,
                  decoration: const InputDecoration(labelText: 'Adres'),
                  validator: _required,
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: TextFormField(
                        controller: _cityController,
                        decoration: const InputDecoration(labelText: 'Şehir'),
                        validator: _required,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: TextFormField(
                        controller: _postalController,
                        decoration: const InputDecoration(
                          labelText: 'Posta kodu',
                        ),
                        validator: _required,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                const RedEastSectionHeader(title: 'Kargo'),
                const SizedBox(height: 10),
                DropdownButtonFormField<int>(
                  key: ValueKey(_shippingMethodId),
                  initialValue: _shippingMethodId,
                  decoration: const InputDecoration(labelText: 'Kargo yöntemi'),
                  items: _shippingMethods
                      .map(
                        (method) => DropdownMenuItem<int>(
                          value: method['id'] as int,
                          child: Text(
                            '${method['name']} · ${_money(method['price_cents'] as int? ?? 0)}',
                          ),
                        ),
                      )
                      .toList(),
                  onChanged: (value) {
                    setState(() => _shippingMethodId = value);
                    _fetchQuote();
                  },
                ),
                const SizedBox(height: 10),
                Row(
                  children: [
                    Expanded(
                      child: TextFormField(
                        controller: _couponController,
                        decoration: const InputDecoration(
                          labelText: 'Kupon kodu',
                          hintText: 'WELCOME10',
                        ),
                      ),
                    ),
                    const SizedBox(width: 10),
                    FilledButton.tonal(
                      onPressed: _loading ? null : _fetchQuote,
                      child: const Text('Uygula'),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                const RedEastSectionHeader(title: 'Ödeme'),
                const SizedBox(height: 10),
                DropdownButtonFormField<String>(
                  initialValue: _paymentMethod,
                  decoration: const InputDecoration(labelText: 'Ödeme yöntemi'),
                  items: const [
                    DropdownMenuItem(value: 'cod', child: Text('Kapıda ödeme')),
                    DropdownMenuItem(
                      value: 'bank_transfer',
                      child: Text('Havale / EFT'),
                    ),
                  ],
                  onChanged: (value) {
                    if (value != null) {
                      setState(() => _paymentMethod = value);
                    }
                  },
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: _notesController,
                  maxLines: 2,
                  decoration: const InputDecoration(labelText: 'Sipariş notu'),
                ),
                const SizedBox(height: 24),
                if (_quote != null) _QuoteCard(quote: _quote!),
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
          child: SizedBox(
            height: 52,
            child: FilledButton(
              onPressed: (_placing || _loading || _quote == null)
                  ? null
                  : _placeOrder,
              child: Text(
                _placing ? 'Sipariş oluşturuluyor…' : 'Siparişi Tamamla',
              ),
            ),
          ),
        ),
      ),
    );
  }

  String? _required(String? value) {
    return (value == null || value.trim().isEmpty) ? 'Bu alan gerekli.' : null;
  }

  String _money(int cents) => '\$${(cents / 100).toStringAsFixed(2)}';
}

class _QuoteCard extends StatelessWidget {
  final Map<String, dynamic> quote;

  const _QuoteCard({required this.quote});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final coupon = quote['coupon'];

    return RedEastSurface(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _row('Ara toplam', quote['subtotal_cents'], theme),
          if ((quote['discount_cents'] as num?)?.toInt() != 0)
            _row('İndirim', -(quote['discount_cents'] as num).toInt(), theme),
          _row('Kargo', quote['shipping_cents'], theme),
          _row('KDV (dahil)', quote['tax_cents'], theme),
          const Divider(height: 24),
          Row(
            children: [
              Text('Toplam', style: theme.textTheme.titleMedium),
              const Spacer(),
              Text(
                _money((quote['total_cents'] as num?)?.toInt() ?? 0),
                style: theme.textTheme.titleLarge,
              ),
            ],
          ),
          if (coupon is Map) ...[
            const SizedBox(height: 6),
            Align(
              alignment: Alignment.centerLeft,
              child: Text(
                'Kupon: ${coupon['code']}',
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.secondary,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _row(String label, dynamic value, ThemeData theme) {
    final cents = value is num ? value.toInt() : 0;

    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        children: [
          Text(label, style: theme.textTheme.bodyMedium),
          const Spacer(),
          Text(_money(cents), style: theme.textTheme.bodyMedium),
        ],
      ),
    );
  }

  String _money(int cents) => '\$${(cents / 100).toStringAsFixed(2)}';
}
