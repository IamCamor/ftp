import { request } from './utils/http';
import type {
  AuthResponse,
  LoginRequest,
  RegisterRequest,
  CatchRecord,
  CatchComment,
  Point,
  WeatherFav,
  Banner,
  Rating,
  Bonus,
  AppNotification,
  AddCatchRequest,
  AddPointRequest,
  AddCommentRequest,
  SaveWeatherFavRequest,
  AddRatingRequest,
  User,
  Subscription,
  Payment,
  SubscriptionStatus,
  SubscriptionPlansResponse,
  FollowResponse,
  FollowersResponse,
  OnlineStatusResponse,
  OnlineUsersResponse
} from './types';

// Auth
export async function login(data: LoginRequest): Promise<AuthResponse> {
  return request('/auth/login', { method: 'POST', data });
}

export async function register(data: RegisterRequest): Promise<AuthResponse> {
  return request('/auth/register', { method: 'POST', data });
}

export async function logout(): Promise<{ ok: boolean }> {
  return request('/auth/logout', { method: 'POST', auth: true });
}

export async function profileMe(): Promise<User> {
  return request('/profile/me', { auth: true });
}

export function oauthRedirect(provider: string): string {
  return `https://api.fishtrackpro.ru/auth/${provider}/redirect`;
}

export function isAuthed(): boolean {
  return !!localStorage.getItem('token');
}

// Feed/Catch
export async function feed(limit = 20, offset = 0): Promise<CatchRecord[]> {
  return request(`/feed?limit=${limit}&offset=${offset}`);
}

export async function catchById(id: number): Promise<CatchRecord> {
  return request(`/catch/${id}`);
}

export async function addCatch(data: AddCatchRequest): Promise<CatchRecord> {
  return request('/catch', { method: 'POST', data, auth: true });
}

export async function likeCatch(id: number): Promise<{ liked: boolean; likes_count: number }> {
  return request(`/catch/${id}/like`, { method: 'POST', auth: true });
}

export async function addCatchComment(id: number, data: AddCommentRequest): Promise<CatchComment> {
  return request(`/catch/${id}/comments`, { method: 'POST', data, auth: true });
}

// Map/Points
export async function points(params: { limit?: number; bbox?: string } = {}): Promise<Point[]> {
  const searchParams = new URLSearchParams();
  if (params.limit) searchParams.set('limit', params.limit.toString());
  if (params.bbox) searchParams.set('bbox', params.bbox);
  
  const query = searchParams.toString();
  return request(`/map/points${query ? `?${query}` : ''}`);
}

export async function pointById(id: number): Promise<Point> {
  return request(`/points/${id}`);
}

export async function createPoint(data: AddPointRequest): Promise<Point> {
  return request('/points', { method: 'POST', data, auth: true });
}

export async function getPointMedia(id: number): Promise<any[]> {
  return request(`/points/${id}/media`);
}

// Weather
export async function getWeatherFavs(): Promise<WeatherFav[]> {
  return request('/weather/favs', { auth: true });
}

export async function saveWeatherFav(data: SaveWeatherFavRequest): Promise<WeatherFav> {
  return request('/weather/favs', { method: 'POST', data, auth: true });
}

// Ratings/Bonuses
export async function addRating(data: AddRatingRequest): Promise<Rating> {
  return request('/ratings', { method: 'POST', data, auth: true });
}

export async function getBonuses(): Promise<Bonus[]> {
  return request('/bonuses', { auth: true });
}

// Notifications
export async function notificationsList(limit = 20, offset = 0): Promise<AppNotification[]> {
  return request(`/notifications?limit=${limit}&offset=${offset}`, { auth: true });
}

export async function notificationRead(id: number): Promise<{ ok: boolean }> {
  return request(`/notifications/${id}/read`, { method: 'POST', auth: true });
}

// Banners
export async function bannersGet(slot: string): Promise<Banner[]> {
  return request(`/banners?slot=${slot}`);
}

// Groups
export async function groups(limit = 20, offset = 0): Promise<any[]> {
  return request(`/groups?limit=${limit}&offset=${offset}`);
}

export async function groupById(id: number): Promise<any> {
  return request(`/groups/${id}`);
}

