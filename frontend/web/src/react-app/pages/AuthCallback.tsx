// Файл не используется (JWT auth не требует OAuth callback)
// Оставлен для совместимости с роутингом

import { useEffect } from 'react';
import { useNavigate } from 'react-router';

export default function AuthCallback() {
  const navigate = useNavigate();

  useEffect(() => {
    // Редирект на главную
    navigate('/');
  }, [navigate]);

  return (
    <div className="min-h-screen flex items-center justify-center">
      <p>Перенаправление...</p>
    </div>
  );
}
