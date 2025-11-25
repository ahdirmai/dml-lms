@props([
/**
* courses: array, contoh struktur tiap item:
* [
* 'id' => 1,
* 'title' => 'Nama Kursus',
* 'modules' => [
* ['id' => ..., 'title' => ..., 'status' => 'completed'|'in_progress'|...],
* ],
* 'preTest' => [
* ['id' => 1, 'q' => 'Apa itu X?', 'options' => ['A','B','C','D']],
* ],
* 'postTest' => [ ... ],
* 'submit_pre_url' => '...',
* 'submit_post_url' => '...',
* 'submit_review_url' => '...',
* ]
*/
'courses' => [],
])

{{-- ============ MODAL TEST (PRE & POST, DENGAN FORM POST) ============ --}}
<div id="modal-test" class="fixed inset-0 hidden items-center justify-center z-50 bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full p-6 overflow-auto max-h-[90vh]">
        <h3 id="test-title" class="text-xl font-bold text-brand mb-2"></h3>
        <div id="test-desc" class="text-sm text-gray-600 mb-4"></div>

        <form id="test-form" method="POST" class="space-y-4">
            @csrf
            <div id="test-questions" class="space-y-4"></div>

            <div class="flex justify-between items-center pt-3 border-t border-gray-100 mt-4">
                <div id="test-progress" class="text-sm text-gray-500"></div>
                <div class="flex space-x-2">
                    <x-ui.button variant="secondary" type="button" id="test-cancel">
                        Batal
                    </x-ui.button>
                    <x-ui.button variant="primary" type="button" id="test-submit">
                        Submit
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ============ MODAL GENERIC (INFO / ERROR) ============ --}}
<div id="modal-generic" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white p-6 rounded-xl shadow-2xl max-w-sm w-full">
        <h3 id="modal-title" class="text-xl font-bold mb-3 text-brand"></h3>
        <p id="modal-message" class="text-gray-700 mb-4"></p>
        <div class="flex justify-end">
            <x-ui.button variant="secondary" id="modal-close" class="w-full">
                Tutup
            </x-ui.button>
        </div>
    </div>
</div>

{{-- ============ MODAL REVIEW (FORM POST BIASA) ============ --}}
<div id="modal-review" class="fixed inset-0 hidden items-center justify-center z-50 bg-black/50 p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-brand mb-2">Berikan Ulasan</h3>
        <p class="text-sm text-gray-600 mb-4">Berikan rating 1–5 bintang untuk kursus ini.</p>

        <form id="review-form" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="stars" id="review-stars-input" value="5">

            <div id="review-stars" class="flex items-center justify-center space-x-2 text-yellow-400 text-2xl mb-2">
                {{-- diisi via JS --}}
            </div>

            <div class="flex justify-center gap-2">
                <x-ui.button variant="secondary" type="button" id="review-cancel">
                    Batal
                </x-ui.button>
                <x-ui.button variant="primary" type="button" id="review-submit">
                    Kirim Ulasan
                </x-ui.button>
            </div>
        </form>
    </div>
</div>

{{-- ============ TOAST (HANYA UNTUK FEEDBACK CEPAT, TIDAK KE SERVER) ============ --}}
<div id="toast"
    class="fixed right-6 bottom-6 hidden bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg z-[150] text-sm">
</div>

