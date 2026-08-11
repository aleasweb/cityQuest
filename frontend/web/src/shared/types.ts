import z from "zod";

export const QuestSchema = z.object({
  id: z.string(), // UUID из Symfony
  title: z.string(),
  description: z.string().nullable(),
  city: z.string().nullable(),
  difficulty: z.string().nullable(),
  durationMinutes: z.number().nullable(),
  distanceKm: z.number().nullable(),
  imageUrl: z.string().nullable(),
  author: z.string().nullable(),
  likesCount: z.number(),
  isPopular: z.boolean(),
  isLikedByCurrentUser: z.boolean().optional(), // Для авторизованных пользователей
  isStartedByCurrentUser: z.boolean().optional(), // Начат ли квест текущим пользователем
  questStatus: z.enum(['active', 'paused', 'completed']).nullable().optional(), // Статус квеста для текущего пользователя
  createdAt: z.string(),
  updatedAt: z.string()
});

export type Quest = z.infer<typeof QuestSchema>;

export const QuestFiltersSchema = z.object({
  city: z.string().optional(),
  difficulty: z.enum(['easy', 'medium', 'hard']).optional(),
  isPopular: z.boolean().optional()
});

export type QuestFilters = z.infer<typeof QuestFiltersSchema>;

// City Types
export const CitySchema = z.object({
  key: z.string(),
  name: z.string()
});

export type City = z.infer<typeof CitySchema>;

// User Progress Types
export const UserProgressSchema = z.object({
  id: z.string(),
  quest_id: z.string(),
  status: z.enum(['active', 'paused', 'completed']),
  is_liked: z.boolean(),
  started_at: z.string(),
  completed_at: z.string().nullable(),
  updated_at: z.string()
});

export type UserProgress = z.infer<typeof UserProgressSchema>;

// Quest step (чекпоинт) — ответ GET /quests/{questId}/steps/{stepNumber}
export const QuestStepCoordinatesSchema = z.object({
  lat: z.number(),
  lng: z.number(),
});

export const QuestStepSchema = z.object({
  id: z.number(),
  number: z.number(),
  title: z.string().nullable(),
  text: z.string().nullable(),
  imageUrl: z.string().nullable(),
  audioUrl: z.string().nullable(),
  videoUrl: z.string().nullable(),
  coordinates: QuestStepCoordinatesSchema,
  radius: z.number(),
  createdAt: z.string(),
});

export type QuestStep = z.infer<typeof QuestStepSchema>;

export const QuestStepCheckBodySchema = z.object({
  latitude: z.number().gte(-90).lte(90),
  longitude: z.number().gte(-180).lte(180),
});

export type QuestStepCheckBody = z.infer<typeof QuestStepCheckBodySchema>;

/** Внутренние поля `data` при успешном POST /user/progress/{questId}/check */
export const QuestStepCheckResultDataSchema = z.object({
  success: z.literal(true),
  nextStepNumber: z.number().optional(),
  distance: z.number(),
});

export type QuestStepCheckResultData = z.infer<typeof QuestStepCheckResultDataSchema>;

// Quest Progress Types (для истории)
export interface QuestProgressItem {
  quest: {
    id: string;
    title: string;
    imageUrl: string | null;
    difficulty: string | null;
    city: string | null;
  };
  status: 'active' | 'paused' | 'completed';
  isLiked: boolean;
  startedAt: string;
  completedAt: string | null;
}

export interface UserProfileWithHistory {
  id: string;
  username: string;
  createdAt: string;
  activeQuest: QuestProgressItem | null;
  pausedQuests: QuestProgressItem[];
  completedQuests: QuestProgressItem[];
}

// Auth Types
export interface RegisterData {
  email: string;
  password: string;
  username: string;
}

export interface LoginData {
  username: string;
  password: string;
}

export interface User {
  id: string;
  email: string;
  username: string;
  createdAt: string;
}

export interface AuthResponse {
  token: string;
  user: User;
}
