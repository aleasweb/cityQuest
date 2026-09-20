import 'dart:io';
import 'package:dio/dio.dart';
import 'package:package_info_plus/package_info_plus.dart';

class PlatformInterceptor extends Interceptor {
  final String _platformHeader;

  PlatformInterceptor._(this._platformHeader);

  static Future<PlatformInterceptor> create() async {
    final packageInfo = await PackageInfo.fromPlatform();
    final version = packageInfo.version;

    final platform = Platform.isIOS
        ? 'ios/$version'
        : Platform.isAndroid
            ? 'android/$version'
            : 'unknown/$version';

    return PlatformInterceptor._(platform);
  }

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.headers['X-App-Platform'] = _platformHeader;
    super.onRequest(options, handler);
  }
}
