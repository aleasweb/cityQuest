import { Heart, Clock, MapPin } from 'lucide-react';
import { Quest } from '@/shared/types';

interface QuestCardProps {
  quest: Quest;
  onClick: () => void;
}

export default function QuestCard({ quest, onClick }: QuestCardProps) {
  const difficultyColors = {
    'легкие': 'bg-green-500',
    'средние': 'bg-yellow-500',
    'сложные': 'bg-red-500'
  };

  return (
    <div 
      onClick={onClick}
      className="relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer overflow-hidden group border-2 border-transparent hover:border-orange-500/50"
      style={{ aspectRatio: '5/3' }}
    >
      <div className="relative h-full">
        <img 
          src={quest.image_url || 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800'} 
          alt={quest.title}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
        
        {/* Gradient overlay */}
        <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
        
        {/* Content overlay */}
        <div className="absolute inset-0 flex flex-col justify-between">
          {/* Bottom content with semi-transparent background */}
          <div className="mt-auto bg-white/20 backdrop-blur-sm p-4 space-y-3 w-full">
            <div className="flex items-start justify-between gap-3">
              <h3 className="font-bold text-lg text-white leading-tight flex-1 truncate">
                {quest.title}
              </h3>
              {/* Difficulty indicator */}
              <div className={`w-3 h-3 rounded-full flex-shrink-0 mt-1 ${
                difficultyColors[quest.difficulty as keyof typeof difficultyColors] || 'bg-gray-500'
              }`} />
            </div>
            
            <div className="flex items-center justify-between text-sm">
              <div className="flex items-center text-orange-300 flex-1 min-w-0">
                <MapPin className="w-4 h-4 mr-1 flex-shrink-0" />
                <span className="truncate">{quest.city}</span>
              </div>
              
              <div className="flex items-center space-x-3 flex-shrink-0">
                <div className="flex items-center text-orange-400">
                  <Heart className="w-4 h-4 mr-1" />
                  <span className="font-medium">{quest.likes_count}</span>
                </div>
                
                {quest.duration_minutes && (
                  <div className="flex items-center text-orange-300">
                    <Clock className="w-4 h-4 mr-1" />
                    <span className="font-medium">{quest.duration_minutes}</span>
                  </div>
                )}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