export async function createGroup(data: any): Promise<any> {
  return request('/groups', { method: 'POST', data, auth: true });
}

export async function joinGroup(id: number): Promise<{ message: string }> {
  return request(`/groups/${id}/join`, { method: 'POST', auth: true });
}

export async function leaveGroup(id: number): Promise<{ message: string }> {
  return request(`/groups/${id}/leave`, { method: 'POST', auth: true });
}

// Events
export async function events(params: { limit?: number; offset?: number; status?: string; group_id?: number } = {}): Promise<any[]> {
  const searchParams = new URLSearchParams();
  if (params.limit) searchParams.set('limit', params.limit.toString());
  if (params.offset) searchParams.set('offset', params.offset.toString());
  if (params.status) searchParams.set('status', params.status);
  if (params.group_id) searchParams.set('group_id', params.group_id.toString());
  
  const query = searchParams.toString();
  return request(`/events${query ? `?${query}` : ''}`);
}

export async function eventById(id: number): Promise<any> {
  return request(`/events/${id}`);
}

export async function createEvent(data: any): Promise<any> {
  return request('/events', { method: 'POST', data, auth: true });
}

export async function joinEvent(id: number): Promise<{ message: string }> {
  return request(`/events/${id}/join`, { method: 'POST', auth: true });
}

export async function leaveEvent(id: number): Promise<{ message: string }> {
  return request(`/events/${id}/leave`, { method: 'POST', auth: true });
}

// Chats
export async function getChats(): Promise<any[]> {
  return request('/chats', { auth: true });
}

export async function getChatById(id: number): Promise<any> {
  return request(`/chats/${id}`, { auth: true });
}

export async function getChatMessages(id: number, limit = 50, offset = 0): Promise<any[]> {
  return request(`/chats/${id}/messages?limit=${limit}&offset=${offset}`, { auth: true });
}

export async function sendChatMessage(id: number, data: { message: string; attachment_url?: string; attachment_type?: string }): Promise<any> {
  return request(`/chats/${id}/messages`, { method: 'POST', data, auth: true });
}

export async function markChatAsRead(id: number): Promise<{ message: string }> {
  return request(`/chats/${id}/read`, { method: 'POST', auth: true });
}

// Live Sessions
export async function liveSessions(params: { limit?: number; offset?: number; status?: string } = {}): Promise<any[]> {
  const searchParams = new URLSearchParams();
  if (params.limit) searchParams.set('limit', params.limit.toString());
  if (params.offset) searchParams.set('offset', params.offset.toString());
  if (params.status) searchParams.set('status', params.status);
  
  const query = searchParams.toString();
  return request(`/live-sessions${query ? `?${query}` : ''}`);
}

export async function liveSessionById(id: number): Promise<any> {
  return request(`/live-sessions/${id}`);
}

export async function createLiveSession(data: any): Promise<any> {
  return request('/live-sessions', { method: 'POST', data, auth: true });
}

export async function startLiveSession(id: number): Promise<any> {
  return request(`/live-sessions/${id}/start`, { method: 'POST', auth: true });
}

export async function endLiveSession(id: number): Promise<any> {
  return request(`/live-sessions/${id}/end`, { method: 'POST', auth: true });
}

export async function joinLiveSession(id: number): Promise<{ message: string }> {
  return request(`/live-sessions/${id}/join`, { method: 'POST', auth: true });
}

export async function leaveLiveSession(id: number): Promise<{ message: string }> {
  return request(`/live-sessions/${id}/leave`, { method: 'POST', auth: true });
}

// Admin API
export async function adminDashboard(): Promise<any> {
  return request('/admin/dashboard', { auth: true });
}

export async function adminRecentActivity(): Promise<any> {
  return request('/admin/recent-activity', { auth: true });
}

// User Management
export async function adminUsers(params: any = {}): Promise<any> {
  const searchParams = new URLSearchParams(params);
  return request(`/admin/users?${searchParams}`, { auth: true });
}

export async function adminUserById(id: number): Promise<any> {
  return request(`/admin/users/${id}`, { auth: true });
}

export async function adminUpdateUser(id: number, data: any): Promise<any> {
  return request(`/admin/users/${id}`, { method: 'PUT', data, auth: true });
}

