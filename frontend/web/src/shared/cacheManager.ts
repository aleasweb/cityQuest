/**
 * CacheManager - Utility for client-side caching with TTL support
 * 
 * Provides simple localStorage-based caching with expiration time.
 * Used for caching API responses that change infrequently.
 * 
 * @example
 * ```typescript
 * const cache = new CacheManager();
 * 
 * // Set cache with 1 hour TTL
 * cache.set('cities', citiesData, 3600);
 * 
 * // Get cached data (returns null if expired or not found)
 * const cities = cache.get<City[]>('cities');
 * 
 * // Check if cache is valid
 * if (cache.isValid('cities')) {
 *   // Use cached data
 * }
 * 
 * // Invalidate specific cache
 * cache.invalidate('cities');
 * 
 * // Clear all cache
 * cache.clear();
 * ```
 */

/**
 * Cache entry structure stored in localStorage
 */
interface CacheEntry<T> {
  /** Cached data */
  data: T;
  /** Unix timestamp (milliseconds) when cache expires */
  expiresAt: number;
}

/**
 * CacheManager class for managing localStorage cache with TTL
 */
export class CacheManager {
  private readonly storageAvailable: boolean;

  constructor() {
    // Check if localStorage is available (may not work in private/incognito mode)
    this.storageAvailable = this.checkStorageAvailable();
  }

  /**
   * Check if localStorage is available and writable
   */
  private checkStorageAvailable(): boolean {
    try {
      const testKey = '__cache_test__';
      localStorage.setItem(testKey, 'test');
      localStorage.removeItem(testKey);
      return true;
    } catch (e) {
      console.warn('[CacheManager] localStorage not available:', e);
      return false;
    }
  }

  /**
   * Get cached data by key
   * 
   * @param key - Cache key
   * @returns Cached data or null if not found/expired
   */
  get<T>(key: string): T | null {
    if (!this.storageAvailable) {
      return null;
    }

    try {
      const item = localStorage.getItem(key);
      
      if (!item) {
        return null;
      }

      const entry: CacheEntry<T> = JSON.parse(item);
      
      // Check if cache is expired
      if (Date.now() > entry.expiresAt) {
        // Remove expired cache
        this.invalidate(key);
        return null;
      }

      return entry.data;
    } catch (error) {
      console.error(`[CacheManager] Error reading cache key "${key}":`, error);
      // Remove corrupted cache entry
      this.invalidate(key);
      return null;
    }
  }

  /**
   * Set cache data with TTL
   * 
   * @param key - Cache key
   * @param data - Data to cache
   * @param ttl - Time to live in seconds
   */
  set<T>(key: string, data: T, ttl: number): void {
    if (!this.storageAvailable) {
      return;
    }

    try {
      const entry: CacheEntry<T> = {
        data,
        expiresAt: Date.now() + (ttl * 1000), // Convert seconds to milliseconds
      };

      localStorage.setItem(key, JSON.stringify(entry));
    } catch (error) {
      // Handle QuotaExceededError or other localStorage errors
      if (error instanceof Error && error.name === 'QuotaExceededError') {
        console.error('[CacheManager] localStorage quota exceeded. Clearing cache.');
        this.clear();
        // Try again after clearing
        try {
          const entry: CacheEntry<T> = {
            data,
            expiresAt: Date.now() + (ttl * 1000),
          };
          localStorage.setItem(key, JSON.stringify(entry));
        } catch (retryError) {
          console.error('[CacheManager] Failed to set cache after clearing:', retryError);
        }
      } else {
        console.error(`[CacheManager] Error setting cache key "${key}":`, error);
      }
    }
  }

  /**
   * Check if cache entry is valid (exists and not expired)
   * 
   * @param key - Cache key
   * @returns true if cache is valid, false otherwise
   */
  isValid(key: string): boolean {
    if (!this.storageAvailable) {
      return false;
    }

    try {
      const item = localStorage.getItem(key);
      
      if (!item) {
        return false;
      }

      const entry: CacheEntry<unknown> = JSON.parse(item);
      
      return Date.now() <= entry.expiresAt;
    } catch (error) {
      console.error(`[CacheManager] Error checking cache validity "${key}":`, error);
      return false;
    }
  }

  /**
   * Invalidate (remove) specific cache entry
   * 
   * @param key - Cache key to remove
   */
  invalidate(key: string): void {
    if (!this.storageAvailable) {
      return;
    }

    try {
      localStorage.removeItem(key);
    } catch (error) {
      console.error(`[CacheManager] Error invalidating cache key "${key}":`, error);
    }
  }

  /**
   * Clear all cache entries
   * 
   * Warning: This removes ALL localStorage items, not just cache entries.
   * Use with caution in production.
   */
  clear(): void {
    if (!this.storageAvailable) {
      return;
    }

    try {
      localStorage.clear();
    } catch (error) {
      console.error('[CacheManager] Error clearing cache:', error);
    }
  }

  /**
   * Get cached data ignoring expiration (for fallback scenarios)
   * 
   * @param key - Cache key
   * @returns Cached data or null if not found (ignores expiration)
   */
  getStale<T>(key: string): T | null {
    if (!this.storageAvailable) {
      return null;
    }

    try {
      const item = localStorage.getItem(key);
      
      if (!item) {
        return null;
      }

      const entry: CacheEntry<T> = JSON.parse(item);
      return entry.data;
    } catch (error) {
      console.error(`[CacheManager] Error reading stale cache key "${key}":`, error);
      return null;
    }
  }
}

/**
 * Singleton instance of CacheManager
 */
export const cache = new CacheManager();

