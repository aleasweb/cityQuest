import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router';
import { useAuth } from '@/react-app/contexts/AuthContext';
import Header from '@/react-app/components/Header';
import { User, MapPin, Heart, Clock, Pause, CheckCircle2, Loader2 } from 'lucide-react';
import { api } from '@/shared/api';
import type { UserProfileWithHistory, QuestProgressItem } from '@/shared/types';

export default function UserProfile() {
  const { user, isAuthenticated, isLoading: authLoading } = useAuth();
  const navigate = useNavigate();
  const [profileData, setProfileData] = useState<UserProfileWithHistory | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Не редиректим пока идет проверка авторизации
    if (authLoading) {
      return;
    }

    if (!isAuthenticated) {
      navigate('/');
      return;
    }

    const loadProfileData = async () => {
      if (!user?.username) return;
      
      try {
        const data = await api.getProfileWithQuestHistory(user.username);
        setProfileData(data);
      } catch (error) {
        console.error('Failed to load profile:', error);
      } finally {
        setLoading(false);
      }
    };

    loadProfileData();
  }, [authLoading, isAuthenticated, navigate, user?.username]);

  if (authLoading || loading) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Header />
        <div className="flex items-center justify-center pt-20">
          <Loader2 className="w-12 h-12 animate-spin text-orange-500" />
        </div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Header />
      
      <div className="max-w-4xl mx-auto px-4 py-8">
        {/* User Info Card */}
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
          <div className="flex items-center space-x-4">
            <div className="w-20 h-20 rounded-full bg-orange-500 flex items-center justify-center">
              <User className="w-10 h-10 text-white" />
            </div>
            <div>
              <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                {user.username}
              </h1>
              <p className="text-gray-600 dark:text-gray-400">{user.email}</p>
            </div>
          </div>
        </div>

        {/* Stats */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div className="flex items-center space-x-3">
              <MapPin className="w-8 h-8 text-blue-500" />
              <div>
                <p className="text-2xl font-bold text-gray-900 dark:text-white">
                  {profileData?.completedQuests.length || 0}
                </p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Пройдено квестов</p>
              </div>
            </div>
          </div>

          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div className="flex items-center space-x-3">
              <Clock className="w-8 h-8 text-yellow-500" />
              <div>
                <p className="text-2xl font-bold text-gray-900 dark:text-white">
                  {profileData?.activeQuest ? 1 : 0}
                </p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Активных</p>
              </div>
            </div>
          </div>

          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div className="flex items-center space-x-3">
              <Heart className="w-8 h-8 text-red-500" />
              <div>
                <p className="text-2xl font-bold text-gray-900 dark:text-white">
                  {(profileData?.completedQuests.filter(q => q.isLiked).length || 0) +
                   (profileData?.pausedQuests.filter(q => q.isLiked).length || 0) +
                   (profileData?.activeQuest?.isLiked ? 1 : 0)}
                </p>
                <p className="text-sm text-gray-600 dark:text-gray-400">Понравилось</p>
              </div>
            </div>
          </div>
        </div>

        {/* Active Quest */}
        {profileData?.activeQuest && (
          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Clock className="w-6 h-6 text-orange-500" />
              Активный квест
            </h2>
            <QuestCard item={profileData.activeQuest} onQuestClick={() => navigate(`/quest/${profileData.activeQuest!.quest.id}`)} />
          </div>
        )}

        {/* Paused Quests */}
        {profileData && profileData.pausedQuests.length > 0 && (
          <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
            <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Pause className="w-6 h-6 text-blue-500" />
              Квесты на паузе
            </h2>
            <div className="space-y-3">
              {profileData.pausedQuests.map((item) => (
                <QuestCard key={item.quest.id} item={item} onQuestClick={() => navigate(`/quest/${item.quest.id}`)} />
              ))}
            </div>
          </div>
        )}

        {/* Completed Quests */}
        <div className="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
          <h2 className="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <CheckCircle2 className="w-6 h-6 text-green-500" />
            Пройденные квесты
          </h2>
          {profileData && profileData.completedQuests.length > 0 ? (
            <>
              <div className="space-y-3">
                {profileData.completedQuests.map((item) => (
                  <QuestCard key={item.quest.id} item={item} onQuestClick={() => navigate(`/quest/${item.quest.id}`)} />
                ))}
              </div>
              <button
                onClick={() => navigate('/progress')}
                className="mt-4 w-full py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              >
                Показать все квесты
              </button>
            </>
          ) : (
            <p className="text-gray-600 dark:text-gray-400">
              Вы еще не проходили квесты. Начните свое приключение!
            </p>
          )}
        </div>
      </div>
    </div>
  );
}

// Quest Card Component
function QuestCard({ item, onQuestClick }: { item: QuestProgressItem; onQuestClick: () => void }) {
  const difficultyColors = {
    'easy': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'medium': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'hard': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  };

  const statusIcons = {
    'active': <Clock className="w-5 h-5 text-orange-500" />,
    'paused': <Pause className="w-5 h-5 text-blue-500" />,
    'completed': <CheckCircle2 className="w-5 h-5 text-green-500" />
  };

  const statusLabels = {
    'active': 'Активен',
    'paused': 'На паузе',
    'completed': 'Завершён'
  };

  return (
    <div
      onClick={onQuestClick}
      className="flex items-center gap-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors cursor-pointer"
    >
      {/* Quest Image */}
      {item.quest.imageUrl ? (
        <img
          src={item.quest.imageUrl}
          alt={item.quest.title}
          className="w-20 h-20 rounded-lg object-cover"
        />
      ) : (
        <div className="w-20 h-20 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
          <MapPin className="w-8 h-8 text-gray-400" />
        </div>
      )}

      {/* Quest Info */}
      <div className="flex-1">
        <h3 className="font-semibold text-gray-900 dark:text-white mb-1">
          {item.quest.title}
        </h3>
        <div className="flex items-center gap-3 text-sm">
          <div className="flex items-center gap-1">
            {statusIcons[item.status]}
            <span className="text-gray-600 dark:text-gray-400">
              {statusLabels[item.status]}
            </span>
          </div>
          {item.quest.difficulty && (
            <span className={`px-2 py-1 rounded text-xs ${difficultyColors[item.quest.difficulty as keyof typeof difficultyColors]}`}>
              {item.quest.difficulty}
            </span>
          )}
          {item.isLiked && (
            <Heart className="w-4 h-4 text-red-500 fill-current" />
          )}
        </div>
        {item.completedAt && (
          <p className="text-xs text-gray-500 dark:text-gray-500 mt-1">
            Завершён: {new Date(item.completedAt).toLocaleDateString('ru-RU')}
          </p>
        )}
      </div>
    </div>
  );
}