export async function adminToggleBlockUser(id: number, blockReason?: string): Promise<any> {
  return request(`/admin/users/${id}/toggle-block`, { 
    method: 'POST', 
    data: { block_reason: blockReason }, 
    auth: true 
  });
}

export async function adminDeleteUser(id: number): Promise<any> {
  return request(`/admin/users/${id}`, { method: 'DELETE', auth: true });
}

// Catch Management
export async function adminCatches(params: any = {}): Promise<any> {
  const searchParams = new URLSearchParams(params);
  return request(`/admin/catches?${searchParams}`, { auth: true });
}

export async function adminCatchById(id: number): Promise<any> {
  return request(`/admin/catches/${id}`, { auth: true });
}

export async function adminUpdateCatch(id: number, data: any): Promise<any> {
  return request(`/admin/catches/${id}`, { method: 'PUT', data, auth: true });
}

export async function adminToggleBlockCatch(id: number, blockReason?: string): Promise<any> {
  return request(`/admin/catches/${id}/toggle-block`, { 
    method: 'POST', 
    data: { block_reason: blockReason }, 
    auth: true 
  });
}

export async function adminDeleteCatch(id: number): Promise<any> {
  return request(`/admin/catches/${id}`, { method: 'DELETE', auth: true });
}

// Point Management
export async function adminPoints(params: any = {}): Promise<any> {
  const searchParams = new URLSearchParams(params);
  return request(`/admin/points?${searchParams}`, { auth: true });
}

export async function adminPointById(id: number): Promise<any> {
  return request(`/admin/points/${id}`, { auth: true });
}

export async function adminUpdatePoint(id: number, data: any): Promise<any> {
  return request(`/admin/points/${id}`, { method: 'PUT', data, auth: true });
}

export async function adminToggleBlockPoint(id: number, blockReason?: string): Promise<any> {
  return request(`/admin/points/${id}/toggle-block`, { 
    method: 'POST', 
    data: { block_reason: blockReason }, 
    auth: true 
  });
}

export async function adminDeletePoint(id: number): Promise<any> {
  return request(`/admin/points/${id}`, { method: 'DELETE', auth: true });
}

// Report Management
export async function adminReports(params: any = {}): Promise<any> {
  const searchParams = new URLSearchParams(params);
  return request(`/admin/reports?${searchParams}`, { auth: true });
}

export async function adminReportById(id: number): Promise<any> {
  return request(`/admin/reports/${id}`, { auth: true });
}

export async function adminReviewReport(id: number, data: { status: string; admin_notes?: string }): Promise<any> {
  return request(`/admin/reports/${id}/review`, { method: 'POST', data, auth: true });
}

export async function adminBulkReviewReports(data: { report_ids: number[]; status: string; admin_notes?: string }): Promise<any> {
  return request('/admin/reports/bulk-review', { method: 'POST', data, auth: true });
}

export async function adminReportStatistics(): Promise<any> {
  return request('/admin/reports/statistics', { auth: true });
}

// Subscription API
export async function getSubscriptionPlans(): Promise<SubscriptionPlansResponse> {
  return request('/subscriptions/plans', { auth: true });
}

export async function getSubscriptionStatus(): Promise<SubscriptionStatus> {
  return request('/subscriptions/status', { auth: true });
}

export async function getSubscriptions(params: any = {}): Promise<{ data: Subscription[] }> {
  return request('/subscriptions', { params, auth: true });
}

export async function createSubscription(data: {
  type: 'pro' | 'premium';
  payment_method: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses';
  use_trial?: boolean;
}): Promise<{ data: { subscription: Subscription; payment: Payment } }> {
  return request('/subscriptions', { method: 'POST', data, auth: true });
}

export async function getSubscriptionById(id: number): Promise<{ data: Subscription }> {
  return request(`/subscriptions/${id}`, { auth: true });
}

export async function cancelSubscription(id: number, reason?: string): Promise<any> {
  return request(`/subscriptions/${id}/cancel`, { method: 'POST', data: { reason }, auth: true });
}

export async function extendSubscription(id: number, days: number): Promise<any> {
  return request(`/subscriptions/${id}/extend`, { method: 'POST', data: { days }, auth: true });
}

