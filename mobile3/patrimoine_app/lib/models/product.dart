class Product {
  Product({
    required this.id,
    required this.title,
    required this.description,
    required this.price,
    required this.discountPercent,
    required this.stock,
    this.icon = '🧵',
    this.category = 'Artisanat',
  });

  final int id;
  final String title;
  final String description;
  final double price;
  final int discountPercent;
  final int stock;
  final String icon;
  final String category;

  double get finalPrice => price * (1 - discountPercent / 100);
}
