import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Quest } from '@/shared/types';
import QuestCard from './QuestCard';

interface QuestSliderProps {
  quests: Quest[];
  onQuestClick: (quest: Quest) => void;
}

export default function QuestSlider({ quests, onQuestClick }: QuestSliderProps) {
  const [currentIndex, setCurrentIndex] = useState(0);
  const questsPerView = 3;
  const maxIndex = Math.max(0, quests.length - questsPerView);

  const goToPrevious = () => {
    setCurrentIndex(Math.max(0, currentIndex - 1));
  };

  const goToNext = () => {
    setCurrentIndex(Math.min(maxIndex, currentIndex + 1));
  };

  if (quests.length === 0) return null;

  return (
    <div className="relative">
      {/* Navigation arrows */}
      {currentIndex > 0 && (
        <button
          onClick={goToPrevious}
          className="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white dark:bg-gray-800 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-orange-500 hover:border-orange-500 transition-all duration-300 -ml-6"
        >
          <ChevronLeft className="w-6 h-6" />
        </button>
      )}
      
      {currentIndex < maxIndex && (
        <button
          onClick={goToNext}
          className="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-12 h-12 bg-white dark:bg-gray-800 rounded-full shadow-lg border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 hover:text-orange-500 hover:border-orange-500 transition-all duration-300 -mr-6"
        >
          <ChevronRight className="w-6 h-6" />
        </button>
      )}

      {/* Quest cards container */}
      <div className="overflow-hidden">
        <div 
          className="flex transition-transform duration-300 ease-in-out"
          style={{ 
            transform: `translateX(-${currentIndex * 410}px)`, // 400px карточка + 10px отступ
            gap: '10px'
          }}
        >
          {quests.map(quest => (
            <div 
              key={quest.id} 
              className="flex-none"
            >
              <QuestCard
                quest={quest}
                onClick={() => onQuestClick(quest)}
              />
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
