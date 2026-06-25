export function openDb() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('pos-offline', 1);
        request.onupgradeneeded = (e) => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('orders')) {
                db.createObjectStore('orders', { keyPath: 'localId' });
            }
            if (!db.objectStoreNames.contains('syncQueue')) {
                db.createObjectStore('syncQueue', { keyPath: 'id', autoIncrement: true });
            }
        };
        request.onsuccess = (e) => resolve(e.target.result);
        request.onerror = (e) => reject(e.target.error);
    });
}

export async function saveOfflineOrder(order) {
    const db = await openDb();
    const tx = db.transaction('orders', 'readwrite');
    tx.objectStore('orders').put({ localId: Date.now().toString(), ...order, createdAt: new Date().toISOString() });
    return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}

export async function getOfflineOrders() {
    const db = await openDb();
    const tx = db.transaction('orders', 'readonly');
    const store = tx.objectStore('orders');
    const all = store.getAll();
    return new Promise((resolve) => {
        all.onsuccess = () => resolve(all.result);
    });
}

export async function clearOfflineOrders() {
    const db = await openDb();
    const tx = db.transaction('orders', 'readwrite');
    tx.objectStore('orders').clear();
    return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror = (e) => reject(e.target.error);
    });
}
