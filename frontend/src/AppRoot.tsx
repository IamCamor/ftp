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
import GroupPostPage from './pages/GroupPostPage';
import EventsPage from './pages/EventsPage';
import EventDetailPage from './pages/EventDetailPage';
import RatingsPage from './pages/RatingsPage';
import LiveFishingPage from './pages/LiveFishingPage';
import LoginPage from './pages/Auth/LoginPage';
import RegisterPage from './pages/Auth/RegisterPage';
import AdminDashboard from './pages/Admin/AdminDashboard';
import UserManagement from './pages/Admin/UserManagement';
import SubscriptionPage from './pages/SubscriptionPage';
import ReferencePage from './pages/ReferencePage';
import ReferenceItemPage from './pages/ReferenceItemPage';
import CatchDetailPage from './pages/CatchDetailPage';
import PlaceDetailPage from './pages/PlaceDetailPage';
import SearchPage from './pages/SearchPage';
import UserProfilePage from './pages/UserProfilePage';
import AddCatchPage from './pages/AddCatchPage';
import AddPointPage from './pages/AddPointPage';
import TracksPage from './pages/TracksPage';
import TrackDetailPage from './pages/TrackDetailPage';
import SettingsPage from './pages/SettingsPage';
import MapSelectionPage from './pages/MapSelectionPage';
import WeatherForecastPage from './pages/WeatherForecastPage';
import CatchFeedPage from './pages/CatchFeedPage';
import ReportPage from './pages/ReportPage';
import { isAuthed, profileMe } from './api';
import { useTestAuth } from './hooks/useTestAuth';
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

const AppContent: React.FC = () => {
  // Используем хук для тестовой авторизации
  useTestAuth();

  // Обработка OAuth токенов из URL
  React.useEffect(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');
    
    if (token) {
      // Сохраняем токен в localStorage
      localStorage.setItem('token', token);
      console.log('OAuth token saved:', token);
      
      // Убираем токен из URL
      const newUrl = new URL(window.location.href);
      newUrl.searchParams.delete('token');
      window.history.replaceState({}, '', newUrl.toString());
      
      // Перезагружаем страницу для применения авторизации
      window.location.reload();
    }
  }, []);

  return (
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
              path="/settings" 
              element={
                <ProtectedRoute>
                  <SettingsPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/map-selection" 
              element={
                <ProtectedRoute>
                  <MapSelectionPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/weather-forecast" 
              element={
                <ProtectedRoute>
                  <WeatherForecastPage />
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
              path="/groups/:groupId/posts/:postId" 
              element={
                <ProtectedRoute>
                  <GroupPostPage />
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
              path="/events/:id" 
              element={
                <ProtectedRoute>
                  <EventDetailPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/ratings" 
              element={
                <ProtectedRoute>
                  <RatingsPage />
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
            <Route 
              path="/subscription" 
              element={
                <ProtectedRoute>
                  <SubscriptionPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/reference" 
              element={<ReferencePage />} 
            />
            <Route 
              path="/reference/:type" 
              element={<ReferencePage />} 
            />
            <Route 
              path="/reference/:type/:slug" 
              element={<ReferenceItemPage />} 
            />
            <Route 
              path="/catch/:id" 
              element={<CatchDetailPage />} 
            />
            <Route 
              path="/place/:id" 
              element={<PlaceDetailPage />} 
            />
            <Route 
              path="/search" 
              element={<SearchPage />} 
            />
            <Route 
              path="/users/:id" 
              element={<UserProfilePage />} 
            />
            <Route 
              path="/add-catch" 
              element={
                <ProtectedRoute>
                  <AddCatchPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/add-point" 
              element={
                <ProtectedRoute>
                  <AddPointPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/tracks" 
              element={
                <ProtectedRoute>
                  <TracksPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/tracks/:id" 
              element={
                <ProtectedRoute>
                  <TrackDetailPage />
                </ProtectedRoute>
              } 
            />
            <Route 
              path="/catch-feed" 
              element={<CatchFeedPage />} 
            />
            <Route path={config.routes.auth.login} element={<LoginPage />} />
            <Route path={config.routes.auth.register} element={<RegisterPage />} />
            
            {/* Admin Routes */}
            <Route 
              path="/q/admin" 
              element={
                <AdminRoute>
                  <AdminDashboard />
                </AdminRoute>
              } 
            />
            <Route 
              path="/q/admin/users" 
              element={
                <AdminRoute>
                  <UserManagement />
                </AdminRoute>
              } 
            />
            <Route 
              path="/report" 
              element={
                <ProtectedRoute>
                  <ReportPage />
                </ProtectedRoute>
              } 
            />
            
            <Route path="/" element={<Navigate to={config.routes.feed} />} />
            <Route path="*" element={<Navigate to={config.routes.feed} />} />
          </Routes>
        </main>
        
        <BottomNav />
      </div>
  );
};

const AppRoot: React.FC = () => {
  return (
    <Router>
      <AppContent />
    </Router>
  );
};

export default AppRoot;
