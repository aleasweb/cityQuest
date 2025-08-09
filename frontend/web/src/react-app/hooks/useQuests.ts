import { useState, useEffect } from 'react';
import { Quest, QuestFilters } from '@/shared/types';

export function useQuests(filters: QuestFilters) {
  const [quests, setQuests] = useState<Quest[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchQuests = async () => {
      try {
        setLoading(true);
        const params = new URLSearchParams();
        if (filters.city) params.append('city', filters.city);
        if (filters.difficulty) params.append('difficulty', filters.difficulty);
        if (filters.search) params.append('search', filters.search);
        

        const response = await fetch(`/api/quests?${params}`);
        const result = await response.json();
        
        if (result.success) {
          setQuests(result.data);
        } else {
          setError('Failed to load quests');
        }
      } catch (err) {
        setError('Failed to load quests');
      } finally {
        setLoading(false);
      }
    };

    fetchQuests();
  }, [filters.city, filters.difficulty, filters.search]);

  return { quests, loading, error };
}

export function useQuest(id: string) {
  const [quest, setQuest] = useState<Quest | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchQuest = async () => {
      try {
        setLoading(true);
        const response = await fetch(`/api/quests/${id}`);
        const result = await response.json();
        
        if (result.success) {
          setQuest(result.data);
        } else {
          setError('Quest not found');
        }
      } catch (err) {
        setError('Failed to load quest');
      } finally {
        setLoading(false);
      }
    };

    fetchQuest();
  }, [id]);

  return { quest, loading, error };
}

export function useCities() {
  const [cities, setCities] = useState<string[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchCities = async () => {
      try {
        const response = await fetch('/api/cities');
        const result = await response.json();
        
        if (result.success) {
          setCities(result.data);
        }
      } catch (err) {
        console.error('Failed to load cities');
      } finally {
        setLoading(false);
      }
    };

    fetchCities();
  }, []);

  return { cities, loading };
}
