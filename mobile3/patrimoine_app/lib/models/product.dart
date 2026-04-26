class Product {
  Product({
    required this.id,
    required this.title,
    required this.description,
    required this.imageUrl,
    required this.price,
    required this.discountPercent,
    required this.stock,
    this.icon = '🧵',
    this.category = 'Artisanat',
  });

  final int id;
  final String title;
  final String description;
  final String imageUrl;
  final double price;
  final int discountPercent;
  final int stock;
  final String icon;
  final String category;

  double get finalPrice => price * (1 - discountPercent / 100);

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      id: (json['id'] as num?)?.toInt() ?? 0,
      title: (json['title'] ?? '').toString(),
      description: (json['description'] ?? '').toString(),
      imageUrl: (json['image_url'] ?? '').toString(),
      price: (json['price'] as num?)?.toDouble() ?? 0,
      discountPercent: (json['discount_percent'] as num?)?.toInt() ?? 0,
      stock: (json['stock'] as num?)?.toInt() ?? 0,
      icon: (json['icon'] ?? '🧵').toString(),
      category: (json['category'] ?? 'Artisanat').toString(),
    );
  }
}
