import React, { useState, useEffect } from 'react';
import { bannersGet } from '../api';
import type { Banner } from '../types';

interface BannerSlotProps {
  slot: string;
  className?: string;
}

const BannerSlot: React.FC<BannerSlotProps> = ({ slot, className = '' }) => {
  const [banners, setBanners] = useState<Banner[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const loadBanners = async () => {
      try {
        const data = await bannersGet(slot);
        setBanners(data);
      } catch (error) {
        console.error('Failed to load banners:', error);
      } finally {
        setLoading(false);
      }
    };

    loadBanners();
  }, [slot]);

  if (loading) {
    return <div className={`banner-slot loading ${className}`}>Загрузка...</div>;
  }

  if (banners.length === 0) {
    return null;
  }

  return (
    <div className={`banner-slot ${className}`}>
      {banners.map((banner) => (
        <a
          key={banner.id}
          href={banner.click_url}
          target="_blank"
          rel="noopener noreferrer"
          className="banner-item"
        >
          <img
            src={banner.image_url}
            alt="Banner"
            className="banner-image"
          />
        </a>
      ))}
    </div>
  );
};

export default BannerSlot;

