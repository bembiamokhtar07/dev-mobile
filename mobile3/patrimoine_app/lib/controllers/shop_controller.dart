import 'package:flutter/material.dart';

import '../models/cart_item.dart';
import '../models/product.dart';
import '../services/api_client.dart';

class ShopController extends ChangeNotifier {
  ShopController({ApiClient? apiClient}) : _apiClient = apiClient ?? ApiClient();

  final ApiClient _apiClient;
  final List<Product> _products = <Product>[];

  final Map<int, int> _cart = <int, int>{};
  bool _isLoading = false;
  String? _lastError;
  String? _adminToken;

  List<Product> get products => List<Product>.unmodifiable(_products);
  bool get isLoading => _isLoading;
  String? get lastError => _lastError;

  int get cartCount => _cart.values.fold(0, (sum, qty) => sum + qty);

  List<CartItem> get cartItems {
    return _products
        .where((p) => (_cart[p.id] ?? 0) > 0)
        .map((p) => CartItem(product: p, quantity: _cart[p.id] ?? 0))
        .toList();
  }

  double get totalPrice =>
      cartItems.fold(0, (sum, item) => sum + item.linePrice);

  Future<void> loadProducts() async {
    _isLoading = true;
    _lastError = null;
    notifyListeners();
    try {
      final fetched = await _apiClient.fetchProducts();
      _products
        ..clear()
        ..addAll(fetched);
    } catch (e) {
      _lastError = e.toString().replaceFirst('Exception: ', '');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  String addToCart(Product product) {
    final current = _cart[product.id] ?? 0;
    if (current >= product.stock) {
      return 'Stock insuffisant pour ${product.title}.';
    }
    _cart[product.id] = current + 1;
    notifyListeners();
    return '${product.title} ajoute au panier.';
  }

  void updateQuantity(Product product, int quantity) {
    final safeQty = quantity.clamp(0, product.stock);
    if (safeQty == 0) {
      _cart.remove(product.id);
    } else {
      _cart[product.id] = safeQty;
    }
    notifyListeners();
  }

  Future<String> placeOrder({
    required String name,
    required String phone,
    required String address,
  }) async {
    if (cartItems.isEmpty) {
      return 'Votre panier est vide.';
    }
    if (name.trim().isEmpty || phone.trim().isEmpty || address.trim().isEmpty) {
      return 'Veuillez remplir tous les champs.';
    }
    try {
      final message = await _apiClient.createOrder(
        customerName: name.trim(),
        customerPhone: phone.trim(),
        customerAddress: address.trim(),
        items: cartItems,
      );
      _cart.clear();
      await loadProducts();
      return message;
    } catch (e) {
      return e.toString().replaceFirst('Exception: ', '');
    }
  }

  Future<String> addProduct({
    required String title,
    required String description,
    required String imageUrl,
    required double price,
    required int discountPercent,
    required int stock,
    required String icon,
    required String category,
  }) async {
    final normalizedTitle = title.trim();
    final normalizedDescription = description.trim();
    if (normalizedTitle.isEmpty || normalizedDescription.isEmpty) {
      return 'Titre et description obligatoires.';
    }
    if (price <= 0 ||
        stock < 0 ||
        discountPercent < 0 ||
        discountPercent > 90) {
      return 'Valeurs invalides pour prix/stock/remise.';
    }
    if (imageUrl.trim().isEmpty) {
      return 'URL image obligatoire.';
    }
    if (_adminToken == null) {
      return 'Session admin invalide. Reconnectez-vous.';
    }
    try {
      final message = await _apiClient.createProduct(
        token: _adminToken!,
        title: normalizedTitle,
        description: normalizedDescription,
        imageUrl: imageUrl.trim(),
        price: price,
        discountPercent: discountPercent,
        stock: stock,
        icon: icon.trim().isEmpty ? '🧵' : icon.trim(),
        category: category.trim().isEmpty ? 'Artisanat' : category.trim(),
      );
      await loadProducts();
      return message;
    } catch (e) {
      return e.toString().replaceFirst('Exception: ', '');
    }
  }

  Future<bool> loginAdmin(String username, String password) async {
    try {
      _adminToken = await _apiClient.loginAdmin(
        username: username.trim(),
        password: password.trim(),
      );
      return true;
    } catch (_) {
      return false;
    }
  }

  void logoutAdmin() {
    _adminToken = null;
  }
}
