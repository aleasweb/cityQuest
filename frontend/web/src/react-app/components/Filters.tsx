import { MapPin } from 'lucide-react';
import { QuestFilters } from '@/shared/types';
import { useCities } from '@/react-app/hooks/useQuests';

interface FiltersProps {
  filters: QuestFilters;
  onFiltersChange: (filters: QuestFilters) => void;
}

export default function Filters({ filters, onFiltersChange }: FiltersProps) {
  const { cities } = useCities();

  return (
    <div className="bg-white rounded-xl shadow-lg border border-orange-500/20 p-6 mb-8">
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {/* City Filter */}
        <div>
          <label className="block text-sm font-medium text-orange-600 mb-2">
            Город
          </label>
          <div className="relative">
                               <select
                     value={filters.city || ''}
                     onChange={(e) => onFiltersChange({ ...filters, city: e.target.value || undefined })}
                     className="w-full pl-10 pr-4 py-3 border-2 border-orange-200 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                   >
                     <option value="">Все города</option>
                     {cities.map(city => (
                       <option key={city.key} value={city.key}>{city.name}</option>
                     ))}
                   </select>
            <MapPin className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-orange-500" />
          </div>
        </div>

        {/* Difficulty Filter */}
        <div>
          <label className="block text-sm font-medium text-orange-600 mb-2">
            Сложность
          </label>
                           <select
                   value={filters.difficulty || ''}
                   onChange={(e) => onFiltersChange({ ...filters, difficulty: e.target.value as any || undefined })}
                   className="w-full px-4 py-3 border-2 border-orange-200 rounded-lg bg-white text-gray-900 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                 >
                   <option value="">Любая сложность</option>
                   <option value="easy">Легкие</option>
                   <option value="medium">Средние</option>
                   <option value="hard">Сложные</option>
                 </select>
        </div>
      </div>
    </div>
  );
}
