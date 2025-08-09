import { Moon, Sun, Compass, User, LogIn } from 'lucide-react';
import { useTheme } from '@/react-app/contexts/ThemeContext';
import { useAuth } from '@getmocha/users-service/react';
import { useNavigate } from 'react-router';

export default function Header() {
  const { theme, toggleTheme } = useTheme();
  const { user, redirectToLogin, logout } = useAuth();
  const navigate = useNavigate();

  return (
    <header className="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center">
            <Compass className="w-8 h-8 text-orange-500 mr-3" />
            <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
              CityQuest
            </h1>
          </div>
          
          <div className="flex items-center space-x-4">
            {/* Theme Toggle */}
            <button
              onClick={toggleTheme}
              className="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              aria-label="Toggle theme"
            >
              {theme === 'light' ? (
                <Moon className="w-5 h-5 text-gray-700 dark:text-gray-300" />
              ) : (
                <Sun className="w-5 h-5 text-gray-700 dark:text-gray-300" />
              )}
            </button>

            {/* User Profile or Login */}
            <div className="flex items-center space-x-3">
              {user ? (
                <>
                  <button
                    onClick={() => navigate('/profile')}
                    className="flex items-center space-x-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg p-2 transition-colors"
                  >
                    <div className="w-8 h-8 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                      {user.google_user_data.picture ? (
                        <img 
                          src={user.google_user_data.picture} 
                          alt={user.google_user_data.name || user.email}
                          className="w-full h-full object-cover"
                        />
                      ) : (
                        <User className="w-4 h-4 text-gray-400" />
                      )}
                    </div>
                    <span className="text-sm font-medium text-gray-900 dark:text-gray-100 hidden md:block">
                      {user.google_user_data.name || user.email.split('@')[0]}
                    </span>
                  </button>
                  <button
                    onClick={logout}
                    className="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
                  >
                    Выйти
                  </button>
                </>
              ) : (
                <button
                  onClick={redirectToLogin}
                  className="flex items-center space-x-2 bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg transition-colors"
                >
                  <LogIn className="w-4 h-4" />
                  <span className="font-medium">Войти</span>
                </button>
              )}
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}
