import 'package:flutter/material.dart';

class AdminPage extends StatefulWidget {
  const AdminPage({super.key, required this.onAddProduct});

  final String Function({
    required String title,
    required String description,
    required double price,
    required int discountPercent,
    required int stock,
    required String icon,
    required String category,
  })
  onAddProduct;

  @override
  State<AdminPage> createState() => _AdminPageState();
}

class _AdminPageState extends State<AdminPage> {
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _priceController = TextEditingController();
  final _discountController = TextEditingController(text: '0');
  final _stockController = TextEditingController(text: '1');
  final _iconController = TextEditingController(text: '🧵');
  final _categoryController = TextEditingController(text: 'Artisanat');

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _priceController.dispose();
    _discountController.dispose();
    _stockController.dispose();
    _iconController.dispose();
    _categoryController.dispose();
    super.dispose();
  }

  void _submit() {
    final price = double.tryParse(_priceController.text.trim()) ?? -1;
    final discount = int.tryParse(_discountController.text.trim()) ?? -1;
    final stock = int.tryParse(_stockController.text.trim()) ?? -1;

    final message = widget.onAddProduct(
      title: _titleController.text,
      description: _descriptionController.text,
      price: price,
      discountPercent: discount,
      stock: stock,
      icon: _iconController.text,
      category: _categoryController.text,
    );

    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));

    if (message.contains('succes')) {
      _titleController.clear();
      _descriptionController.clear();
      _priceController.clear();
      _discountController.text = '0';
      _stockController.text = '1';
      _iconController.text = '🧵';
      _categoryController.text = 'Artisanat';
      setState(() {});
    }
  }

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(14),
      children: [
        Card(
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Admin - Ajouter un produit',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _titleController,
                  decoration: const InputDecoration(labelText: 'Titre'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _descriptionController,
                  decoration: const InputDecoration(labelText: 'Description'),
                  minLines: 2,
                  maxLines: 3,
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _categoryController,
                  decoration: const InputDecoration(labelText: 'Categorie'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _iconController,
                  decoration: const InputDecoration(
                    labelText: 'Icon (emoji)',
                    hintText: 'Ex: 🧵',
                  ),
                ),
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _priceController,
                        decoration: const InputDecoration(labelText: 'Prix'),
                        keyboardType: TextInputType.number,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _discountController,
                        decoration: const InputDecoration(
                          labelText: 'Remise %',
                        ),
                        keyboardType: TextInputType.number,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: TextField(
                        controller: _stockController,
                        decoration: const InputDecoration(labelText: 'Stock'),
                        keyboardType: TextInputType.number,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: _submit,
                    icon: const Icon(Icons.add_box_outlined),
                    label: const Text('Ajouter le produit'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}
