import React, { useState, useEffect } from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
import DashboardPage from './pages/Dashboard';
import SuperAdminDashboard from './pages/SuperAdminDashboard';
import TimetablePage from './pages/TimetablePage';
import SettingsPage from './pages/SettingsPage';
import { Toaster } from './components/ui/sonner';

const App: React.FC = () => {
  const [user, setUser] = useState<any>(null);

  useEffect(() => {
    const savedUser = localStorage.getItem('user');
    if (savedUser) {
      setUser(JSON.parse(savedUser));
    }
  }, []);

  const handleLogin = (userData: any) => {
    setUser(userData);
    localStorage.setItem('user', JSON.stringify(userData));
  };

  const handleLogout = () => {
    setUser(null);
    localStorage.removeItem('user');
  };

  return (
    <Router>
      <div className="min-h-screen bg-slate-50 font-sans">
        <Routes>
          <Route path="/login" element={user ? <Navigate to="/" /> : <LoginPage onLogin={handleLogin} />} />
          <Route path="/register" element={user ? <Navigate to="/" /> : <RegisterPage />} />
          
          <Route path="/" element={
            !user ? <Navigate to="/login" /> : 
            user.role === 'super_admin' ? <SuperAdminDashboard user={user} onLogout={handleLogout} /> :
            <DashboardPage user={user} onLogout={handleLogout} />
          } />

          <Route path="/timetable" element={user ? <TimetablePage user={user} /> : <Navigate to="/login" />} />
          <Route path="/settings" element={user ? <SettingsPage user={user} /> : <Navigate to="/login" />} />
        </Routes>
        <Toaster />
      </div>
    </Router>
  );
}

export default App;
