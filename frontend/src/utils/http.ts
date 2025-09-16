import appConfig from '../config';
import logger from './logger';

// Define types for fetch API
interface RequestInit {
  method?: string;
  headers?: Record<string, string>;
  body?: string;
}

interface RequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE';
  data?: any;
  auth?: boolean;
  params?: Record<string, any>;
  headers?: Record<string, string>;
}

export async function request(path: string, options: RequestOptions = {}) {
  const { method = 'GET', data, auth = false, params, headers: customHeaders = {} } = options;
  
  let url = `${appConfig.apiBase}${path}`;
  
  // Add query parameters for GET requests
  if (params && method === 'GET') {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        searchParams.append(key, String(value));
      }
    });
    if (searchParams.toString()) {
      url += `?${searchParams.toString()}`;
    }
  }
  
  const headers: Record<string, string> = {};

  // Don't set Content-Type for FormData, let the browser set it with boundary
  if (!(data instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }

  // Add custom headers
  Object.entries(customHeaders).forEach(([key, value]) => {
    headers[key] = value;
  });

  if (auth) {
    const token = localStorage.getItem('token');
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
      logger.debug('Using token for request:', token.substring(0, 20) + '...');
    } else {
      console.warn('No token found in localStorage for authenticated request');
    }
  }

  const requestConfig: RequestInit = {
    method,
    headers,
  };

  if (data && method !== 'GET') {
    if (data instanceof FormData) {
      requestConfig.body = data as any;
    } else {
      requestConfig.body = JSON.stringify(data);
    }
  }

  try {
    logger.debug(`Making ${method} request to: ${url}`);
    
    const response = await fetch(url, requestConfig);
    
    // Check for redirects (302, 301, etc.) - these indicate authentication issues
    if (response.redirected || response.status === 302 || response.status === 301) {
      console.error('API Error:', {
        url,
        status: response.status,
        statusText: response.statusText,
        error: 'Authentication required'
      });
      // Clear invalid token
      localStorage.removeItem('token');
      throw new Error('Authentication required');
    }
    
    if (!response.ok) {
      // Check if response is HTML (redirect to login)
      const contentType = response.headers.get('content-type');
      if (contentType && contentType.includes('text/html')) {
        console.error('API Error:', {
          url,
          status: response.status,
          statusText: response.statusText,
          error: 'Authentication required'
        });
        // Clear invalid token
        localStorage.removeItem('token');
        throw new Error('Authentication required');
      }
      
      const errorData = await response.json().catch(() => ({}));
      const errorMessage = errorData.message || `HTTP ${response.status}: ${response.statusText}`;
      
      // Clear token on 401 errors
      if (response.status === 401) {
        localStorage.removeItem('token');
      }
      
      console.error('API Error:', {
        url,
        status: response.status,
        statusText: response.statusText,
        error: errorMessage
      });
      throw new Error(errorMessage);
    }

    const result = await response.json();
    logger.debug(`Request successful: ${method} ${url}`);
    return result;
  } catch (error) {
    if (error instanceof TypeError && error.message === 'Failed to fetch') {
      console.error('Network Error:', {
        url,
        message: 'Unable to connect to API server. Please check if the backend is running.',
        suggestion: 'Make sure the Laravel backend is running on https://api.fishtrackpro.ru',
        error: error.message,
        stack: error.stack
      });
      throw new Error('Не удается подключиться к серверу. Проверьте, запущен ли backend на https://api.fishtrackpro.ru');
    }
    
    console.error('Request failed:', {
      url,
      method,
      error: error instanceof Error ? error.message : 'Unknown error',
      stack: error instanceof Error ? error.stack : undefined
    });
    throw error;
  }
}

