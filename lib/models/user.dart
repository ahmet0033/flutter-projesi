class User {
  final int id;
  final String name;
  final String email;
  final List<String> roles;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.roles = const [],
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      roles:
          (json['roles'] as List?)?.map((role) => role.toString()).toList() ??
          const [],
    );
  }
}
