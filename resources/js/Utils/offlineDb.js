const DB_NAME = "pos_offline_db";
const STORE = "pending_transactions";

const openDB = () =>
    new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = () =>
            req.result.createObjectStore(STORE, { keyPath: "id" });
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });

export const queueTransaction = async (payload) => {
    const db = await openDB();
    const tx = db.transaction(STORE, "readwrite");
    tx.objectStore(STORE).add({
        id: `${Date.now()}_${Math.random().toString(36).slice(2)}`,
        payload,
        createdAt: new Date().toISOString(),
    });
    return new Promise((res, rej) => {
        tx.oncomplete = res;
        tx.onerror = () => rej(tx.error);
    });
};
