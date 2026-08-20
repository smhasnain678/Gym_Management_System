/**
 * WarmUp Gym Management — Offline Queue Manager
 * Phase 14: Offline-First with IndexedDB
 *
 * This module provides:
 *  1. IndexedDB storage for offline action queues
 *  2. Auto-sync when network connectivity is restored
 *  3. Network status indicator updates (Online / Offline / Syncing / Synced)
 */

const DB_NAME    = 'warmup_offline';
const DB_VERSION = 1;
const STORE_NAME = 'action_queue';
const SYNC_URL   = '/api/offline/sync';
const BATCH_SIZE = 50; // max actions per sync request

let db = null;

// ─── Open IndexedDB ──────────────────────────────────────────────────────────
function openDB() {
    return new Promise((resolve, reject) => {
        if (db) return resolve(db);

        const request = indexedDB.open(DB_NAME, DB_VERSION);

        request.onupgradeneeded = (event) => {
            const database = event.target.result;
            if (!database.objectStoreNames.contains(STORE_NAME)) {
                const store = database.createObjectStore(STORE_NAME, {
                    keyPath: 'id',
                    autoIncrement: true,
                });
                store.createIndex('by_type',       'type',       { unique: false });
                store.createIndex('by_created_at', 'created_at', { unique: false });
            }
        };

        request.onsuccess = (event) => {
            db = event.target.result;
            resolve(db);
        };

        request.onerror = () => reject(request.error);
    });
}

// ─── Queue an offline action ──────────────────────────────────────────────────
export async function queueAction(type, data) {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const tx    = database.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);

        const record = {
            type,
            data,
            created_at: new Date().toISOString(),
        };

        const request = store.add(record);
        request.onsuccess = () => resolve(request.result);
        request.onerror   = () => reject(request.error);
    });
}

// ─── Phase 15: Convenience queue helpers ─────────────────────────────────────

/**
 * Queue an offline member deletion.
 * @param {number} memberId
 */
export function queueMemberDelete(memberId) {
    return queueAction('member_delete', { id: memberId });
}

/**
 * Queue an offline trainer creation.
 * @param {Object} payload - Trainer fields (name, phone, gender, joining_date, ...)
 */
export function queueTrainerCreate(payload) {
    return queueAction('trainer_create', payload);
}

/**
 * Queue an offline trainer update.
 * Automatically attaches the trainer's current updated_at so LWW can be enforced.
 * @param {number} trainerId
 * @param {Object} payload - Fields to update
 * @param {string} currentUpdatedAt - The trainer's updated_at value from local state
 */
export function queueTrainerUpdate(trainerId, payload, currentUpdatedAt) {
    return queueAction('trainer_update', {
        id: trainerId,
        client_updated_at: currentUpdatedAt,
        ...payload,
    });
}

/**
 * Queue an offline member update (ensures client_updated_at is included for LWW).
 * @param {number} memberId
 * @param {Object} payload - Fields to update
 * @param {string} currentUpdatedAt - The member's updated_at value from local state
 */
export function queueMemberUpdate(memberId, payload, currentUpdatedAt) {
    return queueAction('member_update', {
        id: memberId,
        client_updated_at: currentUpdatedAt,
        ...payload,
    });
}

/**
 * Queue an offline settings update.
 * Automatically attaches client_updated_at for LWW.
 * @param {Object} payload - Settings fields to update
 * @param {string} currentUpdatedAt - The gym_settings updated_at from local state
 */
export function queueSettingsUpdate(payload, currentUpdatedAt) {
    return queueAction('settings_update', {
        client_updated_at: currentUpdatedAt,
        ...payload,
    });
}

// ─── Get all queued actions ───────────────────────────────────────────────────
async function getAllQueued() {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const tx    = database.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const req   = store.getAll();

        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    });
}

// ─── Remove synced actions by IDs ────────────────────────────────────────────
async function removeByIds(ids) {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const tx    = database.transaction(STORE_NAME, 'readwrite');
        const store = tx.objectStore(STORE_NAME);
        let pending = ids.length;

        if (pending === 0) return resolve();

        ids.forEach((id) => {
            const req    = store.delete(id);
            req.onsuccess = () => { if (--pending === 0) resolve(); };
            req.onerror   = () => reject(req.error);
        });
    });
}