// Payment API
export async function getPaymentMethods(): Promise<{ data: any[] }> {
  return request('/payments/methods', { auth: true });
}

export async function getPayments(params: any = {}): Promise<{ data: Payment[] }> {
  return request('/payments', { params, auth: true });
}

export async function getPaymentById(id: number): Promise<{ data: Payment }> {
  return request(`/payments/${id}`, { auth: true });
}

export async function processPayment(data: {
  payment_id: string;
  provider: 'yandex_pay' | 'sber_pay' | 'apple_pay' | 'google_pay' | 'bonuses';
  provider_data?: Record<string, any>;
}): Promise<{ data: Payment }> {
  return request('/payments/process', { method: 'POST', data, auth: true });
}

export async function cancelPayment(id: number, reason?: string): Promise<any> {
  return request(`/payments/${id}/cancel`, { method: 'POST', data: { reason }, auth: true });
}

export async function refundPayment(id: number, reason?: string): Promise<any> {
  return request(`/payments/${id}/refund`, { method: 'POST', data: { reason }, auth: true });
}

// Follow API
export async function followUser(userId: number): Promise<FollowResponse> {
  return request('/follow', { method: 'POST', data: { user_id: userId }, auth: true });
}

export async function unfollowUser(userId: number): Promise<FollowResponse> {
  return request('/unfollow', { method: 'POST', data: { user_id: userId }, auth: true });
}

export async function toggleFollow(userId: number): Promise<FollowResponse> {
  return request('/follow/toggle', { method: 'POST', data: { user_id: userId }, auth: true });
}

export async function getFollowSuggestions(limit: number = 10): Promise<{ data: User[] }> {
  return request('/follow/suggestions', { params: { limit }, auth: true });
}

export async function getUserFollowers(userId: number, params: any = {}): Promise<FollowersResponse> {
  return request(`/users/${userId}/followers`, { params, auth: true });
}

export async function getUserFollowing(userId: number, params: any = {}): Promise<FollowersResponse> {
  return request(`/users/${userId}/following`, { params, auth: true });
}

export async function isFollowing(userId: number): Promise<{ data: { following: boolean; followers_count: number; following_count: number } }> {
  return request(`/users/${userId}/is-following`, { auth: true });
}

export async function getMutualFollows(userId: number, limit: number = 10): Promise<{ data: User[] }> {
  return request(`/users/${userId}/mutual`, { params: { limit }, auth: true });
}

// Online Status API
export async function updateOnlineStatus(isOnline: boolean = true): Promise<OnlineStatusResponse> {
  return request('/online/update', { method: 'POST', data: { is_online: isOnline }, auth: true });
}

export async function setOffline(): Promise<OnlineStatusResponse> {
  return request('/online/offline', { method: 'POST', auth: true });
}

export async function getOnlineStatus(): Promise<OnlineStatusResponse> {
  return request('/online/status', { auth: true });
}

export async function getOnlineUsers(params: any = {}): Promise<OnlineUsersResponse> {
  return request('/online/users', { params, auth: true });
}

export async function getRecentlyActiveUsers(minutes: number = 30, limit: number = 20): Promise<OnlineUsersResponse> {
  return request('/online/recently-active', { params: { minutes, limit }, auth: true });
}

export async function getOnlineCount(): Promise<{ data: { online_count: number } }> {
  return request('/online/count', { auth: true });
}

// Enhanced Feed API
export async function getFeed(params: {
  type?: 'all' | 'following' | 'nearby';
  latitude?: number;
  longitude?: number;
  radius?: number;
  limit?: number;
  page?: number;
} = {}): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number } }> {
  return request('/feed', { params, auth: true });
}

export async function getPersonalFeed(limit: number = 20, page: number = 1): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number } }> {
  return request('/feed/personal', { params: { limit, page }, auth: true });
}

export async function getNearbyFeed(latitude: number, longitude: number, radius: number = 50, limit: number = 20): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number } }> {
  return request('/feed/nearby', { params: { latitude, longitude, radius, limit }, auth: true });
}

export async function getFollowingFeed(limit: number = 20, page: number = 1): Promise<{ data: { data: CatchRecord[]; current_page: number; last_page: number; per_page: number; total: number } }> {
  return request('/feed/following', { params: { limit, page }, auth: true });
}
