import React from 'react';
import { Helmet } from 'react-helmet-async';

interface SEOHeadProps {
  title?: string;
  description?: string;
  keywords?: string[];
  image?: string;
  url?: string;
  type?: 'website' | 'article' | 'profile';
  author?: string;
  publishedTime?: string;
  modifiedTime?: string;
  section?: string;
  tags?: string[];
  canonical?: string;
  noindex?: boolean;
  nofollow?: boolean;
}

const SEOHead: React.FC<SEOHeadProps> = ({
  title = 'FishTrackPro - Социальная сеть для рыбаков',
  description = 'FishTrackPro - это социальная сеть для рыбаков, где можно делиться уловами, находить места для рыбалки, общаться с единомышленниками и получать полезную информацию о рыбалке.',
  keywords = ['рыбалка', 'уловы', 'места для рыбалки', 'рыбаки', 'социальная сеть', 'fishing', 'catch', 'fishing spots'],
  image = 'https://fishtrackpro.ru/og-image.jpg',
  url = 'https://fishtrackpro.ru',
  type = 'website',
  author,
  publishedTime,
  modifiedTime,
  section,
  tags,
  canonical,
  noindex = false,
  nofollow = false,
}) => {
  const fullTitle = title.includes('FishTrackPro') ? title : `${title} | FishTrackPro`;
  const fullUrl = canonical || url;
  
  const robotsContent = [
    noindex ? 'noindex' : 'index',
    nofollow ? 'nofollow' : 'follow',
  ].join(', ');

  return (
    <Helmet>
      {/* Basic Meta Tags */}
      <title>{fullTitle}</title>
      <meta name="description" content={description} />
      <meta name="keywords" content={keywords.join(', ')} />
      <meta name="robots" content={robotsContent} />
      <link rel="canonical" href={fullUrl} />
      
      {/* Open Graph Meta Tags */}
      <meta property="og:type" content={type} />
      <meta property="og:title" content={fullTitle} />
      <meta property="og:description" content={description} />
      <meta property="og:image" content={image} />
      <meta property="og:url" content={fullUrl} />
      <meta property="og:site_name" content="FishTrackPro" />
      <meta property="og:locale" content="ru_RU" />
      
      {/* Twitter Card Meta Tags */}
      <meta name="twitter:card" content="summary_large_image" />
      <meta name="twitter:title" content={fullTitle} />
      <meta name="twitter:description" content={description} />
      <meta name="twitter:image" content={image} />
      <meta name="twitter:site" content="@fishtrackpro" />
      <meta name="twitter:creator" content="@fishtrackpro" />
      
      {/* Article specific meta tags */}
      {type === 'article' && (
        <>
          {author && <meta property="article:author" content={author} />}
          {publishedTime && <meta property="article:published_time" content={publishedTime} />}
          {modifiedTime && <meta property="article:modified_time" content={modifiedTime} />}
          {section && <meta property="article:section" content={section} />}
          {tags && tags.map((tag, index) => (
            <meta key={index} property="article:tag" content={tag} />
          ))}
        </>
      )}
      
      {/* Additional Meta Tags */}
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <meta name="theme-color" content="#007bff" />
      <meta name="apple-mobile-web-app-capable" content="yes" />
      <meta name="apple-mobile-web-app-status-bar-style" content="default" />
      <meta name="apple-mobile-web-app-title" content="FishTrackPro" />
      
      {/* Favicon */}
      <link rel="icon" type="image/x-icon" href="/favicon.ico" />
      <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
      <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png" />
      <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png" />
      <link rel="manifest" href="/site.webmanifest" />
      
      {/* Structured Data */}
      <script type="application/ld+json">
        {JSON.stringify({
          "@context": "https://schema.org",
          "@type": type === 'article' ? 'Article' : 'WebSite',
          "name": fullTitle,
          "description": description,
          "url": fullUrl,
          "image": image,
          "publisher": {
            "@type": "Organization",
            "name": "FishTrackPro",
            "url": "https://fishtrackpro.ru",
            "logo": {
              "@type": "ImageObject",
              "url": "https://fishtrackpro.ru/logo.png"
            }
          },
          ...(type === 'article' && {
            "author": {
              "@type": "Person",
              "name": author
            },
            "datePublished": publishedTime,
            "dateModified": modifiedTime,
            "mainEntityOfPage": {
              "@type": "WebPage",
              "@id": fullUrl
            }
          }),
          ...(type === 'website' && {
            "potentialAction": {
              "@type": "SearchAction",
              "target": "https://fishtrackpro.ru/search?q={search_term_string}",
              "query-input": "required name=search_term_string"
            }
          })
        })}
      </script>
    </Helmet>
  );
};

export default SEOHead;