// ─── Get pending queue count ──────────────────────────────────────────────────
export async function getPendingCount() {
    const database = await openDB();

    return new Promise((resolve, reject) => {
        const tx    = database.transaction(STORE_NAME, 'readonly');
        const store = tx.objectStore(STORE_NAME);
        const req   = store.count();

        req.onsuccess = () => resolve(req.result);
        req.onerror   = () => reject(req.error);
    });
}

// ─── Network Status Indicator ────────────────────────────────────────────────
function setStatusOnline() {
    updateStatusIndicator('Online', '#DCFCE7', '#15803D', '#22C55E');
}

function setStatusOffline() {
    updateStatusIndicator('Offline', '#FEE2E2', '#DC2626', '#DC2626');
}

function setStatusSyncing() {
    updateStatusIndicator('Syncing...', '#FEF3C7', '#92400E', '#F59E0B');
}

function setStatusSynced() {
    updateStatusIndicator('Synced ✓', '#DCFCE7', '#15803D', '#22C55E');
    // Revert to "Online" after 3 seconds
    setTimeout(setStatusOnline, 3000);
}

function updateStatusIndicator(text, bgColor, textColor, dotColor) {
    const el       = document.getElementById('network-status');
    const textEl   = document.getElementById('network-status-text');
    const dotEl    = el ? el.querySelector('span') : null;

    if (!el || !textEl) return;

    el.style.backgroundColor = bgColor;
    el.style.color           = textColor;
    if (dotEl) dotEl.style.backgroundColor = dotColor;
    textEl.textContent = text;
}

// ─── Sync queued actions to server ───────────────────────────────────────────
export async function syncQueuedActions() {
    const queued = await getAllQueued();
    if (queued.length === 0) {
        setStatusOnline();
        return;
    }

    setStatusSyncing();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    let   allSynced = true;

    // Process in batches
    for (let i = 0; i < queued.length; i += BATCH_SIZE) {
        const batch   = queued.slice(i, i + BATCH_SIZE);
        const actions = batch.map(({ type, data }) => ({ type, data }));

        try {
            const response = await fetch(SYNC_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'Accept':       'application/json',
                },
                body: JSON.stringify({ actions }),
            });

            if (!response.ok && response.status !== 422) {
                allSynced = false;
                console.warn('Sync batch returned HTTP', response.status);
                continue;
            }

            const result = await response.json();

            // Remove successfully synced actions from the queue.
            // Conflicts and errors are retained for manual resolution or retry.
            const successIds = result.results
                .filter((r) => r.status === 'success')
                .map((r) => batch[r.index]?.id)
                .filter(Boolean);

            await removeByIds(successIds);

            if (result.failed > 0) {
                allSynced = false;
                console.warn(`${result.failed} action(s) failed to sync.`);
            }

            if (result.conflicts > 0) {
                allSynced = false;
                console.warn(`${result.conflicts} action(s) had conflicts (server record is newer). Items kept in queue.`);
            }

        } catch (err) {
            allSynced = false;
            console.error('Sync error:', err);
            break;
        }
    }

    if (allSynced) {
        setStatusSynced();
    } else {
        setStatusOnline(); // partial sync — revert to online, will retry next time
    }
}

// ─── Bootstrap ───────────────────────────────────────────────────────────────
export function initOfflineSupport() {
    // Set initial status
    if (navigator.onLine) {
        setStatusOnline();
    } else {
        setStatusOffline();
    }

    // Listen for connectivity changes
    window.addEventListener('online', async () => {
        await syncQueuedActions();
    });

    window.addEventListener('offline', () => {
        setStatusOffline();
    });

    // If online on load, check for pending items to sync
    if (navigator.onLine) {
        getPendingCount().then((count) => {
            if (count > 0) {
                syncQueuedActions();
            }
        });
    }
}
