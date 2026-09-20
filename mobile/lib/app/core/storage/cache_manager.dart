import 'dart:convert';
import 'package:hive_ce_flutter/hive_ce_flutter.dart';

class CacheManager {
  static const String _citiesBox = 'cities_cache';
  static const String _profileBox = 'profile_cache';

  static const Duration _citiesTtl = Duration(hours: 1);

  static Future<void> init() async {
    await Hive.initFlutter();
    await Hive.openBox<String>(_citiesBox);
    await Hive.openBox<String>(_profileBox);
  }

  static Future<void> setCities(List<Map<String, dynamic>> cities) async {
    final box = Hive.box<String>(_citiesBox);
    final data = {
      'timestamp': DateTime.now().toIso8601String(),
      'data': cities,
    };
    await box.put('all', jsonEncode(data));
  }

  static List<Map<String, dynamic>>? getCities() {
    final box = Hive.box<String>(_citiesBox);
    final jsonStr = box.get('all');
    if (jsonStr == null) return null;

    try {
      final decoded = jsonDecode(jsonStr) as Map<String, dynamic>;
      final timestamp = DateTime.parse(decoded['timestamp'] as String);
      final data = (decoded['data'] as List<dynamic>)
          .cast<Map<String, dynamic>>();

      if (DateTime.now().difference(timestamp) > _citiesTtl) {
        return null;
      }
      return data;
    } catch (_) {
      return null;
    }
  }

  static Future<void> setProfile(Map<String, dynamic> profile) async {
    final box = Hive.box<String>(_profileBox);
    await box.put('me', jsonEncode(profile));
  }

  static Map<String, dynamic>? getProfile() {
    final box = Hive.box<String>(_profileBox);
    final jsonStr = box.get('me');
    if (jsonStr == null) return null;

    try {
      return jsonDecode(jsonStr) as Map<String, dynamic>;
    } catch (_) {
      return null;
    }
  }

  static Future<void> clearAll() async {
    await Hive.box<String>(_citiesBox).clear();
    await Hive.box<String>(_profileBox).clear();
  }
}
