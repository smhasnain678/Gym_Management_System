<div id="delete-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden relative">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-lg font-bold text-gray-900 mb-2" id="delete-modal-title">{{ __('Delete?') }}</h3>
            <button type="button" onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form id="delete-form" method="POST" class="p-6 space-y-4">
            @csrf
            @method('DELETE')
            <p class="text-sm" style="color:#374151;" id="delete-modal-message">
                {{ __('Are you sure you want to delete this item?') }}
            </p>
            <div id="delete-modal-warning" class="hidden p-3 rounded-xl text-sm" style="background-color:#FEF3C7; color:#92400E; border:1px solid #FDE68A;">
                <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                <span id="delete-modal-warning-text"></span>
            </div>
            <div class="pt-2 flex justify-end gap-3 border-t border-gray-100 mt-4">
                <button type="button"
                        onclick="closeDeleteModal()"
                        class="px-5 py-2.5 text-sm font-semibold rounded-xl border-2 border-gray-300 bg-white hover:bg-gray-50 transition-colors"
                        style="color:#374151;">
                    {{ __('Cancel') }}
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 shadow-sm transition-all active:scale-95">
                    {{ __('Yes, Delete') }}
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    function openDeleteModal(url, title, message, warning = null) {
        document.getElementById('delete-form').action = url;
        document.getElementById('delete-modal-title').textContent = title;
        document.getElementById('delete-modal-message').innerHTML = message;
        
        const warningDiv = document.getElementById('delete-modal-warning');
        if (warning) {
            document.getElementById('delete-modal-warning-text').innerHTML = warning;
            warningDiv.classList.remove('hidden');
        } else {
            warningDiv.classList.add('hidden');
        }
        
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
    }

    // ── Offline-First: Delete Member ─────────────────────────────────────────
    // When ONLINE  → native form DELETE submission proceeds unchanged.
    // When OFFLINE → if the target URL is a members.destroy route, extract the
    //                member ID, queue as `member_delete`, close the modal, and
    //                show an offline toast.  Non-member deletes are not handled
    //                offline (no other delete action type exists in the backend).
    document.addEventListener('DOMContentLoaded', function () {
        var deleteForm = document.getElementById('delete-form');
        if (!deleteForm) return;

        deleteForm.addEventListener('submit', async function (e) {
            // If online, proceed with the normal form submission.
            if (navigator.onLine) return;

            // Extract the action URL to determine if this is a member delete.
            // members.destroy routes follow the pattern: /members/{id}
            var action = deleteForm.action || '';
            var match  = action.match(/\/members\/(\d+)(?:\?.*)?$/);

            if (!match) {
                // Not a member delete — we have no offline handler for other
                // resource types, so let the form submit (will fail offline,
                // which is the honest behaviour for unsupported types).
                return;
            }

            // ── We are OFFLINE and this is a member delete ────────────────────
            e.preventDefault();

            var memberId = parseInt(match[1], 10);

            // Close the modal immediately so the UI feels responsive.
            closeDeleteModal();

            // Guard: WarmUpOffline may not be ready if the bundle hasn't loaded.
            if (!window.WarmUpOffline || !window.WarmUpOffline.queueMemberDelete) {
                console.error('[WarmUp Offline] queueMemberDelete not available.');
                showDeleteOfflineToast(
                    'Could not queue deletion offline — please try again.',
                    '#FEE2E2', '#DC2626'
                );
                return;
            }

            try {
                await window.WarmUpOffline.queueMemberDelete(memberId);
            } catch (err) {
                console.error('[WarmUp Offline] Failed to queue member_delete:', err);
                showDeleteOfflineToast(
                    'Could not save deletion offline. Please try again.',
                    '#FEE2E2', '#DC2626'
                );
                return;
            }

            showDeleteOfflineToast(
                'Deletion queued offline. The member will be removed when you reconnect.',
                '#DCFCE7', '#15803D'
            );
        });
    });

    /**
     * Display a brief fixed toast at the top of the viewport.
     * Auto-dismisses after 4 seconds.
     */
    function showDeleteOfflineToast(message, bgColor, textColor) {
        var existing = document.getElementById('offline-delete-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.id = 'offline-delete-toast';
        toast.style.cssText = [
            'position:fixed', 'top:20px', 'left:50%',
            'transform:translateX(-50%)',
            'z-index:9999',
            'display:flex', 'align-items:center', 'gap:10px',
            'padding:14px 20px', 'border-radius:14px',
            'font-size:0.875rem', 'font-weight:500',
            'box-shadow:0 4px 24px rgba(0,0,0,0.12)',
            'max-width:90vw',
            'background-color:' + bgColor,
            'color:' + textColor,
            'transition:opacity 0.5s ease',
        ].join(';');

        toast.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"' +
            ' fill="none" stroke="currentColor" stroke-width="2"' +
            ' stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0">' +
            '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>' +
            '<polyline points="22 4 12 13.01 9 10.01"/></svg>' +
            '<span>' + message + '</span>';

        document.body.appendChild(toast);

        // Auto-dismiss after 4 seconds
        setTimeout(function () {
            toast.style.opacity = '0';
            setTimeout(function () { toast.remove(); }, 500);
        }, 4000);
    }
</script>
