import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/cart_item.dart';
import '../models/product.dart';

class ApiClient {
  ApiClient({
    this.baseUrl = 'http://10.0.2.2/mobile3/mobile2/mobile/api',
    http.Client? httpClient,
  }) : _httpClient = httpClient ?? http.Client();

  final String baseUrl;
  final http.Client _httpClient;

  Future<List<Product>> fetchProducts() async {
    final response = await _httpClient.get(Uri.parse('$baseUrl/products.php'));
    final data = _parseResponse(response);
    final list = (data['products'] as List<dynamic>? ?? const <dynamic>[]);
    return list
        .whereType<Map<String, dynamic>>()
        .map(Product.fromJson)
        .toList();
  }

  Future<String> loginAdmin({
    required String username,
    required String password,
  }) async {
    final response = await _httpClient.post(
      Uri.parse('$baseUrl/admin/login.php'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'username': username, 'password': password}),
    );
    final data = _parseResponse(response);
    final token = (data['token'] ?? '').toString();
    if (token.isEmpty) {
      throw Exception('Token admin introuvable.');
    }
    return token;
  }

  Future<String> createProduct({
    required String token,
    required String title,
    required String description,
    required String imageUrl,
    required double price,
    required int discountPercent,
    required int stock,
    required String category,
    required String icon,
  }) async {
    final response = await _httpClient.post(
      Uri.parse('$baseUrl/admin/products.php'),
      headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({
        'title': title,
        'description': description,
        'image_url': imageUrl,
        'price': price,
        'discount_percent': discountPercent,
        'stock': stock,
        'category': category,
        'icon': icon,
      }),
    );
    final data = _parseResponse(response);
    return (data['message'] ?? 'Produit ajoute.').toString();
  }

  Future<String> createOrder({
    required String customerName,
    required String customerPhone,
    required String customerAddress,
    required List<CartItem> items,
  }) async {
    final response = await _httpClient.post(
      Uri.parse('$baseUrl/orders.php'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'customer_name': customerName,
        'customer_phone': customerPhone,
        'customer_address': customerAddress,
        'items': items
            .map((item) => {
                  'product_id': item.product.id,
                  'quantity': item.quantity,
                })
            .toList(),
      }),
    );
    final data = _parseResponse(response);
    return (data['message'] ?? 'Commande enregistree.').toString();
  }

  Map<String, dynamic> _parseResponse(http.Response response) {
    final dynamic decoded = jsonDecode(response.body);
    final map = decoded is Map<String, dynamic>
        ? decoded
        : <String, dynamic>{'ok': false, 'message': 'Reponse invalide.'};

    final ok = map['ok'] == true;
    if (!ok) {
      throw Exception((map['message'] ?? 'Erreur API').toString());
    }
    return map;
  }
}
