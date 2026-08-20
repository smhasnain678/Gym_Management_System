/**
 * WarmUp Gym Management — Application Entry Point
 * Phase 14: Registers the Service Worker and initialises offline support.
 */

// Register the Service Worker (offline-first, PRD Section 9)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .then((reg) => {
                console.log('[WarmUp] Service Worker registered:', reg.scope);
            })
            .catch((err) => {
                console.warn('[WarmUp] Service Worker registration failed:', err);
            });
    });
}

// Import and initialise the offline queue manager
import { initOfflineSupport } from './offline.js';
document.addEventListener('DOMContentLoaded', () => {
    initOfflineSupport();
});
