import React from 'react'
import ReactDOM from 'react-dom/client'
import AppRoot from './AppRoot.tsx'
import './styles/app.css'
import './styles/modern-cards.css'

// Mobile fullscreen support - hide address bar
const hideAddressBar = () => {
  // For iOS Safari
  if ((window.navigator as any).standalone === true) {
    return;
  }
  
  // For other mobile browsers
  if (window.innerHeight < window.outerHeight) {
    window.scrollTo(0, 1);
  }
  
  // Force viewport height
  const vh = window.innerHeight * 0.01;
  document.documentElement.style.setProperty('--vh', `${vh}px`);
};

// Run on load and resize
window.addEventListener('load', hideAddressBar);
window.addEventListener('resize', hideAddressBar);
window.addEventListener('orientationchange', () => {
  setTimeout(hideAddressBar, 100);
});

// Prevent zoom on double tap
let lastTouchEnd = 0;
document.addEventListener('touchend', (event) => {
  const now = (new Date()).getTime();
  if (now - lastTouchEnd <= 300) {
    event.preventDefault();
  }
  lastTouchEnd = now;
}, false);

ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <AppRoot />
  </React.StrictMode>,
)

