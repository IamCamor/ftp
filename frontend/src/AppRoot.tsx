import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Header from './components/Header';
import BottomNav from './components/BottomNav';
import FeedScreen from './pages/FeedScreen';
import MapScreen from './pages/MapScreen';
import WeatherPage from './pages/WeatherPage';
import NotificationsPage from './pages/NotificationsPage';
import ProfilePage from './pages/ProfilePage';
import GroupsPage from './pages/GroupsPage';
import EventsPage from './pages/EventsPage';
import LiveFishingPage from './pages/LiveFishingPage';
import LoginPage from './pages/Auth/LoginPage';
import RegisterPage from './pages/Auth/RegisterPage';
import AdminDashboard from './pages/Admin/AdminDashboard';
import UserManagement from './pages/Admin/UserManagement';
import { isAuthed, profileMe } from './api';
import config from './config';

const ProtectedRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return isAuthed() ? <>{children}</> : <Navigate to={config.routes.auth.login} />;
};

const AdminRoute: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [isAdmin, setIsAdmin] = React.useState<boolean | null>(null);
  
  React.useEffect(() => {
    const checkAdmin = async () => {
      if (isAuthed()) {
        try {
          const user = await profileMe();
          setIsAdmin(user.role === 'admin');
        } catch (error) {
          setIsAdmin(false);
        }
      } else {
        setIsAdmin(false);
      }
    };
    
    checkAdmin();
  }, []);
  
  if (isAdmin === null) {
    return <div>Проверка прав доступа...</div>;
  }
  
  return isAdmin ? <>{children}</> : <Navigate to="/" />;
};

const AppRoot: React.FC = () => {
  return (
    <Router>
      <div className="app">
        <Header />
        
        <main className="main-content">
          <Routes>
            <Route path={config.routes.feed} element={<FeedScreen />} />
            <Route path={config.routes.map} element={<MapScreen />} />
            <Route 
              path={config.routes.weather} 
              element={
                <ProtectedRoute>
                  <WeatherPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path={config.routes.alerts} 
              element={
                <ProtectedRoute>
                  <NotificationsPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path={config.routes.profile} 
              element={
                <ProtectedRoute>
                  <ProfilePage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/groups" 
              element={
                <ProtectedRoute>
                  <GroupsPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/events" 
              element={
                <ProtectedRoute>
                  <EventsPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/live" 
              element={
                <ProtectedRoute>
                  <LiveFishingPage />
                </ProtectedRoute>
              } 
            />
            <Route path={config.routes.auth.login} element={<LoginPage />} />
            <Route path={config.routes.auth.register} element={<RegisterPage />} />
            
            {/* Admin Routes */}
            <Route 
              path="/admin" 
              element={
                <AdminRoute>
                  <AdminDashboard />
                </AdminRoute>
              } 
            />
            <Route 
              path="/admin/users" 
              element={
                <AdminRoute>
                  <UserManagement />
                </AdminRoute>
              } 
            />
            
            <Route path="/" element={<Navigate to={config.routes.feed} />} />
            <Route path="*" element={<Navigate to={config.routes.feed} />} />
          </Routes>
        </main>
        
        <BottomNav />
      </div>
    </Router>
  );
};

export default AppRoot;
