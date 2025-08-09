import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router';
import { useAuth } from '@getmocha/users-service/react';
import { ArrowLeft, User, Heart, Calendar, Trophy, Filter } from 'lucide-react';
import Header from '@/react-app/components/Header';
import QuestCard from '@/react-app/components/QuestCard';
import { Quest } from '@/shared/types';

interface UserQuestProgress {
  quest: Quest;
  is_completed: boolean;
  is_liked: boolean;
  completed_at: string | null;
}

export default function UserProfile() {
  const { user, isPending } = useAuth();
  const navigate = useNavigate();
  const [userQuests, setUserQuests] = useState<UserQuestProgress[]>([]);
  const [loading, setLoading] = useState(true);
  const [filter, setFilter] = useState<'all' | 'completed' | 'liked'>('all');

  useEffect(() => {
    if (!isPending && !user) {
      navigate('/');
      return;
    }

    if (user) {
      fetchUserQuests();
    }
  }, [user, isPending, navigate]);

  const fetchUserQuests = async () => {
    try {
      setLoading(true);
      const response = await fetch('/api/users/me/quests');
      const result = await response.json();
      
      if (result.success) {
        setUserQuests(result.data);
      }
    } catch (error) {
      console.error('Failed to fetch user quests:', error);
    } finally {
      setLoading(false);
    }
  };

  if (isPending) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Header />
        <div className="flex items-center justify-center pt-20">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
        </div>
      </div>
    );
  }

  if (!user) {
    return null;
  }

  const filteredQuests = userQuests.filter(quest => {
    if (filter === 'completed') return quest.is_completed;
    if (filter === 'liked') return quest.is_liked;
    return true;
  });

  const completedCount = userQuests.filter(q => q.is_completed).length;
  const likedCount = userQuests.filter(q => q.is_liked).length;

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Header />
      
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <button
          onClick={() => navigate('/')}
          className="flex items-center text-orange-500 hover:text-orange-600 mb-6 transition-colors"
        >
          <ArrowLeft className="w-5 h-5 mr-2" />
          Назад к квестам
        </button>

        {/* User Info Card */}
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-8">
          <div className="flex items-center space-x-6">
            <div className="w-24 h-24 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
              {user.google_user_data.picture ? (
                <img 
                  src={user.google_user_data.picture} 
                  alt={user.google_user_data.name || user.email}
                  className="w-full h-full object-cover"
                />
              ) : (
                <User className="w-12 h-12 text-gray-400" />
              )}
            </div>
            
            <div className="flex-1">
              <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                {user.google_user_data.name || user.email}
              </h1>
              <p className="text-gray-600 dark:text-gray-400 mb-4">{user.email}</p>
              
              <div className="flex items-center space-x-6 text-sm">
                <div className="flex items-center text-gray-600 dark:text-gray-400">
                  <Calendar className="w-4 h-4 mr-2" />
                  Участник с {new Date(user.created_at).toLocaleDateString('ru-RU')}
                </div>
              </div>
            </div>
          </div>

          {/* Stats */}
          <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
            <div className="text-center">
              <div className="text-3xl font-bold text-orange-500">{userQuests.length}</div>
              <div className="text-gray-600 dark:text-gray-400">Всего квестов</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-green-500">{completedCount}</div>
              <div className="text-gray-600 dark:text-gray-400">Завершено</div>
            </div>
            <div className="text-center">
              <div className="text-3xl font-bold text-red-500">{likedCount}</div>
              <div className="text-gray-600 dark:text-gray-400">Понравилось</div>
            </div>
          </div>
        </div>

        {/* Filter Tabs */}
        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
          <div className="flex items-center space-x-1">
            <Filter className="w-5 h-5 text-orange-500 mr-3" />
            <button
              onClick={() => setFilter('all')}
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                filter === 'all' 
                  ? 'bg-orange-500 text-white' 
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
              }`}
            >
              Все квесты ({userQuests.length})
            </button>
            <button
              onClick={() => setFilter('completed')}
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                filter === 'completed' 
                  ? 'bg-orange-500 text-white' 
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
              }`}
            >
              <Trophy className="w-4 h-4 mr-1 inline" />
              Завершенные ({completedCount})
            </button>
            <button
              onClick={() => setFilter('liked')}
              className={`px-4 py-2 rounded-lg font-medium transition-colors ${
                filter === 'liked' 
                  ? 'bg-orange-500 text-white' 
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
              }`}
            >
              <Heart className="w-4 h-4 mr-1 inline" />
              Понравившиеся ({likedCount})
            </button>
          </div>
        </div>

        {/* Quests Grid */}
        {loading ? (
          <div className="flex items-center justify-center py-20">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
          </div>
        ) : filteredQuests.length > 0 ? (
          <div className="overflow-x-auto">
            <div className="flex space-x-6 pb-4" style={{ width: 'max-content' }}>
              {filteredQuests.map(({ quest, is_completed, is_liked }) => (
                <div key={quest.id} className="flex-none w-72 relative">
                  <QuestCard
                    quest={quest}
                    onClick={() => navigate(`/quest/${quest.id}`)}
                  />
                  {/* Status badges */}
                  <div className="absolute top-2 left-2 flex space-x-1">
                    {is_completed && (
                      <div className="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                        <Trophy className="w-3 h-3 inline mr-1" />
                        Завершен
                      </div>
                    )}
                    {is_liked && (
                      <div className="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                        <Heart className="w-3 h-3 inline mr-1 fill-current" />
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        ) : (
          <div className="text-center py-20">
            <div className="text-gray-500 dark:text-gray-400 text-lg">
              {filter === 'all' && 'У вас пока нет квестов'}
              {filter === 'completed' && 'У вас пока нет завершенных квестов'}
              {filter === 'liked' && 'У вас пока нет понравившихся квестов'}
            </div>
            <div className="text-gray-400 dark:text-gray-500 text-sm mt-2">
              Начните проходить квесты, чтобы они появились здесь!
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
