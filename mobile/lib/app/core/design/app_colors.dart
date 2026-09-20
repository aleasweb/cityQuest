import 'package:flutter/material.dart';

class AppColors {
  AppColors._();

  // Brand
  static const Color primary = Color(0xFFF28B2B);
  static const Color primaryLight = Color(0xFFFDECDA); // For badges/tints
  static const Color premiumTeal = Color(0xFF164A4A); // Brand accent

  
  // Backgrounds
  static const Color backgroundLight = Color(0xFFF7F6F2); // Warm off-white from design
  static const Color backgroundDark = Color(0xFF1C1C1E); // Dark mode equivalent

  // Welcome / splash screen warm backgrounds (matching city illustration)
  static const Color backgroundWarm = Color(0xFFF3F0EB);
  static const Color backgroundWarmEdge = Color(0xFFFDF9F8);
  
  // Surfaces (Cards, Bottom Sheets)
  static const Color surfaceLight = Colors.white;
  static const Color surfaceDark = Color(0xFF2C2C2E);

  // Typography
  static const Color textLight = Color(0xFF1D232A);
  static const Color textDark = Color(0xFFF9FAFB);
  
  static const Color textSecondaryLight = Color(0xFF8B8E99);
  static const Color textSecondaryDark = Color(0xFF9CA3AF);

  // Borders / Dividers
  static const Color borderLight = Color(0xFFE5E5EA);
  static const Color borderDark = Color(0xFF3A3A3C);

  // Status
  static const Color error = Color(0xFFFF453A);
  static const Color success = Color(0xFF34C759);
}
