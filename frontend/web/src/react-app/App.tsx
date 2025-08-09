import { BrowserRouter as Router, Routes, Route } from "react-router";
import { ThemeProvider } from "@/react-app/contexts/ThemeContext";
import { AuthProvider } from "@getmocha/users-service/react";
import HomePage from "@/react-app/pages/Home";
import QuestDetail from "@/react-app/pages/QuestDetail";
import UserProfile from "@/react-app/pages/UserProfile";
import AuthCallback from "@/react-app/pages/AuthCallback";

export default function App() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <Router>
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/quest/:id" element={<QuestDetail />} />
            <Route path="/profile" element={<UserProfile />} />
            <Route path="/auth/callback" element={<AuthCallback />} />
          </Routes>
        </Router>
      </AuthProvider>
    </ThemeProvider>
  );
}
