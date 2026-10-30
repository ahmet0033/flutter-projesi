import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../models/product.dart';
import '../services/api_client.dart';
import '../services/auth_scope.dart';
import '../theme/redeast_theme.dart';

class ReviewsPage extends StatefulWidget {
  final Product product;

  const ReviewsPage({super.key, required this.product});

  @override
  State<ReviewsPage> createState() => _ReviewsPageState();
}

class _ReviewsPageState extends State<ReviewsPage> {
  late Future<List<Map<String, dynamic>>> _reviewsFuture;

  @override
  void initState() {
    super.initState();
    _reviewsFuture = _load();
  }

  Future<List<Map<String, dynamic>>> _load() async {
    final response = await ApiClient.instance.get(
      '/products/${widget.product.slug}/reviews',
    );
    final data = response is Map ? response['data'] : null;

    if (data is! List) {
      return const [];
    }

    return data
        .whereType<Map>()
        .map((review) => review.cast<String, dynamic>())
        .toList();
  }

  Future<void> _reload() async {
    setState(() => _reviewsFuture = _load());
    await _reviewsFuture;
  }

  Future<void> _writeReview() async {
    final formKey = GlobalKey<FormState>();
    final titleController = TextEditingController();
    final bodyController = TextEditingController();
    var rating = 5;

    final submitted = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Yorum yaz'),
        content: Form(
          key: formKey,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                DropdownButtonFormField<int>(
                  initialValue: rating,
                  decoration: const InputDecoration(labelText: 'Puan'),
                  items: const [
                    DropdownMenuItem(value: 5, child: Text('5 - Mükemmel')),
                    DropdownMenuItem(value: 4, child: Text('4 - İyi')),
                    DropdownMenuItem(value: 3, child: Text('3 - Orta')),
                    DropdownMenuItem(value: 2, child: Text('2 - Zayıf')),
                    DropdownMenuItem(value: 1, child: Text('1 - Kötü')),
                  ],
                  onChanged: (value) {
                    if (value != null) {
                      rating = value;
                    }
                  },
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: titleController,
                  decoration: const InputDecoration(labelText: 'Başlık'),
                ),
                const SizedBox(height: 10),
                TextFormField(
                  controller: bodyController,
                  maxLines: 4,
                  decoration: const InputDecoration(labelText: 'Yorumunuz'),
                  validator: (value) =>
                      (value == null || value.trim().length < 10)
                      ? 'En az 10 karakter yazın.'
                      : null,
                ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, false),
            child: const Text('Vazgeç'),
          ),
          FilledButton(
            onPressed: () {
              if (formKey.currentState!.validate()) {
                Navigator.pop(dialogContext, true);
              }
            },
            child: const Text('Gönder'),
          ),
        ],
      ),
    );

    if (submitted != true) {
      titleController.dispose();
      bodyController.dispose();
      return;
    }

    try {
      await ApiClient.instance.post(
        '/products/${widget.product.slug}/reviews',
        data: {
          'rating': rating,
          'title': titleController.text.trim(),
          'body': bodyController.text.trim(),
        },
      );

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Yorumunuz onay için gönderildi.')),
      );
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.toString())));
    } finally {
      titleController.dispose();
      bodyController.dispose();
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthScope.of(context);

    return Scaffold(
      appBar: AppBar(
        title: Text('${widget.product.name} yorumları'),
        actions: [
          if (auth.isAuthenticated)
            IconButton(
              tooltip: 'Yorum yaz',
              onPressed: _writeReview,
              icon: const Icon(Icons.rate_review_outlined),
            ),
        ],
      ),
      body: FutureBuilder<List<Map<String, dynamic>>>(
        future: _reviewsFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Text(
                  snapshot.error.toString(),
                  textAlign: TextAlign.center,
                ),
              ),
            );
          }

          final reviews = snapshot.data ?? const [];

          if (reviews.isEmpty) {
            return const Center(child: Text('Henüz onaylı yorum yok.'));
          }

          return RefreshIndicator(
            onRefresh: _reload,
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: reviews.length,
              separatorBuilder: (_, _) => const SizedBox(height: 10),
              itemBuilder: (context, index) {
                final review = reviews[index];
                final rating = (review['rating'] as num?)?.toInt() ?? 0;
                final createdAt = DateTime.tryParse(
                  review['created_at']?.toString() ?? '',
                );

                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(14),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Text(
                              review['user_name']?.toString() ?? 'Müşteri',
                              style: Theme.of(context).textTheme.titleMedium,
                            ),
                            const Spacer(),
                            Text(
                              '${'★' * rating}${'☆' * (5 - rating)}',
                              style: const TextStyle(
                                color: RedEastColors.warning,
                                fontSize: 14,
                              ),
                            ),
                          ],
                        ),
                        if (review['is_verified_purchase'] == true)
                          const Padding(
                            padding: EdgeInsets.only(top: 2),
                            child: Text(
                              'Doğrulanmış alışveriş',
                              style: TextStyle(
                                fontSize: 11,
                                color: RedEastColors.success,
                              ),
                            ),
                          ),
                        if (review['title'] != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            review['title'].toString(),
                            style: Theme.of(context).textTheme.titleSmall
                                ?.copyWith(fontWeight: FontWeight.w700),
                          ),
                        ],
                        const SizedBox(height: 6),
                        Text(review['body']?.toString() ?? ''),
                        if (createdAt != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            DateFormat('dd.MM.yyyy').format(createdAt),
                            style: Theme.of(context).textTheme.bodySmall
                                ?.copyWith(
                                  color: Theme.of(context).colorScheme.outline,
                                ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
