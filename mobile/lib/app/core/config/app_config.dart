import 'dart:io';
import 'package:flutter/foundation.dart';

class AppConfig {
  static String get apiBaseUrl {
    const envUrl = String.fromEnvironment('API_BASE_URL');
    if (envUrl.isNotEmpty) return envUrl;
    
    if (!kIsWeb && Platform.isAndroid) {
      return 'http://10.0.2.2';
    }
    return 'http://localhost';
  }

  static String get apiHostHeader {
    const envHeader = String.fromEnvironment('API_HOST_HEADER');
    if (envHeader.isNotEmpty) return envHeader;
    
    return 'cityquest.test';
  }

  static bool get isDev => kDebugMode;
}
