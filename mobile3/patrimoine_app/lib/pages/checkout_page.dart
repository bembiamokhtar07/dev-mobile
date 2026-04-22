import 'package:flutter/material.dart';

import '../models/cart_item.dart';

class CheckoutPage extends StatefulWidget {
  const CheckoutPage({
    super.key,
    required this.items,
    required this.totalPrice,
    required this.onPlaceOrder,
  });

  final List<CartItem> items;
  final double totalPrice;
  final void Function({
    required String name,
    required String phone,
    required String address,
  })
  onPlaceOrder;

  @override
  State<CheckoutPage> createState() => _CheckoutPageState();
}

class _CheckoutPageState extends State<CheckoutPage> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _addressController = TextEditingController();

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    _addressController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    if (widget.items.isEmpty) {
      return const Center(
        child: Text('Ajoutez des articles au panier avant de commander.'),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Recapitulatif'),
                const SizedBox(height: 8),
                ...widget.items.map(
                  (item) => Text(
                    '- ${item.product.title} x${item.quantity} '
                    '(${item.linePrice.toStringAsFixed(0)} MRU)',
                  ),
                ),
                const Divider(),
                Text(
                  'Total a payer: ${widget.totalPrice.toStringAsFixed(0)} MRU',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 10),
        TextField(
          controller: _nameController,
          decoration: const InputDecoration(labelText: 'Nom complet'),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: _phoneController,
          decoration: const InputDecoration(labelText: 'Telephone'),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: _addressController,
          decoration: const InputDecoration(labelText: 'Adresse'),
          minLines: 2,
          maxLines: 3,
        ),
        const SizedBox(height: 14),
        FilledButton(
          onPressed: () {
            widget.onPlaceOrder(
              name: _nameController.text,
              phone: _phoneController.text,
              address: _addressController.text,
            );
          },
          child: const Text('Confirmer la commande'),
        ),
      ],
    );
  }
}