@push('styles')
<style>
    .star {
        cursor: pointer;
        font-size: 1.6rem;
        color: #F6C700;
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
    const courses = @json($courses ?? []);
    const courseMap = {};
    (courses || []).forEach(c => {
        if (c && c.id !== undefined) {
            courseMap[String(c.id)] = c;
        }
    });

    const els = {
        modalTest   : document.getElementById('modal-test'),
        modalGeneric: document.getElementById('modal-generic'),
        modalReview : document.getElementById('modal-review'),
        toast       : document.getElementById('toast'),

        testTitle   : document.getElementById('test-title'),
        testDesc    : document.getElementById('test-desc'),
        testForm    : document.getElementById('test-form'),
        testQuestions: document.getElementById('test-questions'),
        testProgress: document.getElementById('test-progress'),
        testSubmit  : document.getElementById('test-submit'),
        testCancel  : document.getElementById('test-cancel'),

        genTitle    : document.getElementById('modal-title'),
        genMessage  : document.getElementById('modal-message'),
        genClose    : document.getElementById('modal-close'),

        reviewForm  : document.getElementById('review-form'),
        reviewStars : document.getElementById('review-stars'),
        reviewStarsInput: document.getElementById('review-stars-input'),
        reviewSubmit: document.getElementById('review-submit'),
        reviewCancel: document.getElementById('review-cancel'),
    };

    let activeTestContext = null;    // { courseId, type, questions }
    let activeReviewCourseId = null;

    function openFlex(el) {
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    }
    function closeFlex(el) {
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    }
    function showToast(msg, timeout = 2000) {
        if (!els.toast) return;
        els.toast.textContent = msg;
        els.toast.classList.remove('hidden');
        setTimeout(() => els.toast.classList.add('hidden'), timeout);
    }
    function getCourse(id) {
        return courseMap[String(id)] || null;
    }

    // ===== GENERIC MODAL (client-side info) =====
    function openGeneric(title, message) {
        if (!els.modalGeneric) return;
        els.genTitle.textContent = title || '';
        els.genMessage.textContent = message || '';
        openFlex(els.modalGeneric);
    }
    function closeGeneric() { closeFlex(els.modalGeneric); }

    // ===== TEST MODAL (PRE/POST, SUBMIT FORM NORMAL) =====
    function openTestModal(courseId, type) {
        const c = getCourse(courseId);
        if (!c) {
            openGeneric('Error', 'Data kursus tidak ditemukan.');
            return;
        }

        const questions = (type === 'pre') ? (c.preTest || []) : (c.postTest || []);
        activeTestContext = { courseId: String(courseId), type, questions };

        const titleBase = type === 'pre' ? 'Pre-Test: ' : 'Post-Test: ';
        els.testTitle.textContent = titleBase + (c.title || 'Kursus');
        els.testDesc.textContent = type === 'pre'
            ? 'Jawab soal berikut untuk mengukur pemahaman awal Anda.'
            : 'Jawab soal berikut sebagai evaluasi akhir setelah menyelesaikan materi.';

        // Set action form ke URL yang dikirim dari luar
        const action =
            type === 'pre'
                ? (c.submit_pre_url || '')
                : (c.submit_post_url || '');

        if (!action) {
            openGeneric('Konfigurasi Tidak Lengkap', 'URL submit untuk tes belum diset di data kursus.');
            return;
        }
        els.testForm.setAttribute('action', action);
        els.testForm.setAttribute('method', 'POST');

        // Bangun list pertanyaan di <div id="test-questions">
        els.testQuestions.innerHTML = '';
        if (!questions || !questions.length) {
            els.testQuestions.innerHTML =
                `<p class="text-gray-500 text-sm">Belum ada soal yang tersedia untuk tes ini.</p>`;
            els.testProgress.textContent = '0 soal';
            els.testSubmit.style.display = 'none';
        } else {
            questions.forEach((q, idx) => {
                const qText = q.q || q.question || q.question_text || `Pertanyaan ${idx+1}`;
                const options = q.options || q.choices || [];
                const wrapper = document.createElement('div');
                wrapper.className = 'p-3 border rounded-md bg-gray-50';

                wrapper.innerHTML = `
                    <p class="font-medium text-gray-800 mb-2">${idx+1}. ${qText}</p>
                    <div class="mt-1 space-y-2">
                        ${
                            options.map((opt, oIdx) => `
                                <label class="flex items-center text-sm text-gray-700">
                                    <input type="radio"
                                           name="answers[${idx}]"
                                           value="${oIdx}"
                                           class="mr-2 text-brand focus:ring-accent">
                                    <span>${opt}</span>
                                </label>
                            `).join('')
                        }
                    </div>
                `;
                els.testQuestions.appendChild(wrapper);
            });

            els.testProgress.textContent = `${questions.length} soal`;
            els.testSubmit.style.display = 'inline-flex';
        }

        openFlex(els.modalTest);
    }

    function closeTestModal() {
        closeFlex(els.modalTest);
        activeTestContext = null;
    }

    function validateAndSubmitTest() {
        if (!activeTestContext) {
            // fallback: submit aja
            els.testForm.submit();
            return;
        }
        const { questions } = activeTestContext;
        if (!questions || !questions.length) {
            // kalau tidak ada soal, tetap submit (biar backend decide)
            els.testForm.submit();
            return;
        }

        // Cek minimal ada 1 jawaban yang dipilih (opsional)
        const inputs = els.testForm.querySelectorAll('input[type="radio"]');
        const anyChecked = Array.from(inputs).some(i => i.checked);
        if (!anyChecked) {
            openGeneric('Belum Diisi', 'Silakan pilih minimal satu jawaban sebelum submit.');
            return;
        }

        els.testForm.submit(); // FORM POST biasa => server yang handle redirect
    }

    // ===== REVIEW (FORM POST NORMAL) =====
    function openReview(courseId) {
        const c = getCourse(courseId);
        if (!c) {
            openGeneric('Error', 'Data kursus tidak ditemukan.');
            return;
        }
        const action = c.submit_review_url || '';
        if (!action) {
            openGeneric('Konfigurasi Tidak Lengkap', 'URL submit untuk review belum diset di data kursus.');
            return;
        }

        activeReviewCourseId = String(courseId);
        els.reviewForm.setAttribute('action', action);
        els.reviewForm.setAttribute('method', 'POST');
        els.reviewStarsInput.value = 5;

        if (!els.reviewStars) return;
        els.reviewStars.innerHTML = '';
        for (let i = 1; i <= 5; i++) {
            const span = document.createElement('span');
            span.className = 'star';
            span.dataset.value = i;
            span.textContent = '☆';
            span.onclick = () => highlightStars(i);
            els.reviewStars.appendChild(span);
        }
        highlightStars(5);

        openFlex(els.modalReview);
    }

    function highlightStars(n) {
        if (!els.reviewStars) return;
        [...els.reviewStars.children].forEach((s, idx) => {
            s.textContent = idx < n ? '★' : '☆';
        });
        if (els.reviewStarsInput) {
            els.reviewStarsInput.value = n;
        }
    }

    function closeReviewModal() {
        closeFlex(els.modalReview);
        activeReviewCourseId = null;
    }

    function submitReviewForm() {
        // Tidak perlu AJAX, cukup submit form
        els.reviewForm.submit();
    }

    // ===== BIND EVENTS =====
    els.testCancel && els.testCancel.addEventListener('click', closeTestModal);
    els.testSubmit && els.testSubmit.addEventListener('click', validateAndSubmitTest);

    els.genClose && els.genClose.addEventListener('click', closeGeneric);

    els.reviewCancel && els.reviewCancel.addEventListener('click', closeReviewModal);
    els.reviewSubmit && els.reviewSubmit.addEventListener('click', submitReviewForm);

    // Klik backdrop untuk tutup
    [els.modalTest, els.modalGeneric, els.modalReview].forEach(el => {
        if (!el) return;
        el.addEventListener('click', e => {
            if (e.target === e.currentTarget) {
                if (el === els.modalTest)    closeTestModal();
                if (el === els.modalGeneric) closeGeneric();
                if (el === els.modalReview)  closeReviewModal();
            }
        });
    });

    // ESC untuk tutup semua modal
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeTestModal();
            closeGeneric();
            closeReviewModal();
        }
    });

    // Expose ke global: bisa dipakai dari Blade mana saja
    window.TestFlow = {
        startPreTest(courseId)  { openTestModal(courseId, 'pre'); },
        startPostTest(courseId) { openTestModal(courseId, 'post'); },
        openReview,
        openGeneric,
    };
})();
</script>
@endpush