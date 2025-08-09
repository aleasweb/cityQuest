import z from "zod";

export const QuestSchema = z.object({
  id: z.number(),
  title: z.string(),
  description: z.string().nullable(),
  city: z.string().nullable(),
  difficulty: z.string().nullable(),
  duration_minutes: z.number().nullable(),
  distance_km: z.number().nullable(),
  image_url: z.string().nullable(),
  author: z.string().nullable(),
  likes_count: z.number(),
  is_popular: z.boolean(),
  created_at: z.string(),
  updated_at: z.string()
});

export type Quest = z.infer<typeof QuestSchema>;

export const QuestFiltersSchema = z.object({
  city: z.string().optional(),
  difficulty: z.enum(['легкие', 'средние', 'сложные']).optional(),
  search: z.string().optional()
});

export type QuestFilters = z.infer<typeof QuestFiltersSchema>;
