import React from 'react';
import Icon from '../Icon';

interface Column {
  key: string;
  label: string;
  render?: (value: any, row: any) => React.ReactNode;
  sortable?: boolean;
}

interface DataTableProps {
  columns: Column[];
  data: any[];
  loading?: boolean;
  onSort?: (column: string, direction: 'asc' | 'desc') => void;
  sortColumn?: string;
  sortDirection?: 'asc' | 'desc';
  actions?: (row: any) => React.ReactNode;
}

const DataTable: React.FC<DataTableProps> = ({
  columns,
  data,
  loading = false,
  onSort,
  sortColumn,
  sortDirection,
  actions
}) => {
  const handleSort = (column: string) => {
    if (!onSort) return;
    
    const newDirection = sortColumn === column && sortDirection === 'asc' ? 'desc' : 'asc';
    onSort(column, newDirection);
  };

  if (loading) {
    return (
      <div className="data-table">
        <div className="data-table__loading">
          <Icon name="refresh" className="spinning" />
          <span>Загрузка...</span>
        </div>
      </div>
    );
  }

  if (data.length === 0) {
    return (
      <div className="data-table">
        <div className="data-table__empty">
          <Icon name="inbox" />
          <span>Данные не найдены</span>
        </div>
      </div>
    );
  }

  return (
    <div className="data-table">
      <table className="data-table__table">
        <thead>
          <tr>
            {columns.map((column) => (
              <th 
                key={column.key}
                className={`data-table__header ${column.sortable ? 'sortable' : ''}`}
                onClick={() => column.sortable && handleSort(column.key)}
              >
                <span>{column.label}</span>
                {column.sortable && (
                  <div className="data-table__sort">
                    {sortColumn === column.key && (
                      <Icon 
                        name={sortDirection === 'asc' ? 'keyboard_arrow_up' : 'keyboard_arrow_down'} 
                      />
                    )}
                  </div>
                )}
              </th>
            ))}
            {actions && <th className="data-table__header">Действия</th>}
          </tr>
        </thead>
        <tbody>
          {data.map((row, index) => (
            <tr key={row.id || index} className="data-table__row">
              {columns.map((column) => (
                <td key={column.key} className="data-table__cell">
                  {column.render 
                    ? column.render(row[column.key], row)
                    : row[column.key]
                  }
                </td>
              ))}
              {actions && (
                <td className="data-table__cell data-table__actions">
                  {actions(row)}
                </td>
              )}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default DataTable;
