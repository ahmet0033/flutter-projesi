import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';

import 'models/cart_model.dart';
import 'pages/account_page.dart';
import 'pages/cart_page.dart';
import 'pages/home_page.dart';
import 'services/auth_controller.dart';
import 'services/auth_scope.dart';
import 'theme/redeast_theme.dart';

void main() {
  runApp(const MainApp());
}

class MainApp extends StatefulWidget {
  const MainApp({super.key});

  @override
  State<MainApp> createState() => _MainAppState();
}

class _MainAppState extends State<MainApp> {
  final CartModel _cart = CartModel();
  final AuthController _auth = AuthController();
  int _currentIndex = 0;

  List<Widget> get _pages => [
    const HomePage(),
    CartPage(onSwitchToHome: () => setState(() => _currentIndex = 0)),
    const AccountPage(),
  ];

  @override
  void initState() {
    super.initState();
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    await _auth.initialize();
    await _cart.load();
  }

  @override
  void dispose() {
    _auth.dispose();
    _cart.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AuthScope(
      auth: _auth,
      child: CartModelScope(
        cart: _cart,
        child: MaterialApp(
          debugShowCheckedModeBanner: false,
          title: 'RedEast Store',
          localizationsDelegates: GlobalMaterialLocalizations.delegates,
          supportedLocales: const [Locale('tr'), Locale('en')],
          locale: const Locale('tr'),
          theme: RedEastTheme.light(),
          darkTheme: RedEastTheme.dark(),
          themeMode: ThemeMode.system,
          home: ListenableBuilder(
            listenable: _cart,
            builder: (context, _) => Scaffold(
              body: IndexedStack(index: _currentIndex, children: _pages),
              bottomNavigationBar: BottomNavigationBar(
                currentIndex: _currentIndex,
                onTap: (index) => setState(() => _currentIndex = index),
                type: BottomNavigationBarType.fixed,
                elevation: 0,
                selectedFontSize: 12,
                unselectedFontSize: 12,
                items: [
                  const BottomNavigationBarItem(
                    icon: Icon(Icons.home_outlined),
                    activeIcon: Icon(Icons.home),
                    label: 'Ana Sayfa',
                  ),
                  BottomNavigationBarItem(
                    icon: Badge(
                      isLabelVisible: _cart.totalItems > 0,
                      label: Text('${_cart.totalItems}'),
                      child: const Icon(Icons.shopping_bag_outlined),
                    ),
                    activeIcon: Badge(
                      isLabelVisible: _cart.totalItems > 0,
                      label: Text('${_cart.totalItems}'),
                      child: const Icon(Icons.shopping_bag),
                    ),
                    label: 'Sepet',
                  ),
                  const BottomNavigationBarItem(
                    icon: Icon(Icons.person_outline),
                    activeIcon: Icon(Icons.person),
                    label: 'Hesap',
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class CartModelScope extends InheritedNotifier<CartModel> {
  const CartModelScope({
    super.key,
    required CartModel cart,
    required super.child,
  }) : super(notifier: cart);

  static CartModel of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<CartModelScope>();
    assert(scope != null, 'No CartModelScope found in context');
    return scope!.notifier!;
  }
}
