import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router';
import { ArrowLeft, Heart, Clock, MapPin, User, Route, Loader2, Pause, XCircle, Play } from 'lucide-react';
import { useQuest } from '@/react-app/hooks/useQuests';
import { useAuth } from '@/react-app/contexts/AuthContext';
import { api } from '@/shared/api';
import Header from '@/react-app/components/Header';
import Toast from '@/react-app/components/Toast';
import ActiveQuestModal from '@/react-app/components/ActiveQuestModal';

export default function QuestDetail() {
  const { id } = useParams<{ id: string }>();
  const navigate = useNavigate();
  const { quest, loading, error } = useQuest(id!);
  const { isAuthenticated } = useAuth();
  
  // Like state
  const [liked, setLiked] = useState(false);
  const [likesCount, setLikesCount] = useState(0);
  const [isLiking, setIsLiking] = useState(false);
  
  // Start quest state
  const [isStarting, setIsStarting] = useState(false);
  const [showActiveQuestModal, setShowActiveQuestModal] = useState(false);
  
  // Pause/Abandon quest state
  const [isPausing, setIsPausing] = useState(false);
  const [isAbandoning, setIsAbandoning] = useState(false);
  const [showAbandonConfirm, setShowAbandonConfirm] = useState(false);
  
  // Toast state
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null);

  // Sync likesCount and liked state with quest data
  useEffect(() => {
    if (quest) {
      setLikesCount(quest.likesCount);
      setLiked(quest.isLikedByCurrentUser ?? false);
    }
  }, [quest]);

  const showToast = (message: string, type: 'success' | 'error') => {
    setToast({ message, type });
  };

  const handleLike = async () => {
    if (!quest) return;
    
    if (!isAuthenticated) {
      showToast('Войдите, чтобы отметить квест', 'error');
      return;
    }

    // Проверяем, начат ли квест
    if (!quest.isStartedByCurrentUser) {
      showToast('Начните квест, чтобы поставить лайк', 'error');
      return;
    }

    // Optimistic update
    const wasLiked = liked;
    const previousCount = likesCount;
    setLiked(!liked);
    setLikesCount(liked ? likesCount - 1 : likesCount + 1);
    setIsLiking(true);

    try {
      const result = await api.toggleLike(quest.id);
      setLiked(result.liked);
      setLikesCount(result.likesCount);
    } catch (err: any) {
      // Rollback optimistic update
      setLiked(wasLiked);
      setLikesCount(previousCount);
      
      if (err?.message?.includes('401')) {
        showToast('Требуется авторизация', 'error');
      } else if (err?.message?.includes('403')) {
        showToast('Начните квест, чтобы поставить лайк', 'error');
      } else if (err?.message?.includes('404')) {
        showToast('Квест не найден', 'error');
      } else {
        showToast('Не удалось обновить лайк', 'error');
      }
    } finally {
      setIsLiking(false);
    }
  };

  const handleStartQuest = async () => {
    if (!quest) return;
    
    if (!isAuthenticated) {
      showToast('Войдите, чтобы начать квест', 'error');
      return;
    }

    setIsStarting(true);

    try {
      await api.startQuest(quest.id);
      showToast('Квест успешно запущен!', 'success');
      
      // Reload quest data to update status
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } catch (err: any) {
      if (err?.message?.includes('409')) {
        // Active quest conflict
        setShowActiveQuestModal(true);
      } else if (err?.message?.includes('401')) {
        showToast('Требуется авторизация', 'error');
      } else if (err?.message?.includes('404')) {
        showToast('Квест не найден', 'error');
      } else {
        showToast('Не удалось запустить квест', 'error');
      }
    } finally {
      setIsStarting(false);
    }
  };

  const handlePauseQuest = async () => {
    if (!quest) return;
    
    setIsPausing(true);

    try {
      await api.pauseQuest(quest.id);
      showToast('Квест поставлен на паузу', 'success');
      
      // Reload quest data to update status
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } catch (err: any) {
      if (err?.message?.includes('401')) {
        showToast('Требуется авторизация', 'error');
      } else if (err?.message?.includes('404')) {
        showToast('Квест не найден', 'error');
      } else {
        showToast('Не удалось приостановить квест', 'error');
      }
    } finally {
      setIsPausing(false);
    }
  };

  const handleAbandonQuest = async () => {
    if (!quest) return;
    
    setIsAbandoning(true);

    try {
      await api.abandonQuest(quest.id);
      showToast('Вы отказались от квеста', 'success');
      
      // Redirect to home after abandoning
      setTimeout(() => {
        navigate('/');
      }, 1000);
    } catch (err: any) {
      if (err?.message?.includes('401')) {
        showToast('Требуется авторизация', 'error');
      } else if (err?.message?.includes('404')) {
        showToast('Квест не найден', 'error');
      } else {
        showToast('Не удалось отказаться от квеста', 'error');
      }
    } finally {
      setIsAbandoning(false);
      setShowAbandonConfirm(false);
    }
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
    'easy': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    'medium': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'hard': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
  };

  const difficultyLabels = {
    'easy': 'Легкие',
    'medium': 'Средние',
    'hard': 'Сложные'
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
            {quest.imageUrl ? (
              <>
            <img
                  src={quest.imageUrl}
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
              </>
            ) : (
              <div className="w-full h-full bg-gray-200 flex flex-col items-center justify-center">
                <div className="text-gray-400 text-center mb-6">
                  <div className="text-6xl mb-4">🖼️</div>
                  <div className="text-lg">Нет изображения</div>
                </div>
                <div className="bg-white/90 backdrop-blur-sm p-6 rounded-lg shadow-lg">
                  <h1 className="text-3xl font-bold text-gray-900 mb-2">{quest.title}</h1>
                  <div className="flex items-center text-gray-600">
                    <MapPin className="w-5 h-5 mr-2" />
                    {quest.city}
                  </div>
                </div>
              </div>
            )}
          </div>

          <div className="p-8">
            {/* Stats Row */}
            <div className="flex flex-wrap items-center gap-6 mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
              {isAuthenticated && (
                <div className="flex items-center">
                  <button
                    onClick={handleLike}
                    disabled={isLiking}
                    className={`flex items-center ${
                      liked ? 'text-red-500' : 'text-gray-500 hover:text-red-500'
                    } transition-all duration-200 ${
                      isLiking ? 'opacity-50 cursor-not-allowed' : ''
                    } ${liked ? 'transform scale-110' : ''}`}
                  >
                    {isLiking ? (
                      <Loader2 className="w-6 h-6 mr-2 animate-spin" />
                    ) : (
                      <Heart className={`w-6 h-6 mr-2 ${liked ? 'fill-current' : ''}`} />
                    )}
                    <span className="font-semibold text-lg">
                      {likesCount}
                    </span>
                  </button>
                </div>
              )}

              <span className={`px-4 py-2 rounded-full text-sm font-medium ${
                difficultyColors[quest.difficulty as keyof typeof difficultyColors] || 
                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
              }`}>
                {difficultyLabels[quest.difficulty as keyof typeof difficultyLabels] || quest.difficulty}
              </span>

              {quest.durationMinutes && (
                <div className="flex items-center text-gray-600 dark:text-gray-400">
                  <Clock className="w-5 h-5 mr-2" />
                  <span>{quest.durationMinutes} минут</span>
                </div>
              )}

              {quest.distanceKm && (
                <div className="flex items-center text-gray-600 dark:text-gray-400">
                  <Route className="w-5 h-5 mr-2" />
                  <span>{quest.distanceKm} км</span>
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

            {/* Quest Action Buttons */}
            {quest.questStatus === 'active' ? (
              // Active quest: Show Pause button only
              <div className="flex justify-center">
                <button
                  onClick={handlePauseQuest}
                  disabled={isPausing}
                  className={`bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-semibold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 ${
                    isPausing ? 'opacity-75 cursor-not-allowed' : ''
                  }`}
                >
                  {isPausing ? <Loader2 className="w-5 h-5 animate-spin" /> : <Pause className="w-5 h-5" />}
                  {isPausing ? 'Пауза...' : 'Поставить на паузу'}
                </button>
              </div>
            ) : quest.questStatus === 'paused' ? (
              // Paused quest: Show Resume and Abandon buttons
              <div className="flex justify-center gap-4">
                <button
                  onClick={handleStartQuest}
                  disabled={isStarting}
                  className={`bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 ${
                    isStarting ? 'opacity-75 cursor-not-allowed' : ''
                  }`}
                >
                  {isStarting ? <Loader2 className="w-5 h-5 animate-spin" /> : <Play className="w-5 h-5" />}
                  {isStarting ? 'Запуск...' : 'Продолжить квест'}
                </button>
                
                <button
                  onClick={() => setShowAbandonConfirm(true)}
                  disabled={isAbandoning}
                  className={`bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 ${
                    isAbandoning ? 'opacity-75 cursor-not-allowed' : ''
                  }`}
                >
                  {isAbandoning ? <Loader2 className="w-5 h-5 animate-spin" /> : <XCircle className="w-5 h-5" />}
                  {isAbandoning ? 'Отказ...' : 'Отказаться от квеста'}
                </button>
              </div>
            ) : (
              // Not started: Show Start button
              <div className="flex justify-center">
                <button
                  onClick={handleStartQuest}
                  disabled={isStarting}
                  className={`bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 flex items-center gap-2 ${
                    isStarting ? 'opacity-75 cursor-not-allowed' : ''
                  }`}
                >
                  {isStarting && <Loader2 className="w-5 h-5 animate-spin" />}
                  {isStarting ? 'Запуск...' : 'Начать квест'}
                </button>
              </div>
            )}
          </div>
        </div>
      </main>

      {/* Toast Notification */}
      {toast && (
        <Toast
          message={toast.message}
          type={toast.type}
          onClose={() => setToast(null)}
          duration={toast.type === 'success' ? 3000 : 5000}
        />
      )}

      {/* Active Quest Modal */}
      <ActiveQuestModal
        isOpen={showActiveQuestModal}
        onClose={() => setShowActiveQuestModal(false)}
        activeQuestTitle="Текущий активный квест"
        onGoToQuest={() => {
          setShowActiveQuestModal(false);
          // In future: navigate to active quest
          showToast('Переход к активному квесту', 'success');
        }}
      />

      {/* Abandon Confirmation Modal */}
      {showAbandonConfirm && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-4">
              Подтвердите действие
            </h3>
            <p className="text-gray-600 dark:text-gray-300 mb-6">
              Вы уверены, что хотите отказаться от квеста? Весь прогресс будет удалён.
            </p>
            <div className="flex gap-3">
              <button
                onClick={() => setShowAbandonConfirm(false)}
                className="flex-1 px-4 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg font-medium hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                disabled={isAbandoning}
              >
                Отмена
              </button>
              <button
                onClick={handleAbandonQuest}
                className="flex-1 px-4 py-3 bg-red-500 text-white rounded-lg font-medium hover:bg-red-600 transition-colors flex items-center justify-center gap-2"
                disabled={isAbandoning}
              >
                {isAbandoning && <Loader2 className="w-4 h-4 animate-spin" />}
                {isAbandoning ? 'Удаление...' : 'Отказаться'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
