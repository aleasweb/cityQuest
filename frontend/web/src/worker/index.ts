import { Hono } from "hono";
import { zValidator } from '@hono/zod-validator';
import { QuestFiltersSchema } from "@/shared/types";
import {
  getOAuthRedirectUrl,
  exchangeCodeForSessionToken,
  authMiddleware,
  deleteSession,
  MOCHA_SESSION_TOKEN_COOKIE_NAME,
} from "@getmocha/users-service/backend";
import { getCookie, setCookie } from "hono/cookie";

const app = new Hono<{ Bindings: Env }>();

// Get all quests with optional filters
app.get('/api/quests', zValidator('query', QuestFiltersSchema), async (c) => {
  const { city, difficulty, search } = c.req.valid('query');
  
  let sql = 'SELECT * FROM quests WHERE 1=1';
  const params: any[] = [];
  
  if (city) {
    sql += ' AND city = ?';
    params.push(city);
  }
  
  if (difficulty) {
    sql += ' AND difficulty = ?';
    params.push(difficulty);
  }
  
  if (search) {
    sql += ' AND title LIKE ?';
    params.push(`%${search}%`);
  }
  
  // Default sorting by popularity first, then by creation date
  sql += ' ORDER BY likes_count DESC, created_at DESC';
  
  const result = await c.env.DB.prepare(sql).bind(...params).all();
  
  return c.json({
    success: true,
    data: result.results
  });
});

// Get single quest by ID
app.get('/api/quests/:id', async (c) => {
  const id = c.req.param('id');
  
  const result = await c.env.DB.prepare('SELECT * FROM quests WHERE id = ?')
    .bind(id)
    .first();
  
  if (!result) {
    return c.json({ success: false, error: 'Quest not found' }, 404);
  }
  
  return c.json({
    success: true,
    data: result
  });
});

// Get unique cities for filter dropdown
app.get('/api/cities', async (c) => {
  const result = await c.env.DB.prepare('SELECT DISTINCT city FROM quests WHERE city IS NOT NULL ORDER BY city')
    .all();
  
  return c.json({
    success: true,
    data: result.results.map((row: any) => row.city)
  });
});

// Like a quest
app.post('/api/quests/:id/like', async (c) => {
  const id = c.req.param('id');
  
  await c.env.DB.prepare('UPDATE quests SET likes_count = likes_count + 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
    .bind(id)
    .run();
  
  return c.json({ success: true });
});

// Auth endpoints
app.get('/api/oauth/google/redirect_url', async (c) => {
  const redirectUrl = await getOAuthRedirectUrl('google', {
    apiUrl: c.env.MOCHA_USERS_SERVICE_API_URL,
    apiKey: c.env.MOCHA_USERS_SERVICE_API_KEY,
  });

  return c.json({ redirectUrl }, 200);
});

app.post("/api/sessions", async (c) => {
  const body = await c.req.json();

  if (!body.code) {
    return c.json({ error: "No authorization code provided" }, 400);
  }

  const sessionToken = await exchangeCodeForSessionToken(body.code, {
    apiUrl: c.env.MOCHA_USERS_SERVICE_API_URL,
    apiKey: c.env.MOCHA_USERS_SERVICE_API_KEY,
  });

  setCookie(c, MOCHA_SESSION_TOKEN_COOKIE_NAME, sessionToken, {
    httpOnly: true,
    path: "/",
    sameSite: "none",
    secure: true,
    maxAge: 60 * 24 * 60 * 60, // 60 days
  });

  return c.json({ success: true }, 200);
});

app.get("/api/users/me", authMiddleware, async (c) => {
  return c.json(c.get("user"));
});

app.get('/api/logout', async (c) => {
  const sessionToken = getCookie(c, MOCHA_SESSION_TOKEN_COOKIE_NAME);

  if (typeof sessionToken === 'string') {
    await deleteSession(sessionToken, {
      apiUrl: c.env.MOCHA_USERS_SERVICE_API_URL,
      apiKey: c.env.MOCHA_USERS_SERVICE_API_KEY,
    });
  }

  setCookie(c, MOCHA_SESSION_TOKEN_COOKIE_NAME, '', {
    httpOnly: true,
    path: '/',
    sameSite: 'none',
    secure: true,
    maxAge: 0,
  });

  return c.json({ success: true }, 200);
});

// Get user's quest progress
app.get('/api/users/me/quests', authMiddleware, async (c) => {
  const user = c.get('user');
  if (!user) {
    return c.json({ error: 'User not authenticated' }, 401);
  }
  
  const result = await c.env.DB.prepare(`
    SELECT 
      q.*,
      COALESCE(uqp.is_completed, 0) as is_completed,
      COALESCE(uqp.is_liked, 0) as is_liked,
      uqp.completed_at
    FROM quests q
    LEFT JOIN user_quest_progress uqp ON q.id = uqp.quest_id AND uqp.user_id = ?
    WHERE uqp.user_id IS NOT NULL
    ORDER BY uqp.updated_at DESC
  `).bind(user.id).all();
  
  return c.json({
    success: true,
    data: result.results.map((row: any) => ({
      quest: {
        id: row.id,
        title: row.title,
        description: row.description,
        city: row.city,
        difficulty: row.difficulty,
        duration_minutes: row.duration_minutes,
        distance_km: row.distance_km,
        image_url: row.image_url,
        author: row.author,
        likes_count: row.likes_count,
        is_popular: row.is_popular,
        created_at: row.created_at,
        updated_at: row.updated_at
      },
      is_completed: Boolean(row.is_completed),
      is_liked: Boolean(row.is_liked),
      completed_at: row.completed_at
    }))
  });
});

// Mark quest as completed
app.post('/api/quests/:id/complete', authMiddleware, async (c) => {
  const questId = c.req.param('id');
  const user = c.get('user');
  if (!user) {
    return c.json({ error: 'User not authenticated' }, 401);
  }
  
  await c.env.DB.prepare(`
    INSERT INTO user_quest_progress (user_id, quest_id, is_completed, completed_at, updated_at)
    VALUES (?, ?, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
    ON CONFLICT(user_id, quest_id) DO UPDATE SET
      is_completed = 1,
      completed_at = CURRENT_TIMESTAMP,
      updated_at = CURRENT_TIMESTAMP
  `).bind(user.id, questId).run();
  
  return c.json({ success: true });
});

// Toggle quest like status
app.post('/api/quests/:id/toggle-like', authMiddleware, async (c) => {
  const questId = c.req.param('id');
  const user = c.get('user');
  if (!user) {
    return c.json({ error: 'User not authenticated' }, 401);
  }
  
  // Check current status
  const current = await c.env.DB.prepare(`
    SELECT is_liked FROM user_quest_progress 
    WHERE user_id = ? AND quest_id = ?
  `).bind(user.id, questId).first();
  
  const newLikedStatus = current ? !current.is_liked : true;
  
  await c.env.DB.prepare(`
    INSERT INTO user_quest_progress (user_id, quest_id, is_liked, updated_at)
    VALUES (?, ?, ?, CURRENT_TIMESTAMP)
    ON CONFLICT(user_id, quest_id) DO UPDATE SET
      is_liked = ?,
      updated_at = CURRENT_TIMESTAMP
  `).bind(user.id, questId, newLikedStatus ? 1 : 0, newLikedStatus ? 1 : 0).run();
  
  return c.json({ success: true, is_liked: newLikedStatus });
});

export default app;
