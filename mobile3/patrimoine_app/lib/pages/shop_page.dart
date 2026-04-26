import 'package:flutter/material.dart';

import '../models/product.dart';
import '../widgets/product_card.dart';

class ShopPage extends StatelessWidget {
  const ShopPage({
    super.key,
    required this.products,
    required this.onAddToCart,
  });

  final List<Product> products;
  final void Function(Product product) onAddToCart;

  @override
  Widget build(BuildContext context) {
    final width = MediaQuery.of(context).size.width;
    final columns = width < 650 ? 2 : 3;
    return GridView.builder(
      padding: const EdgeInsets.all(14),
      itemCount: products.length,
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: columns,
        mainAxisSpacing: 8,
        crossAxisSpacing: 8,
        childAspectRatio: width < 650 ? 0.62 : 0.58,
      ),
      itemBuilder: (context, index) {
        final product = products[index];
        return ProductCard(product: product, onAdd: () => onAddToCart(product));
      },
    );
  }
}
