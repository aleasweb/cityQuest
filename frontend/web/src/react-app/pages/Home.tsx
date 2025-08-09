import { useState } from 'react';
import { useNavigate } from 'react-router';
import { Loader2, TrendingUp, Sparkles } from 'lucide-react';
import { QuestFilters } from '@/shared/types';
import { useQuests } from '@/react-app/hooks/useQuests';
import QuestCard from '@/react-app/components/QuestCard';
import QuestSlider from '@/react-app/components/QuestSlider';
import Filters from '@/react-app/components/Filters';
import Header from '@/react-app/components/Header';

export default function Home() {
  const [filters, setFilters] = useState<QuestFilters>({});
  const { quests, loading, error } = useQuests(filters);
  const navigate = useNavigate();

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
        <Header />
        <div className="flex items-center justify-center pt-20">
          <div className="text-center">
            <div className="text-red-500 text-lg">Ошибка загрузки квестов</div>
            <button 
              onClick={() => window.location.reload()} 
              className="mt-4 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-semibold rounded-lg shadow-lg hover:shadow-xl transition-all duration-300"
            >
              Попробовать снова
            </button>
          </div>
        </div>
      </div>
    );
  }

  const popularQuests = quests.filter(quest => quest.is_popular);
  const newQuests = quests.filter(quest => !quest.is_popular);

  return (
    <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
      <Header />
      
      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Filters filters={filters} onFiltersChange={setFilters} />
        
        {loading ? (
          <div className="flex items-center justify-center py-20">
            <div className="text-center">
              <Loader2 className="w-10 h-10 animate-spin text-orange-500 mx-auto mb-4" />
              <div className="text-gray-600 dark:text-gray-400">Загружаем квесты...</div>
            </div>
          </div>
        ) : (
          <>
            {/* Popular Quests Section */}
            {popularQuests.length > 0 && (
              <section className="mb-12">
                <div className="flex items-center mb-6">
                  <TrendingUp className="w-6 h-6 text-orange-500 mr-3" />
                  <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Популярные квесты
                  </h2>
                </div>
                <QuestSlider 
                  quests={popularQuests}
                  onQuestClick={(quest) => navigate(`/quest/${quest.id}`)}
                />
              </section>
            )}

            {/* New Quests Section */}
            {newQuests.length > 0 && (
              <section>
                <div className="flex items-center mb-6">
                  <Sparkles className="w-6 h-6 text-orange-500 mr-3" />
                  <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Новые квесты
                  </h2>
                </div>
                <QuestSlider 
                  quests={newQuests}
                  onQuestClick={(quest) => navigate(`/quest/${quest.id}`)}
                />
              </section>
            )}

            {quests.length === 0 && (
              <div className="text-center py-20">
                <div className="text-gray-500 dark:text-gray-400 text-lg">
                  Квесты не найдены
                </div>
                <div className="text-gray-400 dark:text-gray-500 text-sm mt-2">
                  Попробуйте изменить фильтры поиска
                </div>
              </div>
            )}
          </>
        )}
      </main>
    </div>
  );
}
