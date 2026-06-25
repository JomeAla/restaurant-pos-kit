import { useState, useEffect } from 'react';

export function useOffline() {
    const [isOnline, setIsOnline] = useState(navigator.onLine);
    const [pendingSync, setPendingSync] = useState(0);

    useEffect(() => {
        const handleOnline = () => setIsOnline(true);
        const handleOffline = () => setIsOnline(false);
        window.addEventListener('online', handleOnline);
        window.addEventListener('offline', handleOffline);
        return () => {
            window.removeEventListener('online', handleOnline);
            window.removeEventListener('offline', handleOffline);
        };
    }, []);

    useEffect(() => {
        const interval = setInterval(() => {
            if ('indexedDB' in window && isOnline) {
                const request = indexedDB.open('pos-offline', 1);
                request.onsuccess = (e) => {
                    const db = e.target.result;
                    const tx = db.transaction('orders', 'readonly');
                    const store = tx.objectStore('orders');
                    const count = store.count();
                    count.onsuccess = () => setPendingSync(count.result);
                };
            }
        }, 3000);
        return () => clearInterval(interval);
    }, [isOnline]);

    return { isOnline, pendingSync };
}
