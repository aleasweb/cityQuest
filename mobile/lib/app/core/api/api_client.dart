import 'package:dio/dio.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart';
import 'package:cookie_jar/cookie_jar.dart';
import 'package:path_provider/path_provider.dart';
import 'package:mobile/app/core/config/app_config.dart';
import 'package:mobile/app/core/api/envelope_interceptor.dart';
import 'package:mobile/app/core/api/platform_interceptor.dart';

class ApiClient {
  final Dio _dio;
  final PersistCookieJar? _cookieJar;

  ApiClient._(this._dio, this._cookieJar);

  /// Конструктор для тестов
  ApiClient.forTest(this._dio) : _cookieJar = null;

  static Future<ApiClient> create() async {
    final headers = <String, dynamic>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };

    if (AppConfig.apiHostHeader.isNotEmpty) {
      headers['Host'] = AppConfig.apiHostHeader;
    }

    final dio = Dio(
      BaseOptions(
        baseUrl: AppConfig.apiBaseUrl,
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 10),
        headers: headers,
      ),
    );

    final appDocDir = await getApplicationDocumentsDirectory();
    final cookieJar = PersistCookieJar(
      ignoreExpires: true,
      storage: FileStorage('${appDocDir.path}/.cookies/'),
    );
    dio.interceptors.add(CookieManager(cookieJar));

    final platformInterceptor = await PlatformInterceptor.create();

    dio.interceptors.addAll([
      platformInterceptor,
      EnvelopeInterceptor(),
      if (AppConfig.isDev) LogInterceptor(responseBody: true, requestBody: true),
    ]);

    return ApiClient._(dio, cookieJar);
  }

  Dio get client => _dio;

  Future<void> clearCookies() async {
    await _cookieJar?.deleteAll();
  }
}
