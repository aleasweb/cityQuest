import 'package:flutter_test/flutter_test.dart';
import 'package:mobile/app/features/progress/data/api/progress_api.dart';
import '../../../../helpers/test_api_client.dart';

void main() {
  group('ProgressApi Tests', () {
    test('getList should return parsed list of progress', () async {
      final progressResponse = {
        "data": [
          {
            "questId": "3db76d13-a3f8-4719-8534-0e03132b72f4",
            "status": "active",
            "currentStepNumber": 2,
            "totalSteps": 5,
            "startedAt": "2025-11-30 12:36:59"
          }
        ],
        "meta": {
          "total": 1
        }
      };

      final apiClient = TestApiClient.create({
        '/api/user/progress': progressResponse,
      });

      final progressApi = ProgressApi(apiClient);
      final result = await progressApi.getList();

      expect(result.length, 1);
      final progress = result.first;
      expect(progress.questId, "3db76d13-a3f8-4719-8534-0e03132b72f4");
      expect(progress.status, "active");
      expect(progress.currentStepNumber, 2);
    });

    test('checkStep should return StepCheckResponseDto', () async {
      final checkStepResponse = {
        "data": {
          "success": true,
          "distance": 15.5,
          "nextStepNumber": 3,
          "isQuestCompleted": false
        }
      };

      final apiClient = TestApiClient.create({
        '/api/user/progress/3db76d13-a3f8-4719-8534-0e03132b72f4/check': checkStepResponse,
      });

      final progressApi = ProgressApi(apiClient);
      final result = await progressApi.checkStep('3db76d13-a3f8-4719-8534-0e03132b72f4', 55.752121, 37.617664);

      expect(result.success, true);
      expect(result.distance, 15.5);
      expect(result.nextStepNumber, 3);
      expect(result.isQuestCompleted, false);
    });
  });
}
