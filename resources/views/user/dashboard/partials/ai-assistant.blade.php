{{-- resources/views/dashboard/partials/ai-assistant.blade.php --}}
<div class="lg:col-span-1">
    <div class="bg-white p-5 sm:p-6 rounded-2xl shadow-custom-soft border border-gray-100 h-full flex flex-col">
        <h2 class="text-lg sm:text-xl font-bold text-brand flex items-center mb-3">
            <i data-lucide="sparkles" class="w-5 h-5 mr-2 text-accent"></i>
            AI Learning Assistant
        </h2>
        <p class="text-sm text-gray-700 mb-4">
            Tanyakan ringkasan kursus, istilah sulit, atau minta rekomendasi pelatihan lanjutan.
        </p>

        <div class="mt-auto">
            <input type="text"
                class="w-full p-3 mb-3 border border-gray-300 rounded-lg text-sm focus:ring-accent focus:border-accent"
                placeholder="Contoh: Apa itu B3?" />
            <x-ui.button variant="primary" class="w-full">
                <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                Tanya AI
            </x-ui.button>
            <p class="text-xs text-gray-500 mt-4 italic text-center">
                "AI ini dapat membantu Anda memahami materi lebih cepat."
            </p>
        </div>
    </div>
</div>