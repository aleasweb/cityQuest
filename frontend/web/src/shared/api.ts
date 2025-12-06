import { jwtDecode } from 'jwt-decode';
import type { Quest, QuestFilters, UserProgress, RegisterData, LoginData, AuthResponse, User, City } from './types';

const API_URL = import.meta.env.VITE_API_URL || '/api';

/**
 * API Response wrapper
 */
interface ApiResponse<T> {
  data?: T;
  message?: string;
  error?: string;
}

/**
 * HTTP request helper with JWT authentication
 */
async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const token = localStorage.getItem('jwt_token');
  
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...(token && { Authorization: `Bearer ${token}` }),
    ...options.headers,
  };

  const url = endpoint.startsWith('http') ? endpoint : `${API_URL}${endpoint}`;
  
  try {
    const response = await fetch(url, {
      ...options,
      headers,
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || `HTTP Error: ${response.status}`);
    }

    return data;
  } catch (error) {
    console.error('API Request Error:', error);
    throw error;
  }
}

/**
 * API Client
 */
export const api = {
  // ============ QUESTS ============
  
  /**
   * Получить список квестов с фильтрами
   * GET /quests?city=Moscow&difficulty=easy
   */
  getQuests: async (filters?: QuestFilters): Promise<Quest[]> => {
    const params = new URLSearchParams();
    
    if (filters?.city) params.append('city', filters.city);
    if (filters?.difficulty) params.append('difficulty', filters.difficulty);
    
    const query = params.toString();
    const response = await apiRequest<ApiResponse<Quest[]>>(
      `/quests${query ? `?${query}` : ''}`
    );
    
    return response.data || [];
  },
  
  /**
   * Получить квест по ID
   * GET /quests/{id}
   */
  getQuest: async (id: string): Promise<Quest> => {
    const response = await apiRequest<ApiResponse<Quest>>(`/quests/${id}`);
    
    if (!response.data) {
      throw new Error('Quest not found');
    }
    
    return response.data;
  },
  
  /**
   * Получить квесты рядом с локацией
   * GET /quests/nearby?lat=55.7558&lng=37.6173&radius=10&limit=20
   */
  getNearbyQuests: async (
    lat: number,
    lng: number,
    radius: number = 10,
    limit: number = 20
  ): Promise<Quest[]> => {
    const params = new URLSearchParams({
      lat: lat.toString(),
      lng: lng.toString(),
      radius: radius.toString(),
      limit: limit.toString(),
    });
    
    const response = await apiRequest<ApiResponse<Quest[]>>(`/quests/nearby?${params}`);
    return response.data || [];
  },
  
  /**
   * Переключить лайк на квесте (требует авторизации)
   * POST /quests/{id}/like
   */
  toggleLike: async (questId: string): Promise<{ liked: boolean }> => {
    const response = await apiRequest<{ message: string; data: { liked: boolean } }>(
      `/quests/${questId}/like`,
      { method: 'POST' }
    );
    
    return response.data;
  },

           /**
          * Получить список городов
          * GET /cities
          */
         getCities: async (): Promise<City[]> => {
           const response = await apiRequest<{ data: City[] }>('/cities');
           return response.data || [];
  },

  // ============ USER PROGRESS ============
  
  /**
   * Получить прогресс пользователя
   * GET /user/progress?status=active&liked=true
   */
  getUserProgress: async (params?: {
    status?: 'active' | 'paused' | 'completed';
    liked?: boolean;
  }): Promise<UserProgress[]> => {
    const query = new URLSearchParams();
    
    if (params?.status) query.append('status', params.status);
    if (params?.liked !== undefined) query.append('liked', params.liked.toString());
    
    const queryString = query.toString();
    const response = await apiRequest<ApiResponse<UserProgress[]>>(
      `/user/progress${queryString ? `?${queryString}` : ''}`
    );
    
    return response.data || [];
  },
  
  /**
   * Начать квест
   * POST /user/progress/{questId}/start
   */
  startQuest: async (questId: string): Promise<UserProgress> => {
    const response = await apiRequest<{ message: string; data: UserProgress }>(
      `/user/progress/${questId}/start`,
      { method: 'POST' }
    );
    
    return response.data;
  },
  
  /**
   * Приостановить квест
   * PATCH /user/progress/{questId}/pause
   */
  pauseQuest: async (questId: string): Promise<UserProgress> => {
    const response = await apiRequest<{ message: string; data: UserProgress }>(
      `/user/progress/${questId}/pause`,
      { method: 'PATCH' }
    );
    
    return response.data;
  },
  
  /**
   * Завершить квест
   * PATCH /user/progress/{questId}/complete
   */
  completeQuest: async (questId: string): Promise<UserProgress> => {
    const response = await apiRequest<{ message: string; data: UserProgress }>(
      `/user/progress/${questId}/complete`,
      { method: 'PATCH' }
    );
    
    return response.data;
  },

  // ============ AUTHENTICATION ============
  
  /**
   * Регистрация нового пользователя
   * POST /auth/register
   */
  register: async (data: RegisterData): Promise<User> => {
    const response = await apiRequest<{ message: string; user: User }>(
      '/auth/register',
      {
        method: 'POST',
        body: JSON.stringify(data),
      }
    );
    
    return response.user;
  },
  
  /**
   * Вход в систему
   * POST /auth/login
   */
  login: async (data: LoginData): Promise<AuthResponse> => {
    const response = await apiRequest<{ token: string }>(
      '/auth/login',
      {
        method: 'POST',
        body: JSON.stringify(data),
      }
    );
    
    // Сохраняем токен
    localStorage.setItem('jwt_token', response.token);
    
    // Декодируем JWT для получения данных пользователя
    const payload = jwtDecode<{ sub?: string; user_id?: string; email?: string; username?: string }>(response.token);
    
    return {
      token: response.token,
      user: {
        id: payload.sub || payload.user_id || '',
        email: payload.email || payload.username || '',
        username: payload.username || payload.email || '',
        createdAt: new Date().toISOString(),
      },
    };
  },
  
  /**
   * Выход из системы
   * POST /auth/logout
   */
  logout: async (): Promise<void> => {
    try {
      await apiRequest('/auth/logout', { method: 'POST' });
    } finally {
      localStorage.removeItem('jwt_token');
    }
  },
  
  /**
   * Проверка текущего пользователя
   */
  getCurrentUser: async (): Promise<User | null> => {
    const token = localStorage.getItem('jwt_token');
    
    if (!token) {
      return null;
    }
    
    try {
      // Декодируем JWT
      const payload = jwtDecode<{ 
        sub?: string; 
        user_id?: string; 
        email?: string; 
        username?: string; 
        exp?: number 
      }>(token);
      
      // Проверяем срок действия
      if (payload.exp && payload.exp * 1000 < Date.now()) {
        localStorage.removeItem('jwt_token');
        return null;
      }
      
      return {
        id: payload.sub || payload.user_id || '',
        email: payload.email || payload.username || '',
        username: payload.username || payload.email || '',
        createdAt: new Date().toISOString(),
      };
    } catch (error) {
      console.error('Failed to decode JWT:', error);
      localStorage.removeItem('jwt_token');
      return null;
    }
  },
};

export default api;
