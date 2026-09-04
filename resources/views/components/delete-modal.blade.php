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
</script>
