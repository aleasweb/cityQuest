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
  createdAt: z.string(),
  updatedAt: z.string()
});

export type Quest = z.infer<typeof QuestSchema>;

export const QuestFiltersSchema = z.object({
  city: z.string().optional(),
  difficulty: z.enum(['easy', 'medium', 'hard']).optional()
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
