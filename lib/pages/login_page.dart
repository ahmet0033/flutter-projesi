import 'package:flutter/material.dart';

import '../main.dart' show CartModelScope;
import '../services/auth_scope.dart';
import '../widgets/redeast_ui.dart';
import 'register_page.dart';

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _submitting = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate() || _submitting) {
      return;
    }

    setState(() => _submitting = true);
    final auth = AuthScope.of(context);
    final cart = CartModelScope.of(context);
    final ok = await auth.login(
      _emailController.text.trim(),
      _passwordController.text,
    );

    if (!mounted) {
      return;
    }

    if (!ok) {
      setState(() => _submitting = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(auth.error ?? 'Giriş yapılamadı.')),
      );
      return;
    }

    await cart.mergeGuestCart();

    if (!mounted) {
      return;
    }

    Navigator.of(context).pop(true);
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthScope.of(context);

    return Scaffold(
      appBar: AppBar(title: const Text('Giriş Yap')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const Center(child: RedEastLogo(height: 30)),
            const SizedBox(height: 24),
            Text('Giriş Yap', style: Theme.of(context).textTheme.headlineLarge),
            const SizedBox(height: 4),
            Text(
              'Hesabınıza giriş yapın.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                color: Theme.of(context).colorScheme.outline,
              ),
            ),
            const SizedBox(height: 28),
            TextFormField(
              controller: _emailController,
              keyboardType: TextInputType.emailAddress,
              autofillHints: const [AutofillHints.email],
              decoration: const InputDecoration(labelText: 'E-posta'),
              validator: (value) => (value == null || !value.contains('@'))
                  ? 'Geçerli bir e-posta girin.'
                  : null,
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _passwordController,
              obscureText: true,
              autofillHints: const [AutofillHints.password],
              decoration: const InputDecoration(labelText: 'Şifre'),
              validator: (value) => (value == null || value.length < 8)
                  ? 'Şifre en az 8 karakter olmalı.'
                  : null,
            ),
            const SizedBox(height: 20),
            SizedBox(
              height: 52,
              child: FilledButton(
                onPressed: (_submitting || auth.loading) ? null : _submit,
                child: Text(_submitting ? 'Giriş yapılıyor…' : 'Giriş Yap'),
              ),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: _submitting
                  ? null
                  : () => Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(builder: (_) => const RegisterPage()),
                    ),
              child: const Text('Hesabınız yok mu? Kayıt olun'),
            ),
          ],
        ),
      ),
    );
  }
}
