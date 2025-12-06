import { BrowserRouter as Router, Routes, Route } from "react-router";
import { ThemeProvider } from "@/react-app/contexts/ThemeContext";
import { AuthProvider } from "@/react-app/contexts/AuthContext";
import HomePage from "@/react-app/pages/Home";
import QuestDetail from "@/react-app/pages/QuestDetail";
import UserProfile from "@/react-app/pages/UserProfile";

export default function App() {
  return (
    <ThemeProvider>
      <AuthProvider>
        <Router>
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/quest/:id" element={<QuestDetail />} />
            <Route path="/profile" element={<UserProfile />} />
          </Routes>
        </Router>
      </AuthProvider>
    </ThemeProvider>
  );
}
