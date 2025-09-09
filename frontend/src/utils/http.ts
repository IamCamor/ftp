import appConfig from '../config';

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
}

export async function request(path: string, options: RequestOptions = {}) {
  const { method = 'GET', data, auth = false, params } = options;
  
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
  
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
  };

  if (auth) {
    const token = localStorage.getItem('token');
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
  }

  const requestConfig: RequestInit = {
    method,
    headers,
  };

  if (data && method !== 'GET') {
    requestConfig.body = JSON.stringify(data);
  }

  try {
    console.log(`Making ${method} request to: ${url}`);
    
    const response = await fetch(url, requestConfig);
    
    if (!response.ok) {
      const errorData = await response.json().catch(() => ({}));
      const errorMessage = errorData.message || `HTTP ${response.status}: ${response.statusText}`;
      console.error('API Error:', {
        url,
        status: response.status,
        statusText: response.statusText,
        error: errorMessage
      });
      throw new Error(errorMessage);
    }

    const result = await response.json();
    console.log(`Request successful: ${method} ${url}`);
    return result;
  } catch (error) {
    if (error instanceof TypeError && error.message === 'Failed to fetch') {
      console.error('Network Error:', {
        url,
        message: 'Unable to connect to API server. Please check if the backend is running.',
        suggestion: 'Make sure the Laravel backend is running on http://localhost:8000'
      });
      throw new Error('Не удается подключиться к серверу. Проверьте, запущен ли backend на http://localhost:8000');
    }
    
    console.error('Request failed:', {
      url,
      method,
      error: error instanceof Error ? error.message : 'Unknown error'
    });
    throw error;
  }
}

