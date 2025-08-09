import { useState } from 'react';
import { useParams, useNavigate } from 'react-router';
import { ArrowLeft, Heart, Clock, MapPin, User, Route } from 'lucide-react';
import { useQuest } from '@/react-app/hooks/useQuests';
import Header from '@/react-app/components/Header';

export default function QuestDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { quest, loading, error } = useQuest(id!);
  const [liked, setLiked] = useState(false);

  const handleLike = async () => {
    if (!liked && quest) {
      try {
        await fetch(`/api/quests/${quest.id}/like`, { method: 'POST' });
        setLiked(true);
      } catch (err) {
        console.error('Failed to like quest');
      }
    }
  };

  const handleStartQuest = () => {
    alert('Квест запущен! В реальном приложении здесь был бы переход к началу квеста.');
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Header />
        <div className="flex items-center justify-center pt-20">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-orange-500"></div>
        </div>
      </div>
    );
  }

  if (error || !quest) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Header />
        <div className="flex items-center justify-center pt-20">
          <div className="text-center">
            <div className="text-red-500 text-lg">Квест не найден</div>
            <button 
              onClick={() => navigate('/')} 
              className="mt-4 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
            >
              Вернуться к списку
            </button>
          </div>
        </div>
      </div>
    );
  }

  const difficultyColors = {
    'легкие': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'средние': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'сложные': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  };

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Header />
      
      <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <button
          onClick={() => navigate('/')}
          className="flex items-center text-orange-500 hover:text-orange-600 mb-6 transition-colors"
        >
          <ArrowLeft className="w-5 h-5 mr-2" />
          Назад к квестам
        </button>

        <div className="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
          {/* Hero Image */}
          <div className="relative h-96">
            <img
              src={quest.image_url || 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=1200'}
              alt={quest.title}
              className="w-full h-full object-cover"
            />
            <div className="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent" />
            <div className="absolute bottom-6 left-6 right-6">
              <h1 className="text-3xl font-bold text-white mb-2">{quest.title}</h1>
              <div className="flex items-center text-white/90">
                <MapPin className="w-5 h-5 mr-2" />
                {quest.city}
              </div>
            </div>
          </div>

          <div className="p-8">
            {/* Stats Row */}
            <div className="flex flex-wrap items-center gap-6 mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
              <div className="flex items-center">
                <button
                  onClick={handleLike}
                  className={`flex items-center ${liked ? 'text-red-500' : 'text-gray-500 hover:text-red-500'} transition-colors`}
                >
                  <Heart className={`w-6 h-6 mr-2 ${liked ? 'fill-current' : ''}`} />
                  <span className="font-semibold text-lg">
                    {quest.likes_count + (liked ? 1 : 0)}
                  </span>
                </button>
              </div>

              <span className={`px-4 py-2 rounded-full text-sm font-medium ${
                difficultyColors[quest.difficulty as keyof typeof difficultyColors] || 
                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
              }`}>
                {quest.difficulty}
              </span>

              {quest.duration_minutes && (
                <div className="flex items-center text-gray-600 dark:text-gray-400">
                  <Clock className="w-5 h-5 mr-2" />
                  <span>{quest.duration_minutes} минут</span>
                </div>
              )}

              {quest.distance_km && (
                <div className="flex items-center text-gray-600 dark:text-gray-400">
                  <Route className="w-5 h-5 mr-2" />
                  <span>{quest.distance_km} км</span>
                </div>
              )}

              {quest.author && (
                <div className="flex items-center text-gray-600 dark:text-gray-400">
                  <User className="w-5 h-5 mr-2" />
                  <span>{quest.author}</span>
                </div>
              )}
            </div>

            {/* Description */}
            <div className="mb-8">
              <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Описание квеста
              </h2>
              <p className="text-gray-600 dark:text-gray-300 leading-relaxed">
                {quest.description || 'Описание отсутствует'}
              </p>
            </div>

            {/* Start Quest Button */}
            <div className="flex justify-center">
              <button
                onClick={handleStartQuest}
                className="bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
              >
                Начать квест
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
