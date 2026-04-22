import 'package:flutter/material.dart';

import '../models/cart_item.dart';
import '../models/product.dart';

class ShopController extends ChangeNotifier {
  final List<Product> _products = <Product>[
    Product(
      id: 1,
      title: 'Melhafa Safran',
      description: 'Etoffe traditionnelle aux teintes du desert.',
      price: 2800,
      discountPercent: 10,
      stock: 8,
      icon: '🧣',
      category: 'Textile',
    ),
    Product(
      id: 2,
      title: 'Vannerie d oasis',
      description: 'Corbeille artisanale en fibres naturelles.',
      price: 1500,
      discountPercent: 0,
      stock: 12,
      icon: '🧺',
      category: 'Deco',
    ),
    Product(
      id: 3,
      title: 'Theiere en cuivre',
      description: 'Piece marquee au marteau par un artisan local.',
      price: 4200,
      discountPercent: 15,
      stock: 6,
      icon: '🫖',
      category: 'Cuisine',
    ),
    Product(
      id: 4,
      title: 'Collier saharien',
      description: 'Bijou inspire des motifs mauritaniens.',
      price: 2200,
      discountPercent: 5,
      stock: 10,
      icon: '📿',
      category: 'Bijoux',
    ),
  ];

  final Map<int, int> _cart = <int, int>{};
  int _nextId = 5;

  List<Product> get products => List<Product>.unmodifiable(_products);

  int get cartCount => _cart.values.fold(0, (sum, qty) => sum + qty);

  List<CartItem> get cartItems {
    return _products
        .where((p) => (_cart[p.id] ?? 0) > 0)
        .map((p) => CartItem(product: p, quantity: _cart[p.id] ?? 0))
        .toList();
  }

  double get totalPrice =>
      cartItems.fold(0, (sum, item) => sum + item.linePrice);

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

  String placeOrder({
    required String name,
    required String phone,
    required String address,
  }) {
    if (cartItems.isEmpty) {
      return 'Votre panier est vide.';
    }
    if (name.trim().isEmpty || phone.trim().isEmpty || address.trim().isEmpty) {
      return 'Veuillez remplir tous les champs.';
    }
    _cart.clear();
    notifyListeners();
    return 'Merci $name, votre commande est enregistree.';
  }

  String addProduct({
    required String title,
    required String description,
    required double price,
    required int discountPercent,
    required int stock,
    required String icon,
    required String category,
  }) {
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

    _products.insert(
      0,
      Product(
        id: _nextId++,
        title: normalizedTitle,
        description: normalizedDescription,
        price: price,
        discountPercent: discountPercent,
        stock: stock,
        icon: icon.trim().isEmpty ? '🧵' : icon.trim(),
        category: category.trim().isEmpty ? 'Artisanat' : category.trim(),
      ),
    );
    notifyListeners();
    return 'Produit ajoute avec succes.';
  }
}
