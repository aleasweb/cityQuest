import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:mobile/app/core/design/app_text_styles.dart';
import 'package:mobile/app/features/cities/application/city_controller.dart';

class CitySelectorSheet extends ConsumerWidget {
  const CitySelectorSheet({super.key});

  static Future<String?> show(BuildContext context) {
    return showModalBottomSheet<String>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
      ),
      builder: (context) => const CitySelectorSheet(),
    );
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final citiesState = ref.watch(citiesControllerProvider);
    final theme = Theme.of(context);

    return SafeArea(
      child: citiesState.when(
        data: (cities) {
          return Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              ListTile(
                title: const Text('Любой город'),
                onTap: () => Navigator.pop(context, ''),
              ),
              if (cities.isEmpty)
                const Padding(
                  padding: EdgeInsets.all(16.0),
                  child: Center(child: Text('Нет доступных городов')),
                )
              else
                Flexible(
                  child: ListView.builder(
                    shrinkWrap: true,
                    itemCount: cities.length,
                    itemBuilder: (context, index) {
                      final city = cities[index];
                      return ListTile(
                        title: Text(city.name),
                        onTap: () => Navigator.pop(context, city.id),
                      );
                    },
                  ),
                ),
            ],
          );
        },
        loading: () => const Padding(
          padding: EdgeInsets.all(24.0),
          child: Center(child: CircularProgressIndicator()),
        ),
        error: (err, st) => Padding(
          padding: const EdgeInsets.all(24.0),
          child: Center(
            child: Text(err.toString(), style: TextStyle(color: theme.colorScheme.error)),
          ),
        ),
      ),
    );
  }
}
