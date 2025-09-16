import React, { useState, useEffect, useCallback } from 'react';
import { useSearchParams, useNavigate } from 'react-router-dom';
import { search, getSearchSuggestions } from '../api';
import type { SearchResult, SearchFilters, SearchResponse } from '../types';
import Icon from '../components/Icon';
import Avatar from '../components/Avatar';

const SearchPage: React.FC = () => {
  const [searchParams, setSearchParams] = useSearchParams();
  const navigate = useNavigate();
  
  const [query, setQuery] = useState(searchParams.get('q') || '');
  const [results, setResults] = useState<SearchResult[]>([]);
  const [loading, setLoading] = useState(false);
  const [suggestions, setSuggestions] = useState<string[]>([]);
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [filters, setFilters] = useState<SearchFilters>({
    type: undefined,
    sort_by: 'relevance',
    sort_order: 'desc'
  });
  const [showFilters, setShowFilters] = useState(false);
  const [searchResponse, setSearchResponse] = useState<SearchResponse | null>(null);
  const [currentPage, setCurrentPage] = useState(1);

  const searchTypes = [
    { value: 'catch', label: 'Уловы', icon: 'fishing' },
    { value: 'event', label: 'Мероприятия', icon: 'event' },
    { value: 'track', label: 'Треки', icon: 'route' },
    { value: 'user', label: 'Пользователи', icon: 'person' },
    { value: 'point', label: 'Места', icon: 'place' },
    { value: 'fish_species', label: 'Виды рыб', icon: 'pets' },
    { value: 'fishing_method', label: 'Методы ловли', icon: 'build' },
    { value: 'bait', label: 'Наживки', icon: 'bug_report' },
    { value: 'location', label: 'Места ловли', icon: 'location_on' }
  ];

  const sortOptions = [
    { value: 'relevance', label: 'По релевантности' },
    { value: 'date', label: 'По дате' },
    { value: 'popularity', label: 'По популярности' },
    { value: 'rating', label: 'По рейтингу' }
  ];

  // Debounced search
  const debouncedSearch = useCallback(
    debounce(async (searchQuery: string, searchFilters: SearchFilters, page: number = 1) => {
      if (!searchQuery.trim()) {
        setResults([]);
        setSearchResponse(null);
        return;
      }

    try {
      setLoading(true);
        const response = await search({
          query: searchQuery,
          filters: searchFilters,
          page,
          per_page: 20
        });
        
        setResults(response.results);
        setSearchResponse(response);
        setCurrentPage(page);
      } catch (error) {
        console.error('Search error:', error);
        setResults([]);
        setSearchResponse(null);
      } finally {
        setLoading(false);
      }
    }, 300),
    []
  );

  // Load suggestions
  const loadSuggestions = useCallback(
    debounce(async (searchQuery: string) => {
      if (searchQuery.length < 2) {
        setSuggestions([]);
        return;
      }

      try {
        const response = await getSearchSuggestions(searchQuery, filters.type);
        setSuggestions(response.suggestions);
      } catch (error) {
        console.error('Suggestions error:', error);
      }
    }, 200),
    [filters.type]
  );

  // Initial search
  useEffect(() => {
    if (query) {
      debouncedSearch(query, filters, 1);
    }
  }, [query, filters, debouncedSearch]);

  // Load suggestions when query changes
  useEffect(() => {
    loadSuggestions(query);
  }, [query, loadSuggestions]);

  // Update URL when query changes
  useEffect(() => {
    const params = new URLSearchParams();
    if (query) params.set('q', query);
    if (filters.type) params.set('type', filters.type);
    if (filters.sort_by) params.set('sort_by', filters.sort_by);
    if (filters.sort_order) params.set('sort_order', filters.sort_order);
    
    setSearchParams(params, { replace: true });
  }, [query, filters, setSearchParams]);

  // Removed unused handleSearch function

  const handleSuggestionClick = (suggestion: string) => {
    setQuery(suggestion);
    setShowSuggestions(false);
  };

  const handleFilterChange = (newFilters: Partial<SearchFilters>) => {
    setFilters(prev => ({ ...prev, ...newFilters }));
    setCurrentPage(1);
  };

  const handlePageChange = (page: number) => {
    debouncedSearch(query, filters, page);
  };

  const getResultIcon = (type: string) => {
    const typeConfig = searchTypes.find(t => t.value === type);
    return typeConfig?.icon || 'search';
  };

  const getResultLabel = (type: string) => {
    const typeConfig = searchTypes.find(t => t.value === type);
    return typeConfig?.label || 'Результат';
  };

  const getResultUrl = (result: SearchResult) => {
    switch (result.type) {
      case 'catch':
        return `/catches/${result.id}`;
      case 'event':
        return `/events/${result.id}`;
      case 'track':
        return `/tracks/${result.id}`;
      case 'user':
        return `/users/${result.id}`;
      case 'point':
        return `/points/${result.id}`;
      case 'fish_species':
        return `/reference/fish/${result.id}`;
      case 'fishing_method':
        return `/reference/methods/${result.id}`;
      case 'bait':
        return `/reference/baits/${result.id}`;
      case 'location':
        return `/reference/locations/${result.id}`;
      default:
        return '#';
    }
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  };

  const renderResult = (result: SearchResult) => (
    <div 
      key={`${result.type}-${result.id}`}
      className="search-result"
      onClick={() => navigate(getResultUrl(result))}
    >
      <div className="result-image">
        {result.image ? (
          <img src={result.image} alt={result.title} />
        ) : (
          <div className="result-icon">
            <Icon name={getResultIcon(result.type)} size="md" />
          </div>
        )}
      </div>
      
      <div className="result-content">
        <div className="result-header">
          <h3 className="result-title">{result.title}</h3>
          <span className="result-type">
            <Icon name={getResultIcon(result.type)} size="sm" />
            {getResultLabel(result.type)}
          </span>
        </div>
        
        {result.description && (
          <p className="result-description">{result.description}</p>
        )}
        
        <div className="result-meta">
          {result.user && (
            <div className="result-user">
              <Avatar 
                src={result.user.photo_url} 
                size="md" 
                name={result.user.name}
                isGuide={result.user.is_guide}
                guideIconUrl={result.user.guide_icon_url}
              />
              <span>{result.user.name}</span>
            </div>
          )}
          
          <div className="result-date">
            <Icon name="schedule" size="sm" />
            <span>{formatDate(result.created_at)}</span>
          </div>
        </div>
      </div>
    </div>
  );

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
      <div className="max-w-4xl mx-auto px-4 py-8">
        {/* Header */}
        <div className="glass-surface p-6 mb-8">
          <h1 className="md3-headline-large text-center">Поиск</h1>
        </div>
        
        <div className="space-y-6">
          {/* Search Input */}
          <div className="glass-card p-6">
            <div className="relative">
              <div className="flex items-center space-x-3">
                <Icon name="search" size={24} className="text-gray-500" />
                <input
                  type="text"
                  className="flex-1 px-4 py-3 border border-gray-300 rounded-2xl bg-white/50 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  placeholder="Поиск по уловам, мероприятиям, трекам, пользователям, местам..."
                  value={query}
                  onChange={(e) => setQuery(e.target.value)}
                  onFocus={() => setShowSuggestions(true)}
                  onBlur={() => setTimeout(() => setShowSuggestions(false), 200)}
                />
                {query && (
                  <button
                    className="md3-button md3-button-text p-2"
                    onClick={() => setQuery('')}
                  >
                    <Icon name="close" size={20} />
                  </button>
                )}
              </div>
              
              {/* Suggestions */}
              {showSuggestions && suggestions.length > 0 && (
                <div className="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden z-10">
                  {suggestions.map((suggestion, index) => (
                    <div
                      key={index}
                      className="flex items-center space-x-3 px-4 py-3 hover:bg-gray-50 cursor-pointer"
                      onClick={() => handleSuggestionClick(suggestion)}
                    >
                      <Icon name="search" size={16} className="text-gray-500" />
                      <span className="md3-body-medium">{suggestion}</span>
                    </div>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Filters */}
          <div className="glass-card p-6">
            <button
              className="md3-button md3-button-outlined flex items-center space-x-2"
              onClick={() => setShowFilters(!showFilters)}
            >
              <Icon name="tune" size={20} />
              <span>Фильтры</span>
            </button>
          
          {showFilters && (
            <div className="filters-panel">
              <div className="filter-group">
                <label>Тип контента</label>
                <div className="filter-options">
                  {searchTypes.map((type) => (
                    <label key={type.value} className="filter-option">
                      <input
                        type="radio"
                        name="type"
                        value={type.value}
                        checked={filters.type === type.value}
                        onChange={(e) => handleFilterChange({ type: e.target.value as any })}
                      />
                      <span className="filter-option-custom"></span>
                      <Icon name={type.icon} size="sm" />
                      <span>{type.label}</span>
                    </label>
                  ))}
                </div>
              </div>
              
              <div className="filter-group">
                <label>Сортировка</label>
                <select
                  value={filters.sort_by}
                  onChange={(e) => handleFilterChange({ sort_by: e.target.value as any })}
                >
                  {sortOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                      {option.label}
                    </option>
                  ))}
                </select>
              </div>
              
              <div className="filter-group">
                <label>Порядок</label>
                <select
                  value={filters.sort_order}
                  onChange={(e) => handleFilterChange({ sort_order: e.target.value as any })}
                >
                  <option value="desc">По убыванию</option>
                  <option value="asc">По возрастанию</option>
                </select>
              </div>
            </div>
          )}
        </div>

        {/* Results */}
        <div className="search-results">
          {loading ? (
            <div className="loading">
              <Icon name="hourglass_empty" size="md" />
              <span>Поиск...</span>
            </div>
          ) : results.length > 0 ? (
            <>
              <div className="results-header">
                <h2>Результаты поиска</h2>
                <span className="results-count">
                  Найдено: {searchResponse?.total || 0}
                </span>
                          </div>
              
              <div className="results-list">
                {results.map(renderResult)}
                        </div>
              
              {/* Pagination */}
              {searchResponse && searchResponse.last_page > 1 && (
                <div className="pagination">
                  <button 
                    className="pagination-btn"
                    disabled={currentPage === 1}
                    onClick={() => handlePageChange(currentPage - 1)}
                  >
                    <Icon name="chevron_left" size="sm" />
                    Назад
                  </button>
                  
                  <div className="pagination-info">
                    Страница {currentPage} из {searchResponse.last_page}
                </div>
                  
                  <button 
                    className="pagination-btn"
                    disabled={currentPage === searchResponse.last_page}
                    onClick={() => handlePageChange(currentPage + 1)}
                  >
                    Вперед
                    <Icon name="chevron_right" size="sm" />
                  </button>
                </div>
              )}
            </>
          ) : query ? (
            <div className="no-results">
              <Icon name="search_off" size="xl" />
              <h3>Ничего не найдено</h3>
              <p>Попробуйте изменить поисковый запрос или фильтры</p>
            </div>
          ) : (
            <div className="search-placeholder">
              <Icon name="search" size="xl" />
              <h3>Начните поиск</h3>
              <p>Введите запрос в поле выше</p>
            </div>
          )}
          </div>
        </div>
      </div>
    </div>
  );
};

// Debounce utility
function debounce<T extends (...args: any[]) => any>(
  func: T,
  wait: number
): (...args: Parameters<T>) => void {
  let timeout: number;
  return (...args: Parameters<T>) => {
    clearTimeout(timeout);
    timeout = window.setTimeout(() => func(...args), wait);
  };
}

export default SearchPage;