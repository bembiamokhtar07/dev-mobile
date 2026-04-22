import 'package:flutter/material.dart';

class HomePage extends StatelessWidget {
  const HomePage({super.key, required this.onOpenShop});

  final VoidCallback onOpenShop;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(22),
            gradient: const LinearGradient(
              colors: [Color(0xFF7A4E2E), Color(0xFFB98962)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'مرحبا بكم في معرض الأثريات الموريتانية',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'لمحة سريعة: هذا التطبيق يعرض منتجات تراثية موريتانية '
                'مميزة ويمكنكم اختيار ما يناسبكم ثم طلبه بسهولة.',
                style: TextStyle(color: Colors.white, height: 1.4),
              ),
              const SizedBox(height: 14),
              FilledButton(
                onPressed: onOpenShop,
                style: FilledButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: const Color(0xFF7A4E2E),
                ),
                child: const Text('الدخول إلى المعرض'),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
        const Card(
          child: ListTile(
            leading: Icon(Icons.record_voice_over),
            title: Text('تراث شفهي'),
            subtitle: Text('قصص وشعر يعكس الهوية الموريتانية الأصيلة.'),
          ),
        ),
        const Card(
          child: ListTile(
            leading: Icon(Icons.checkroom),
            title: Text('أزياء تقليدية'),
            subtitle: Text('ألوان ونقوش صحراوية بطابع أنيق.'),
          ),
        ),
        const Card(
          child: ListTile(
            leading: Icon(Icons.auto_awesome),
            title: Text('صناعة يدوية'),
            subtitle: Text('منتجات مصنوعة بعناية من حرفيين محليين.'),
          ),
        ),
      ],
    );
  }
}
