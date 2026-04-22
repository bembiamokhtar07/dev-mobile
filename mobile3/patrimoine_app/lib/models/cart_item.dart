import 'product.dart';

class CartItem {
  const CartItem({required this.product, required this.quantity});

  final Product product;
  final int quantity;

  double get linePrice => product.finalPrice * quantity;
}
