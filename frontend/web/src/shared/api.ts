// Phase 2: Removed jwt-decode import - no longer decode JWT on client
// JWT is in HttpOnly cookie and user data comes from backend
import type { Quest, QuestFilters, UserProgress, RegisterData, LoginData, AuthResponse, User, City, UserProfileWithHistory } from './types';

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
 * Phase 2: JWT now in HttpOnly cookie, removed localStorage operations
 */
async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    ...options.headers,
  };

  const url = endpoint.startsWith('http') ? endpoint : `${API_URL}${endpoint}`;
  
  try {
    const response = await fetch(url, {
      ...options,
      headers,
      // Phase 2: Send cookies (including HttpOnly jwt_token) with every request
      credentials: 'include',
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
    if (filters?.isPopular !== undefined) params.append('is_popular', filters.isPopular.toString());
    
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
  toggleLike: async (questId: string): Promise<{ liked: boolean; likesCount: number }> => {
    const response = await apiRequest<{ message: string; data: { liked: boolean; likesCount: number } }>(
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
  
  /**
   * Отказаться от квеста (удалить прогресс)
   * DELETE /user/progress/{questId}
   */
  abandonQuest: async (questId: string): Promise<void> => {
    await apiRequest<{ message: string }>(
      `/user/progress/${questId}`,
      { method: 'DELETE' }
    );
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
   * Phase 2: JWT automatically set in HttpOnly cookie by backend
   * User data returned in response body (no need to decode JWT)
   */
  login: async (data: LoginData): Promise<AuthResponse> => {
    const response = await apiRequest<{ token: string; user: User }>(
      '/auth/login',
      {
        method: 'POST',
        body: JSON.stringify(data),
      }
    );
    
    // Phase 2: No localStorage operations - JWT is in HttpOnly cookie
    // User data comes from backend in response body
    
    return {
      token: response.token, // Still returned for backward compatibility
      user: response.user,
    };
  },
  
  /**
   * Выход из системы
   * POST /auth/logout
   * Phase 2: HttpOnly cookie will expire automatically, no localStorage to clean
   */
  logout: async (): Promise<void> => {
    await apiRequest('/auth/logout', { method: 'POST' });
    // Phase 2: No localStorage operations - cookie handled by browser
  },
  
  /**
   * Получить профиль пользователя с историей квестов
   * GET /users/{username}?includeQuests=true
   */
  getProfileWithQuestHistory: async (username: string): Promise<UserProfileWithHistory> => {
    return apiRequest<UserProfileWithHistory>(
      `/users/${username}?includeQuests=true`
    );
  },

  /**
   * Проверка текущего пользователя
   * Phase 2: Calls backend /auth/me to get user data (JWT in HttpOnly cookie)
   * No JWT decoding on client - more secure approach
   */
  getCurrentUser: async (): Promise<User | null> => {
    try {
      // Phase 2: Call backend endpoint with credentials (HttpOnly cookie sent automatically)
      const response = await apiRequest<{ data: { user: User } }>(
        '/auth/me',
        {
          method: 'GET',
        }
      );
      
      return response.data.user;
    } catch (error) {
      // 401 Unauthorized means no valid JWT cookie - user not logged in
      console.log('User not authenticated');
      return null;
    }
  },
};

export default api;
