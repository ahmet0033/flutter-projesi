import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';

import '../theme/redeast_theme.dart';

class RedEastLogo extends StatelessWidget {
  final double height;
  final bool? light;

  const RedEastLogo({super.key, this.height = 28, this.light});

  @override
  Widget build(BuildContext context) {
    final isDark = light ?? Theme.of(context).brightness == Brightness.dark;

    return SvgPicture.asset(
      isDark
          ? 'assets/brand/logos/RedEast.white.svg'
          : 'assets/brand/logos/RedEast.black.svg',
      height: height,
    );
  }
}

class RedEastPageHeader extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Widget? trailing;

  const RedEastPageHeader({
    super.key,
    required this.title,
    this.subtitle,
    this.trailing,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: theme.textTheme.headlineLarge),
              if (subtitle != null) ...[
                const SizedBox(height: 6),
                Text(
                  subtitle!,
                  style: theme.textTheme.bodyMedium?.copyWith(
                    color: theme.colorScheme.outline,
                  ),
                ),
              ],
            ],
          ),
        ),
        ?trailing,
      ],
    );
  }
}

class RedEastSectionHeader extends StatelessWidget {
  final String title;
  final String? actionLabel;
  final VoidCallback? onAction;

  const RedEastSectionHeader({
    super.key,
    required this.title,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Text(title, style: Theme.of(context).textTheme.titleLarge),
        ),
        if (actionLabel != null)
          TextButton(onPressed: onAction, child: Text(actionLabel!)),
      ],
    );
  }
}

class RedEastSurface extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  final VoidCallback? onTap;
  final Color? color;

  const RedEastSurface({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.onTap,
    this.color,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final content = Container(
      padding: padding,
      decoration: BoxDecoration(
        color: color ?? theme.colorScheme.surface,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: theme.colorScheme.outline.withValues(alpha: 0.25),
        ),
      ),
      child: child,
    );

    if (onTap == null) {
      return content;
    }

    return Material(
      color: Colors.transparent,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        borderRadius: BorderRadius.circular(18),
        onTap: onTap,
        child: content,
      ),
    );
  }
}

class RedEastEmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  final String message;
  final String? actionLabel;
  final VoidCallback? onAction;

  const RedEastEmptyState({
    super.key,
    this.icon = Icons.inventory_2_outlined,
    required this.title,
    required this.message,
    this.actionLabel,
    this.onAction,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 64, color: theme.colorScheme.outline),
            const SizedBox(height: 16),
            Text(
              title,
              style: theme.textTheme.headlineSmall,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 6),
            Text(
              message,
              style: theme.textTheme.bodyMedium?.copyWith(
                color: theme.colorScheme.outline,
              ),
              textAlign: TextAlign.center,
            ),
            if (actionLabel != null) ...[
              const SizedBox(height: 20),
              FilledButton.tonal(
                onPressed: onAction,
                child: Text(actionLabel!),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class RedEastRating extends StatelessWidget {
  final double? rating;
  final int count;
  final double size;

  const RedEastRating({
    super.key,
    required this.rating,
    this.count = 0,
    this.size = 16,
  });

  @override
  Widget build(BuildContext context) {
    if (rating == null) {
      return const SizedBox.shrink();
    }

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(Icons.star, size: size, color: RedEastColors.warning),
        const SizedBox(width: 4),
        Text(
          '${rating!.toStringAsFixed(1)}${count > 0 ? ' ($count)' : ''}',
          style: Theme.of(
            context,
          ).textTheme.bodySmall?.copyWith(fontWeight: FontWeight.w600),
        ),
      ],
    );
  }
}

class RedEastStatusPill extends StatelessWidget {
  final String label;
  final Color? color;

  const RedEastStatusPill({super.key, required this.label, this.color});

  @override
  Widget build(BuildContext context) {
    final background = color ?? Theme.of(context).colorScheme.primary;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: background.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelSmall?.copyWith(
          color: background,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}

class RedEastQuantityStepper extends StatelessWidget {
  final int quantity;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;

  const RedEastQuantityStepper({
    super.key,
    required this.quantity,
    required this.onIncrement,
    required this.onDecrement,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Container(
      decoration: BoxDecoration(
        border: Border.all(
          color: theme.colorScheme.outline.withValues(alpha: 0.35),
        ),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          IconButton(
            onPressed: onDecrement,
            icon: const Icon(Icons.remove, size: 16),
            constraints: const BoxConstraints(minWidth: 34, minHeight: 34),
            padding: EdgeInsets.zero,
          ),
          SizedBox(
            width: 28,
            child: Text(
              '$quantity',
              textAlign: TextAlign.center,
              style: theme.textTheme.titleSmall,
            ),
          ),
          IconButton(
            onPressed: onIncrement,
            icon: const Icon(Icons.add, size: 16),
            constraints: const BoxConstraints(minWidth: 34, minHeight: 34),
            padding: EdgeInsets.zero,
          ),
        ],
      ),
    );
  }
}
