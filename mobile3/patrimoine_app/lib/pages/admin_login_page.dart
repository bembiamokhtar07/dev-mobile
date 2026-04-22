import 'package:flutter/material.dart';

class AdminLoginPage extends StatefulWidget {
  const AdminLoginPage({super.key, required this.onLoginSuccess});

  final bool Function(String username, String password) onLoginSuccess;

  @override
  State<AdminLoginPage> createState() => _AdminLoginPageState();
}

class _AdminLoginPageState extends State<AdminLoginPage> {
  final _userController = TextEditingController();
  final _passController = TextEditingController();

  @override
  void dispose() {
    _userController.dispose();
    _passController.dispose();
    super.dispose();
  }

  void _submit() {
    final ok = widget.onLoginSuccess(
      _userController.text.trim(),
      _passController.text.trim(),
    );

    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(ok ? 'تم تسجيل الدخول بنجاح' : 'بيانات الدخول غير صحيحة'),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 420),
        child: Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'تسجيل دخول الأدمن',
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'تجريبيا: المستخدم admin وكلمة المرور 1234',
                  style: Theme.of(context).textTheme.bodySmall,
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _userController,
                  decoration: const InputDecoration(labelText: 'اسم المستخدم'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _passController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'كلمة المرور'),
                ),
                const SizedBox(height: 14),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton(
                    onPressed: _submit,
                    child: const Text('دخول'),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
