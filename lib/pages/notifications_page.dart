import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

import '../services/api_client.dart';
import '../services/auth_scope.dart';

class NotificationsPage extends StatefulWidget {
  const NotificationsPage({super.key});

  @override
  State<NotificationsPage> createState() => _NotificationsPageState();
}

class _NotificationsPageState extends State<NotificationsPage> {
  late Future<List<Map<String, dynamic>>> _notificationsFuture;

  @override
  void initState() {
    super.initState();
    _notificationsFuture = _load();
  }

  Future<List<Map<String, dynamic>>> _load() async {
    final response = await ApiClient.instance.get('/notifications');
    final data = response is Map ? response['data'] : null;

    if (data is! List) {
      return const [];
    }

    return data
        .whereType<Map>()
        .map((item) => item.cast<String, dynamic>())
        .toList();
  }

  Future<void> _reload() async {
    setState(() => _notificationsFuture = _load());
    await _notificationsFuture;
  }

  Future<void> _markRead(Map<String, dynamic> notification) async {
    final id = notification['id']?.toString();

    if (id == null) {
      return;
    }

    try {
      await ApiClient.instance.post('/notifications/$id/read');
      await _reload();
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.toString())));
    }
  }

  Future<void> _markAllRead() async {
    try {
      await ApiClient.instance.post('/notifications/read-all');
      await _reload();
    } catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.toString())));
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthScope.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Bildirimler'),
        actions: [
          if (auth.isAuthenticated)
            TextButton(
              onPressed: _markAllRead,
              child: const Text('Tümünü oku'),
            ),
        ],
      ),
      body: !auth.isAuthenticated
          ? const Center(child: Text('Bildirimler için giriş yapın.'))
          : FutureBuilder<List<Map<String, dynamic>>>(
              future: _notificationsFuture,
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

                final notifications = snapshot.data ?? const [];

                if (notifications.isEmpty) {
                  return const Center(child: Text('Bildiriminiz yok.'));
                }

                return RefreshIndicator(
                  onRefresh: _reload,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: notifications.length,
                    separatorBuilder: (_, _) => const SizedBox(height: 10),
                    itemBuilder: (context, index) {
                      final item = notifications[index];
                      final data = item['data'] is Map
                          ? (item['data'] as Map).cast<String, dynamic>()
                          : <String, dynamic>{};
                      final isUnread = item['read_at'] == null;
                      final createdAt = DateTime.tryParse(
                        item['created_at']?.toString() ?? '',
                      );

                      return Card(
                        child: ListTile(
                          leading: Icon(
                            isUnread
                                ? Icons.notifications_active
                                : Icons.notifications_none,
                            color: isUnread
                                ? Theme.of(context).colorScheme.secondary
                                : Theme.of(context).colorScheme.outline,
                          ),
                          title: Text(
                            data['title']?.toString() ?? 'Bildirim',
                            style: TextStyle(
                              fontWeight: isUnread
                                  ? FontWeight.w700
                                  : FontWeight.w500,
                            ),
                          ),
                          subtitle: Text(data['body']?.toString() ?? ''),
                          trailing: createdAt == null
                              ? null
                              : Text(
                                  DateFormat('dd.MM HH:mm').format(createdAt),
                                  style: Theme.of(context).textTheme.bodySmall
                                      ?.copyWith(
                                        color: Theme.of(
                                          context,
                                        ).colorScheme.outline,
                                      ),
                                ),
                          onTap: isUnread ? () => _markRead(item) : null,
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
