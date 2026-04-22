import 'package:flutter/material.dart';

import '../models/cart_item.dart';
import '../models/product.dart';

class CartPage extends StatelessWidget {
  const CartPage({
    super.key,
    required this.items,
    required this.totalPrice,
    required this.onUpdateQuantity,
  });

  final List<CartItem> items;
  final double totalPrice;
  final void Function(Product product, int quantity) onUpdateQuantity;

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const Center(child: Text('Votre panier est vide.'));
    }

    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        ...items.map((item) {
          return Card(
            child: ListTile(
              leading: CircleAvatar(child: Text(item.product.icon)),
              title: Text(item.product.title),
              subtitle: Text(
                '${item.product.finalPrice.toStringAsFixed(0)} MRU x '
                '${item.quantity} = ${item.linePrice.toStringAsFixed(0)} MRU',
              ),
              trailing: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  IconButton(
                    onPressed: () =>
                        onUpdateQuantity(item.product, item.quantity - 1),
                    icon: const Icon(Icons.remove_circle_outline),
                  ),
                  Text('${item.quantity}'),
                  IconButton(
                    onPressed: () =>
                        onUpdateQuantity(item.product, item.quantity + 1),
                    icon: const Icon(Icons.add_circle_outline),
                  ),
                ],
              ),
            ),
          );
        }),
        const SizedBox(height: 12),
        Text(
          'Total: ${totalPrice.toStringAsFixed(0)} MRU',
          style: Theme.of(
            context,
          ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w700),
        ),
      ],
    );
  }
}
