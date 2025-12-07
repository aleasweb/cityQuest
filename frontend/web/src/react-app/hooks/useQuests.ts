import { useState, useEffect } from 'react';
import { api } from '@/shared/api';
import type { Quest, QuestFilters, City } from '@/shared/types';

/**
 * Хук для получения списка квестов с фильтрами
 */
export function useQuests(filters: QuestFilters) {
  const [quests, setQuests] = useState<Quest[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchQuests = async () => {
      try {
        setLoading(true);
        setError(null);
        const data = await api.getQuests(filters);
        setQuests(data);
      } catch (err) {
        console.error('Failed to load quests:', err);
        setError(err instanceof Error ? err.message : 'Failed to load quests');
      } finally {
        setLoading(false);
      }
    };

    fetchQuests();
  }, [filters.city, filters.difficulty, filters.isPopular]);

  return { quests, loading, error };
}

/**
 * Хук для получения одного квеста по ID
 */
export function useQuest(id: string) {
  const [quest, setQuest] = useState<Quest | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchQuest = async () => {
      try {
        setLoading(true);
        setError(null);
        const data = await api.getQuest(id);
        setQuest(data);
      } catch (err) {
        console.error('Failed to load quest:', err);
        setError(err instanceof Error ? err.message : 'Quest not found');
      } finally {
        setLoading(false);
      }
    };

    fetchQuest();
  }, [id]);

  return { quest, loading, error };
}

/**
 * Хук для получения списка городов
 */
export function useCities() {
  const [cities, setCities] = useState<City[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchCities = async () => {
      try {
        const data = await api.getCities();
        setCities(data);
      } catch (err) {
        console.error('Failed to load cities:', err);
      } finally {
        setLoading(false);
      }
    };

    fetchCities();
  }, []);

  return { cities, loading };
}

/**
 * Хук для работы с лайками квестов
 */
export function useQuestLike(questId: string) {
  const [isLiked, setIsLiked] = useState(false);
  const [isLoading, setIsLoading] = useState(false);

  const toggleLike = async () => {
    try {
      setIsLoading(true);
      const result = await api.toggleLike(questId);
      setIsLiked(result.liked);
    } catch (err) {
      console.error('Failed to toggle like:', err);
      throw err;
    } finally {
      setIsLoading(false);
    }
  };

  return { isLiked, isLoading, toggleLike };
}
