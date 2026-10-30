import 'package:flutter/material.dart';

import '../main.dart' show CartModelScope;
import '../services/auth_scope.dart';
import 'login_page.dart';
import 'notifications_page.dart';
import 'orders_page.dart';
import 'register_page.dart';
import 'wishlist_page.dart';

class AccountPage extends StatelessWidget {
  const AccountPage({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = AuthScope.of(context);

    if (!auth.initialized) {
      return const Center(child: CircularProgressIndicator());
    }

    if (!auth.isAuthenticated) {
      return _GuestAccount(
        onLogin: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const LoginPage()),
        ),
        onRegister: () => Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const RegisterPage()),
        ),
      );
    }

    final user = auth.user!;
    final cart = CartModelScope.of(context);
    final theme = Theme.of(context);

    return ListView(
      padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
      children: [
        CircleAvatar(
          radius: 32,
          backgroundColor: theme.colorScheme.primary,
          child: Text(
            user.name.isNotEmpty ? user.name[0].toUpperCase() : '?',
            style: TextStyle(
              color: theme.colorScheme.onPrimary,
              fontSize: 26,
              fontFamily: 'InstrumentSerif',
            ),
          ),
        ),
        const SizedBox(height: 12),
        Text(
          user.name,
          textAlign: TextAlign.center,
          style: theme.textTheme.headlineSmall,
        ),
        const SizedBox(height: 4),
        Text(
          user.email,
          textAlign: TextAlign.center,
          style: theme.textTheme.bodyMedium?.copyWith(
            color: theme.colorScheme.outline,
          ),
        ),
        const SizedBox(height: 28),
        Card(
          child: ListTile(
            leading: const Icon(Icons.receipt_long_outlined),
            title: const Text('Siparişlerim'),
            subtitle: const Text('Siparişlerinizi ve durumlarını görüntüleyin'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const OrdersPage()),
            ),
          ),
        ),
        const SizedBox(height: 12),
        Card(
          child: ListTile(
            leading: const Icon(Icons.favorite_border),
            title: const Text('Favorilerim'),
            subtitle: const Text('Kaydettiğiniz ürünler'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const WishlistPage()),
            ),
          ),
        ),
        const SizedBox(height: 12),
        Card(
          child: ListTile(
            leading: const Icon(Icons.notifications_none),
            title: const Text('Bildirimler'),
            subtitle: const Text('Sipariş ve hesap bildirimleri'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () => Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const NotificationsPage()),
            ),
          ),
        ),
        const SizedBox(height: 12),
        OutlinedButton.icon(
          onPressed: () async {
            await auth.logout();
            await cart.load();
          },
          icon: const Icon(Icons.logout),
          label: const Text('Çıkış Yap'),
        ),
      ],
    );
  }
}

class _GuestAccount extends StatelessWidget {
  final VoidCallback onLogin;
  final VoidCallback onRegister;

  const _GuestAccount({required this.onLogin, required this.onRegister});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.person_outline,
              size: 72,
              color: theme.colorScheme.outline,
            ),
            const SizedBox(height: 16),
            Text(
              'Hesabınıza giriş yapın',
              style: theme.textTheme.headlineSmall,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'Siparişlerinizi takip edin ve daha hızlı alışveriş yapın.',
              style: theme.textTheme.bodyMedium?.copyWith(
                color: theme.colorScheme.outline,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              child: FilledButton(
                onPressed: onLogin,
                child: const Text('Giriş Yap'),
              ),
            ),
            const SizedBox(height: 10),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton(
                onPressed: onRegister,
                child: const Text('Kayıt Ol'),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
