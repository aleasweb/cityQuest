import { useState } from 'react';
import { Compass, User, LogIn } from 'lucide-react';
import { useAuth } from '@/react-app/contexts/AuthContext';
import { useNavigate } from 'react-router';
import AuthModal from './AuthModal';

export default function Header() {
  const { user, isAuthenticated, logout } = useAuth();
  const navigate = useNavigate();
  const [isAuthModalOpen, setIsAuthModalOpen] = useState(false);

  const handleLogin = () => {
    setIsAuthModalOpen(true);
  };

  return (
    <header className="bg-white shadow-sm border-b border-gray-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex justify-between items-center h-16">
          <div className="flex items-center cursor-pointer" onClick={() => navigate('/')}>
            <Compass className="w-8 h-8 text-orange-500 mr-3" />
            <h1 className="text-2xl font-bold text-gray-900">
              CityQuest
            </h1>
          </div>
          
          <div className="flex items-center space-x-4">
            {/* User Profile or Login */}
            <div className="flex items-center space-x-3">
              {isAuthenticated && user ? (
                <>
                  <button
                    onClick={() => navigate('/profile')}
                    className="flex items-center space-x-2 hover:bg-gray-100 rounded-lg p-2 transition-colors"
                  >
                    <div className="w-8 h-8 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
                      <User className="w-4 h-4 text-gray-400" />
                    </div>
                    <span className="text-sm font-medium text-gray-900 hidden md:block">
                      {user.username || user.email.split('@')[0]}
                    </span>
                  </button>
                  <button
                    onClick={logout}
                    className="text-sm text-gray-600 hover:text-gray-900 transition-colors"
                  >
                    Выйти
                  </button>
                </>
              ) : (
                <button
                  onClick={handleLogin}
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

      {/* Auth Modal */}
      <AuthModal 
        isOpen={isAuthModalOpen} 
        onClose={() => setIsAuthModalOpen(false)} 
      />
    </header>
  );
}
