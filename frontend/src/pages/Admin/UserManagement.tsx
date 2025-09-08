import React, { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import AdminLayout from '../../components/Admin/AdminLayout';
import DataTable from '../../components/Admin/DataTable';
import BlockModal from '../../components/Admin/BlockModal';
import Icon from '../../components/Icon';
import { adminUsers, adminToggleBlockUser, adminDeleteUser } from '../../api';

const UserManagement: React.FC = () => {
  const [users, setUsers] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [blockModal, setBlockModal] = useState<{
    isOpen: boolean;
    user: any | null;
  }>({ isOpen: false, user: null });
  const [filters, setFilters] = useState({
    status: '',
    role: '',
    search: ''
  });

  const loadUsers = async () => {
    try {
      setLoading(true);
      const response = await adminUsers(filters);
      setUsers(response.data.data);
    } catch (error) {
      console.error('Ошибка загрузки пользователей:', error);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadUsers();
  }, [filters]);

  const handleBlockUser = async (reason: string) => {
    if (!blockModal.user) return;

    try {
      await adminToggleBlockUser(blockModal.user.id, reason);
      setBlockModal({ isOpen: false, user: null });
      loadUsers();
    } catch (error) {
      console.error('Ошибка блокировки пользователя:', error);
    }
  };

  const handleDeleteUser = async (user: any) => {
    if (!confirm(`Удалить пользователя ${user.name}?`)) return;

    try {
      await adminDeleteUser(user.id);
      loadUsers();
    } catch (error) {
      console.error('Ошибка удаления пользователя:', error);
    }
  };

  const columns = [
    {
      key: 'name',
      label: 'Пользователь',
      render: (value: string, row: any) => (
        <div className="user-cell">
          <div className="user-cell__avatar">
            <Icon name="person" />
          </div>
          <div className="user-cell__info">
            <div className="user-cell__name">{row.name}</div>
            <div className="user-cell__username">@{row.username}</div>
          </div>
        </div>
      )
    },
    {
      key: 'email',
      label: 'Email'
    },
    {
      key: 'role',
      label: 'Роль',
      render: (value: string) => (
        <span className={`role-badge role-badge--${value}`}>
          {value === 'admin' ? 'Администратор' : 'Пользователь'}
        </span>
      )
    },
    {
      key: 'is_blocked',
      label: 'Статус',
      render: (value: boolean) => (
        <span className={`status-badge ${value ? 'blocked' : 'active'}`}>
          <Icon name={value ? 'block' : 'check_circle'} />
          {value ? 'Заблокирован' : 'Активен'}
        </span>
      )
    },
    {
      key: 'created_at',
      label: 'Дата регистрации',
      render: (value: string) => new Date(value).toLocaleDateString()
    }
  ];

  const actions = (user: any) => (
    <div className="table-actions">
      <Link 
        to={`/admin/users/${user.id}`}
        className="btn btn--sm btn--primary"
        title="Просмотр"
      >
        <Icon name="visibility" />
      </Link>
      
      <button
        className={`btn btn--sm ${user.is_blocked ? 'btn--success' : 'btn--warning'}`}
        onClick={() => setBlockModal({ isOpen: true, user })}
        title={user.is_blocked ? 'Разблокировать' : 'Заблокировать'}
      >
        <Icon name={user.is_blocked ? 'lock_open' : 'block'} />
      </button>
      
      {user.role !== 'admin' && (
        <button
          className="btn btn--sm btn--danger"
          onClick={() => handleDeleteUser(user)}
          title="Удалить"
        >
          <Icon name="delete" />
        </button>
      )}
    </div>
  );

  return (
    <AdminLayout>
      <div className="admin-page">
        <div className="admin-page__header">
          <h1>Управление пользователями</h1>
        </div>

        {/* Filters */}
        <div className="admin-filters">
          <div className="admin-filters__group">
            <select
              value={filters.status}
              onChange={(e) => setFilters({ ...filters, status: e.target.value })}
              className="form-select"
            >
              <option value="">Все статусы</option>
              <option value="active">Активные</option>
              <option value="blocked">Заблокированные</option>
            </select>
          </div>

          <div className="admin-filters__group">
            <select
              value={filters.role}
              onChange={(e) => setFilters({ ...filters, role: e.target.value })}
              className="form-select"
            >
              <option value="">Все роли</option>
              <option value="user">Пользователи</option>
              <option value="admin">Администраторы</option>
            </select>
          </div>

          <div className="admin-filters__group">
            <input
              type="text"
              placeholder="Поиск по имени, username или email..."
              value={filters.search}
              onChange={(e) => setFilters({ ...filters, search: e.target.value })}
              className="form-input"
            />
          </div>
        </div>

        {/* Data Table */}
        <DataTable
          columns={columns}
          data={users}
          loading={loading}
          actions={actions}
        />

        {/* Block Modal */}
        <BlockModal
          isOpen={blockModal.isOpen}
          onClose={() => setBlockModal({ isOpen: false, user: null })}
          onConfirm={handleBlockUser}
          title={blockModal.user?.is_blocked ? 'Разблокировать пользователя' : 'Заблокировать пользователя'}
          itemName={blockModal.user?.name || ''}
          isBlocked={blockModal.user?.is_blocked || false}
        />
      </div>
    </AdminLayout>
  );
};

export default UserManagement;
