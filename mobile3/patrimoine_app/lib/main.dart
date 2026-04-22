import 'package:flutter/material.dart';

import 'controllers/shop_controller.dart';
import 'models/product.dart';
import 'pages/admin_page.dart';
import 'pages/admin_login_page.dart';
import 'pages/cart_page.dart';
import 'pages/checkout_page.dart';
import 'pages/home_page.dart';
import 'pages/shop_page.dart';

void main() {
  runApp(const PatrimoineApp());
}

class PatrimoineApp extends StatelessWidget {
  const PatrimoineApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Patrimoine Mauritanien',
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF7A4E2E)),
        scaffoldBackgroundColor: const Color(0xFFFAF7F3),
        appBarTheme: const AppBarTheme(centerTitle: true),
        cardTheme: CardThemeData(
          elevation: 1.2,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
      home: const ShopRootPage(),
    );
  }
}

class ShopRootPage extends StatefulWidget {
  const ShopRootPage({super.key});

  @override
  State<ShopRootPage> createState() => _ShopRootPageState();
}

class _ShopRootPageState extends State<ShopRootPage> {
  final ShopController _controller = ShopController();
  int _selectedTab = 0;
  bool _isAdminLoggedIn = false;

  static const String _adminUser = 'admin';
  static const String _adminPass = '1234';

  void _showMessage(String message) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  void _addToCart(Product product) {
    _showMessage(_controller.addToCart(product));
  }

  bool _handleAdminLogin(String username, String password) {
    final ok = username == _adminUser && password == _adminPass;
    if (ok) {
      setState(() => _isAdminLoggedIn = true);
    }
    return ok;
  }

  void _placeOrder({
    required String name,
    required String phone,
    required String address,
  }) {
    _showMessage(
      _controller.placeOrder(name: name, phone: phone, address: address),
    );
    if (_controller.cartItems.isEmpty) {
      setState(() => _selectedTab = 1);
    }
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, _) {
        final pages = <Widget>[
          HomePage(onOpenShop: () => setState(() => _selectedTab = 1)),
          ShopPage(products: _controller.products, onAddToCart: _addToCart),
          CartPage(
            items: _controller.cartItems,
            totalPrice: _controller.totalPrice,
            onUpdateQuantity: _controller.updateQuantity,
          ),
          CheckoutPage(
            items: _controller.cartItems,
            totalPrice: _controller.totalPrice,
            onPlaceOrder: _placeOrder,
          ),
          _isAdminLoggedIn
              ? AdminPage(
                  onAddProduct:
                      ({
                        required String title,
                        required String description,
                        required double price,
                        required int discountPercent,
                        required int stock,
                        required String icon,
                        required String category,
                      }) {
                        final message = _controller.addProduct(
                          title: title,
                          description: description,
                          price: price,
                          discountPercent: discountPercent,
                          stock: stock,
                          icon: icon,
                          category: category,
                        );
                        return message;
                      },
                )
              : AdminLoginPage(onLoginSuccess: _handleAdminLogin),
        ];

        return Scaffold(
          appBar: AppBar(
            title: const Text('Patrimoine Mauritanien'),
            actions: [
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 10),
                child: Chip(
                  label: Text('السلة: ${_controller.cartCount}'),
                  avatar: const Icon(Icons.shopping_cart_outlined),
                ),
              ),
              if (_isAdminLoggedIn)
                IconButton(
                  tooltip: 'تسجيل خروج الأدمن',
                  onPressed: () {
                    setState(() {
                      _isAdminLoggedIn = false;
                    });
                    _showMessage('تم تسجيل الخروج من الأدمن.');
                  },
                  icon: const Icon(Icons.logout),
                ),
            ],
          ),
          body: pages[_selectedTab],
          bottomNavigationBar: NavigationBar(
            selectedIndex: _selectedTab,
            onDestinationSelected: (index) =>
                setState(() => _selectedTab = index),
            destinations: const [
              NavigationDestination(icon: Icon(Icons.home), label: 'ترحيب'),
              NavigationDestination(icon: Icon(Icons.store), label: 'المعرض'),
              NavigationDestination(
                icon: Icon(Icons.shopping_bag),
                label: 'السلة',
              ),
              NavigationDestination(icon: Icon(Icons.payment), label: 'الطلب'),
              NavigationDestination(
                icon: Icon(Icons.admin_panel_settings),
                label: 'Admin',
              ),
            ],
          ),
        );
      },
    );
  }
}
