import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/app/features/quests/data/api/quest_api.dart';
import 'package:mobile/app/features/quests/data/dto/quest_dto.dart';
import 'package:mobile/app/features/quests/domain/quest_difficulty.dart';
import '../../../../helpers/test_api_client.dart';

void main() {
  group('QuestApi Tests', () {
    test('getQuests should return parsed list from backend response', () async {
      // Пример ответа бэкенда из задачи
      final moscowQuestsResponse = {
        "data": [
          {
            "id": "3db76d13-a3f8-4719-8534-0e03132b72f4",
            "title": "Московский кремль",
            "description": "Ваша задача по фото найти объекты на территории московского кремля",
            "city": "Москва",
            "difficulty": "easy",
            "durationMinutes": 30,
            "distanceKm": 2.0,
            "imageUrl": "/s3/q6.webp",
            "author": "aleas",
            "likesCount": 5,
            "isPopular": true,
            "latitude": 55.752121,
            "longitude": 37.617664,
            "type": "linear",
            "createdAt": "2025-11-30 12:36:59",
            "updatedAt": "2025-11-30 12:36:59",
            "isLikedByCurrentUser": false
          }
        ],
        "meta": {
          "total": 1,
          "limit": 20,
          "offset": 0,
          "count": 1
        }
      };

      final apiClient = TestApiClient.create({
        '/api/quests': moscowQuestsResponse,
      });

      final questApi = QuestApi(apiClient);

      final result = await questApi.getQuests(city: 'moscow');

      expect(result.items.length, 1);
      expect(result.total, 1);
      
      final quest = result.items.first;
      expect(quest.id, "3db76d13-a3f8-4719-8534-0e03132b72f4");
      expect(quest.title, "Московский кремль");
      expect(quest.difficulty, QuestDifficulty.easy);
      expect(quest.authorName, "aleas");
    });

    test('getQuestById should return a single parsed quest', () async {
      final singleQuestResponse = {
        "data": {
          "id": "3db76d13-a3f8-4719-8534-0e03132b72f4",
          "title": "Московский кремль",
          "description": "Ваша задача по фото найти объекты на территории московского кремля",
          "city": "Москва",
          "difficulty": "easy",
          "durationMinutes": 30,
          "distanceKm": 2.0,
          "imageUrl": "/s3/q6.webp",
          "author": "aleas",
          "likesCount": 5,
          "isPopular": true,
          "latitude": 55.752121,
          "longitude": 37.617664,
          "type": "linear",
          "createdAt": "2025-11-30 12:36:59",
          "updatedAt": "2025-11-30 12:36:59",
          "isLikedByCurrentUser": false
        }
      };

      final apiClient = TestApiClient.create({
        '/api/quests/3db76d13-a3f8-4719-8534-0e03132b72f4': singleQuestResponse,
      });

      final questApi = QuestApi(apiClient);

      final result = await questApi.getQuestById('3db76d13-a3f8-4719-8534-0e03132b72f4');

      expect(result.id, "3db76d13-a3f8-4719-8534-0e03132b72f4");
      expect(result.title, "Московский кремль");
    });
  });
}
