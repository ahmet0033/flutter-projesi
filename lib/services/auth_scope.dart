import 'package:flutter/material.dart';

import 'auth_controller.dart';

class AuthScope extends InheritedNotifier<AuthController> {
  const AuthScope({
    super.key,
    required AuthController auth,
    required super.child,
  }) : super(notifier: auth);

  static AuthController of(BuildContext context) {
    final scope = context.dependOnInheritedWidgetOfExactType<AuthScope>();
    assert(scope != null, 'No AuthScope found in context');
    return scope!.notifier!;
  }
}
