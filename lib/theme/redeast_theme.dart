import 'package:flutter/material.dart';

/// RedEast brand tokens. Source of truth: `.agents/design/brand.md`.
abstract final class RedEastColors {
  static const charcoal = Color(0xFF363636);
  static const gray = Color(0xFF9C9C9C);
  static const red = Color(0xFFED2839);
  static const paper = Color(0xFFF7F7F5);
  static const ink = Color(0xFF111111);
  static const white = Color(0xFFFFFFFF);
  static const success = Color(0xFF1F8A5B);
  static const warning = Color(0xFFB7791F);
  static const info = Color(0xFF2563EB);
}

abstract final class RedEastTheme {
  static ThemeData light() => _build(Brightness.light);

  static ThemeData dark() => _build(Brightness.dark);

  static ThemeData _build(Brightness brightness) {
    final isDark = brightness == Brightness.dark;
    final background = isDark ? RedEastColors.ink : RedEastColors.paper;
    final surface = isDark ? const Color(0xFF1C1C1C) : RedEastColors.white;
    final onSurface = isDark ? RedEastColors.white : RedEastColors.charcoal;

    final scheme =
        ColorScheme.fromSeed(
          seedColor: RedEastColors.charcoal,
          brightness: brightness,
        ).copyWith(
          primary: isDark ? RedEastColors.white : RedEastColors.charcoal,
          onPrimary: isDark ? RedEastColors.ink : RedEastColors.white,
          secondary: RedEastColors.red,
          onSecondary: RedEastColors.white,
          surface: surface,
          onSurface: onSurface,
          error: RedEastColors.red,
          onError: RedEastColors.white,
          outline: RedEastColors.gray,
        );

    final base = ThemeData(
      useMaterial3: true,
      brightness: brightness,
      colorScheme: scheme,
      scaffoldBackgroundColor: background,
      fontFamily: 'CabinetGrotesk',
    );

    final textTheme = base.textTheme
        .apply(bodyColor: onSurface, displayColor: onSurface)
        .copyWith(
          displayLarge: TextStyle(
            fontFamily: 'InstrumentSerif',
            fontSize: 56,
            height: 1,
            letterSpacing: -1.5,
            color: onSurface,
          ),
          displayMedium: TextStyle(
            fontFamily: 'InstrumentSerif',
            fontSize: 44,
            height: 1.05,
            letterSpacing: -1,
            color: onSurface,
          ),
          displaySmall: TextStyle(
            fontFamily: 'InstrumentSerif',
            fontSize: 36,
            height: 1.1,
            letterSpacing: -0.5,
            color: onSurface,
          ),
          headlineLarge: TextStyle(
            fontFamily: 'InstrumentSerif',
            fontSize: 32,
            height: 1.15,
            color: onSurface,
          ),
          headlineMedium: TextStyle(
            fontFamily: 'InstrumentSerif',
            fontSize: 28,
            height: 1.2,
            color: onSurface,
          ),
          headlineSmall: TextStyle(
            fontFamily: 'InstrumentSerif',
            fontSize: 24,
            height: 1.2,
            color: onSurface,
          ),
          titleLarge: TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontSize: 20,
            fontWeight: FontWeight.w700,
            color: onSurface,
          ),
          titleMedium: TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: onSurface,
          ),
          bodyLarge: TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontSize: 16,
            height: 1.5,
            color: onSurface,
          ),
          bodyMedium: TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontSize: 14,
            height: 1.45,
            color: onSurface,
          ),
          labelLarge: TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: onSurface,
          ),
        );

    final outline = RedEastColors.gray.withValues(alpha: 0.35);

    return base.copyWith(
      textTheme: textTheme,
      appBarTheme: AppBarTheme(
        backgroundColor: background,
        foregroundColor: onSurface,
        surfaceTintColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: TextStyle(
          fontFamily: 'CabinetGrotesk',
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: onSurface,
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: isDark
              ? RedEastColors.white
              : RedEastColors.charcoal,
          foregroundColor: isDark ? RedEastColors.ink : RedEastColors.white,
          minimumSize: const Size(0, 48),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          textStyle: const TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      bottomNavigationBarTheme: BottomNavigationBarThemeData(
        backgroundColor: surface,
        selectedItemColor: isDark
            ? RedEastColors.white
            : RedEastColors.charcoal,
        unselectedItemColor: RedEastColors.gray,
        type: BottomNavigationBarType.fixed,
        elevation: 0,
        selectedLabelStyle: const TextStyle(
          fontFamily: 'CabinetGrotesk',
          fontSize: 12,
          fontWeight: FontWeight.w600,
        ),
        unselectedLabelStyle: const TextStyle(
          fontFamily: 'CabinetGrotesk',
          fontSize: 12,
        ),
      ),
      chipTheme: ChipThemeData(
        backgroundColor: surface,
        selectedColor: scheme.primary,
        labelStyle: TextStyle(
          fontFamily: 'CabinetGrotesk',
          fontSize: 13,
          color: onSurface,
        ),
        secondaryLabelStyle: TextStyle(
          fontFamily: 'CabinetGrotesk',
          fontSize: 13,
          color: scheme.onPrimary,
        ),
        side: BorderSide(color: outline),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(999)),
      ),
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: isDark
              ? RedEastColors.white
              : RedEastColors.charcoal,
          textStyle: const TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: onSurface,
          side: BorderSide(color: outline),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          textStyle: const TextStyle(
            fontFamily: 'CabinetGrotesk',
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      dialogTheme: DialogThemeData(
        backgroundColor: surface,
        surfaceTintColor: Colors.transparent,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      ),
      bottomSheetTheme: BottomSheetThemeData(
        backgroundColor: surface,
        surfaceTintColor: Colors.transparent,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
      ),
      cardTheme: CardThemeData(
        color: surface,
        elevation: 0,
        margin: EdgeInsets.zero,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(color: outline),
        ),
      ),
      snackBarTheme: SnackBarThemeData(
        backgroundColor: RedEastColors.charcoal,
        contentTextStyle: const TextStyle(
          fontFamily: 'CabinetGrotesk',
          color: RedEastColors.white,
        ),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
      dividerTheme: DividerThemeData(color: outline, thickness: 1),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: surface,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: outline),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(color: outline),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide(
            color: isDark ? RedEastColors.white : RedEastColors.charcoal,
            width: 1.5,
          ),
        ),
      ),
    );
  }
}
