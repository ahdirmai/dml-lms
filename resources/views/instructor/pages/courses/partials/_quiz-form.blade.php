@php
/** @var \App\Models\Lms\Course $course */
/** @var string $kind */ // 'pretest' | 'posttest'
/** @var \App\Models\Lms\Quiz|null $existing */
/** @var string $store_route */
$K = \Illuminate\Support\Str::slug($kind, '_'); // pretest | posttest
$modalId = "question-modal-{$K}";
$formId = "question-form-{$K}";
$titleId = "qm-title-{$K}";
$methodId = "qm-method-{$K}";
$promptId = "qm-prompt-{$K}";
$pointsId = "qm-points-{$K}";
$orderId = "qm-order-visible-{$K}";
$correctId = "qm-correct-{$K}";
$correctId = "qm-correct-{$K}";
$optionsId = "qm-options-{$K}";
$prefix = $routePrefix ?? 'instructor'; // default to instructor if not set
@endphp

<div class="space-y-6">
    {{-- @if (session('success'))
    <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
    @endif

    @if (session('error'))
    <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
        <p class="font-semibold">{{ session('error') }}</p>
        @if (session('import_errors'))
        <ul class="list-disc list-inside mt-2">
            @foreach (session('import_errors') as $line)
            <li>{{ $line }}</li>
            @endforeach
        </ul>
        @endif
    </div>
    @endif --}}
    {{-- ===== Header Quiz ===== --}}
    <form action="{{ $store_route }}" method="POST" class="p-4 border rounded-xl bg-white">
        @csrf
        <input type="hidden" name="redirect_tab" value="{{ $kind }}">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Judul {{ ucfirst($kind) }}</label>
                <input type="text" id="quiz_title_{{ $kind }}" name="title"
                    value="{{ old('title', $existing?->title ?? ucfirst($kind)) }}" class="w-full p-3 border rounded-xl"
                    required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Batas Waktu (detik)</label>
                <input type="number" name="time_limit_seconds" min="10" max="86400"
                    value="{{ old('time_limit_seconds', $existing?->time_limit_seconds ?? 600) }}"
                    class="w-full p-3 border rounded-xl">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Passing Score (%)</label>
                <input type="number" name="passing_score" min="0" max="100"
                    value="{{ old('passing_score', $existing?->passing_score ?? 70) }}"
                    class="w-full p-3 border rounded-xl">
            </div>
            {{-- <div class="flex items-end justify-between">
                <div>
                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="shuffle_questions" value="1" @checked(old('shuffle_questions',
                            (int)($existing?->shuffle_questions ?? 0))==1)>
                        <span>Acak Pertanyaan</span>
                    </label>
                    <label class="inline-flex items-center gap-2 ml-4">
                        <input type="checkbox" name="shuffle_options" value="1" @checked(old('shuffle_options',
                            (int)($existing?->shuffle_options ?? 0))==1)>
                        <span>Acak Opsi</span>
                    </label>
                </div>
            </div> --}}
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="bg-primary-accent text-white px-5 py-2 rounded-xl"
                onclick="return handleQuizSave(event, '{{ $kind }}')">
                Simpan Pengaturan Quiz
            </button>
        </div>
    </form>

    {{-- ===== Jika belum ada quiz, stop di sini ===== --}}
    @if(!$existing)
    <div class="p-4 border rounded-xl bg-yellow-50 text-yellow-800">
        Simpan pengaturan {{ ucfirst($kind) }} terlebih dahulu untuk membuat pertanyaan.
    </div>
</div>
@php
return; @endphp
@endif

{{-- ===== Daftar Pertanyaan ===== --}}
<div id="questions-{{ $K }}" class="p-4 border rounded-xl bg-white">
    {{-- Baris 1: Judul + Tambah --}}
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-lg font-semibold">Daftar Pertanyaan ({{ ucfirst($kind) }})</h4>
        <button type="button" class="bg-secondary-highlight text-white px-4 py-2 rounded-xl"
            onclick="BUILDER_QUIZ['{{ $K }}'].open('{{ $existing->id }}')">
            + Tambah Pertanyaan
        </button>
    </div>

    {{-- Baris 2: Toolbar Import --}}
    <div class="flex flex-wrap items-center gap-2 mb-4">
        @if(
        $kind === \App\Models\Lms\Quiz::KIND_PRETEST)
        <button id="btnImportPretest-{{ $K }}" type="button"
            class="px-3 py-2 rounded bg-amber-500 text-white hover:bg-amber-600">
            Import Excel (Pretest)
        </button>
        @else

        <button id="btnImportPosttest-{{ $K }}" type="button"
            class="px-3 py-2 rounded bg-purple-600 text-white hover:bg-purple-700">
            Import Excel (Posttest)
        </button>

        @endif
        <a href="{{ route($prefix . '.courses.quizzes.template') }}"
            class="px-3 py-2 rounded bg-green-600 text-white hover:bg-green-700">
            Download Template (Excel)
        </a>

        {{-- Hanya tampil di POSTTEST: Copy dari Pretest --}}
        @if(
        $kind === \App\Models\Lms\Quiz::KIND_POSTTEST &&
        method_exists($course, 'pretest') && $course->pretest &&
        $course->pretest->questions()->exists()
        )
        <form method="POST" action="{{ route($prefix . '.courses.posttest.copyFromPretest', $course->id) }}"
            onsubmit="return confirm('Salin semua pertanyaan dari Pretest ke Posttest?');" class="ml-auto">
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            <input type="hidden" name="redirect_tab" value="posttest">
            <button type="submit" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-xl">
                Copy dari Pretest
            </button>
        </form>
        @endif
    </div>

    {{-- ====== IMPORT MODAL (namespaced per kind) ====== --}}
    <div id="importModal-{{ $K }}" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b p-4">
                <h3 class="text-lg font-semibold">
                    Import Pertanyaan <span id="importKindBadge-{{ $K }}" class="uppercase"></span>
                </h3>
                <button type="button" class="p-2 rounded hover:bg-gray-100" data-close-modal>&times;</button>
            </div>

            <form id="formImport-{{ $K }}" class="p-4 space-y-4" method="POST" action="" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="importKind-{{ $K }}" name="kind" value="">
                <div>
                    <label class="block font-medium mb-1">File Excel (.xlsx/.xls/.csv)</label>
                    <input type="file" name="file" id="importFile-{{ $K }}" accept=".xlsx,.xls,.csv" required
                        class="w-full border rounded px-3 py-2">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="replace_existing-{{ $K }}" name="replace_existing" value="1">
                    <label for="replace_existing-{{ $K }}">Hapus & ganti semua pertanyaan lama</label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
                        Import
                    </button>
                    <button type="button" data-close-modal class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300">
                        Batal
                    </button>
                </div>

                <p class="text-sm text-gray-600">
                    Format: <code>question, options, correct, points, order</code><br>
                    <b>options</b>: pakai <code>;</code><br>
                    <b>correct</b>: pakai <code>;</code> jika lebih dari satu (multiple_choice).
                </p>
            </form>
        </div>
    </div>

    @forelse($existing->questions()->with('options')->orderBy('order')->get() as $q)
    <div class="p-3 border rounded-lg mb-3">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-sm text-gray-500">#{{ $q->order ?? 0 }} • {{ $q->points }} poin</div>
                <div class="font-semibold">{{ $q->question_text }}</div>
            </div>
            <div class="flex gap-2">
                <button type="button" class="text-blue-600 text-sm hover:underline"
                    onclick='BUILDER_QUIZ["{{ $K }}"].open("{{ $existing->id }}", @json($q))'>
                    Edit
                </button>
                <form action="{{ route($prefix . '.quizzes.questions.destroy', [$existing->id, $q->id]) }}" method="POST"
                    onsubmit="return confirm('Hapus pertanyaan ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-red-600 text-sm hover:underline">Hapus</button>
                </form>
            </div>
        </div>

        <ul class="mt-2 ml-5 list-disc">
            @foreach($q->options as $idx => $opt)
            <li class="{{ $opt->is_correct ? 'text-green-700 font-semibold' : '' }}">
                {{ chr(65 + $idx) }}. {{ $opt->option_text }}
                @if($opt->is_correct)
                <span class="ml-2 text-xs px-2 py-0.5 rounded bg-green-prd">benar</span>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @empty
    <div class="p-3 rounded bg-soft text-gray-600">Belum ada pertanyaan.</div>
    @endforelse
</div>
</div>
{{-- ===== MODAL (namespaced per kind) ===== --}}
<div id="{{ $modalId }}" class="fixed inset-0 bg-black/40 hidden z-50 items-center justify-center">
    <div class="bg-white w-full max-w-2xl rounded-2xl p-6">
        <h4 id="{{ $titleId }}" class="text-lg font-bold mb-4">Tambah Pertanyaan</h4>

        <form id="{{ $formId }}" action="{{ route($prefix . '.quizzes.questions.store', $existing?->id ?? 0) }}"
            method="POST">
            @csrf
            <input type="hidden" name="_method" id="{{ $methodId }}" value="POST">
            <input type="hidden" name="correct_index" id="{{ $correctId }}" value="0">
            <input type="hidden" name="redirect_tab" value="{{ $kind }}">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-2">Pertanyaan</label>
                    <textarea name="prompt" id="{{ $promptId }}" rows="3" class="w-full p-3 border rounded-xl"
                        required></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold mb-2">Poin</label>
                        <input type="number" name="points" id="{{ $pointsId }}" min="0" value="1"
                            class="w-full p-3 border rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-2">Urutan</label>
                        <input type="number" name="order" id="{{ $orderId }}" min="0" value=""
                            class="w-full p-3 border rounded-xl" placeholder="(opsional)">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2">Opsi Jawaban</label>
                    <div id="{{ $optionsId }}" class="space-y-2"></div>
                    <button type="button" class="mt-2 text-sm text-blue-600"
                        onclick="BUILDER_QUIZ['{{ $K }}'].addOption()">+ tambah opsi</button>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" class="px-4 py-2 rounded-xl bg-gray-200"
                    onclick="BUILDER_QUIZ['{{ $K }}'].close()">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl bg-primary-accent text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>


@push('scripts')
<script>
    window.BUILDER_QUIZ = window.BUILDER_QUIZ || {};
(function(){
  const K         = "{{ $K }}";
  const modal     = document.getElementById("{{ $modalId }}");
  const form      = document.getElementById("{{ $formId }}");
  const titleEl   = document.getElementById("{{ $titleId }}");
  const methodEl  = document.getElementById("{{ $methodId }}");
  const promptEl  = document.getElementById("{{ $promptId }}");
  const pointsEl  = document.getElementById("{{ $pointsId }}");
  const orderEl   = document.getElementById("{{ $orderId }}");
  const correctEl = document.getElementById("{{ $correctId }}");
  const optionsEl = document.getElementById("{{ $optionsId }}");

  const storeUrl = "{{ route($prefix . '.quizzes.questions.store', $existing?->id ?? 0) }}"; // quiz ini (pre/post)

  function setAction(url, isUpdate){
    form.action = url;
    methodEl.value = isUpdate ? 'PUT' : 'POST';
  }

  function clearOptions(){ optionsEl.innerHTML = ''; }

  function valOr(v, f=''){ return (v === undefined || v === null) ? f : v; }

  function optionRow(index, data = {}){
    const id        = valOr(data.id, '');
    const text      = valOr(data.text, data.option_text || '');
    const isCorrect = !!data.is_correct;

    const row = document.createElement('div');
    row.className = 'flex items-center gap-2';
    row.innerHTML = `
      <input type="hidden" name="options[${index}][id]" value="${id}">
      <input type="text"   name="options[${index}][text]" value="${text}" class="flex-1 p-2 border rounded" placeholder="Teks opsi..." required>
      <label class="inline-flex items-center gap-1 text-sm">
        <input type="radio" name="__correct_choice_{{ $K }}" ${isCorrect ? 'checked' : ''} onchange="BUILDER_QUIZ['{{ $K }}'].setCorrect(${index})">
        benar
      </label>
      <button type="button" class="text-red-600 text-sm" onclick="BUILDER_QUIZ['{{ $K }}'].removeOption(this)">hapus</button>
    `;
    optionsEl.appendChild(row);
  }

  function renumberOptions(){
    const rows = Array.from(optionsEl.children);
    rows.forEach((row, idx) => {
      row.querySelectorAll('input[name^="options["]').forEach(inp => {
        const name = inp.getAttribute('name');
        const updated = name.replace(/options\[\d+\]/, `options[${idx}]`);
        inp.setAttribute('name', updated);
      });
      const radio = row.querySelector('input[type="radio"][name^="__correct_choice_"]');
      if (radio) radio.setAttribute('onchange', `BUILDER_QUIZ['{{ $K }}'].setCorrect(${idx})`);
    });
  }

  function addOption(){ optionRow(optionsEl.children.length, {}); }

  function removeOption(btn){
    const row = btn.closest('div');
    row.remove();
    renumberOptions();

    // pastikan selalu ada yg benar terpilih
    const checked = optionsEl.querySelector('input[type="radio"][name="__correct_choice_{{ $K }}"]:checked');
    if (!checked) {
      const first = optionsEl.querySelector('input[type="radio"][name="__correct_choice_{{ $K }}"]');
      if (first) { first.checked = true; correctEl.value = 0; }
    }
  }

  function setCorrect(idx){ correctEl.value = String(idx); }

  function getPrompt(q){ return valOr(q?.prompt, q?.question_text || ''); }
  function getPoints(q){ return valOr(q?.points, 1); }
  function getOrder(q){  return valOr(q?.order, ''); }

  function open(quizId, question = null){
    // reset
    form.reset();
    clearOptions();
    correctEl.value = 0;

    if (question) {
      // UPDATE
      titleEl.textContent = 'Edit Pertanyaan';
      setAction(`{{ url($prefix . '/quizzes') }}/${quizId}/questions/${question.id}`, true);

      promptEl.value = getPrompt(question);
      pointsEl.value = getPoints(question);
      orderEl.value  = getOrder(question);

      const opts = Array.isArray(question.options) ? question.options : [];
      if (opts.length) {
        opts.forEach((opt, idx) => optionRow(idx, opt));
        const corrIdx = opts.findIndex(o => !!o.is_correct);
        correctEl.value = corrIdx >= 0 ? corrIdx : 0;
        const radios = optionsEl.querySelectorAll('input[type="radio"][name="__correct_choice_{{ $K }}"]');
        if (radios[correctEl.value]) radios[correctEl.value].checked = true;
      } else {
        addOption(); addOption();
        const first = optionsEl.querySelector('input[type="radio"][name="__correct_choice_{{ $K }}"]');
        if (first) first.checked = true;
        correctEl.value = 0;
      }
    } else {
      // CREATE
      titleEl.textContent = 'Tambah Pertanyaan';
      setAction(storeUrl, false);

      addOption(); addOption();
      const first = optionsEl.querySelector('input[type="radio"][name="__correct_choice_{{ $K }}"]');
      if (first) first.checked = true;
      correctEl.value = 0;

      orderEl.value = '';
    }

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    promptEl.focus();
  }

  function close(){
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  }

  // expose per kind
  window.BUILDER_QUIZ[K] = { open, close, addOption, removeOption, setCorrect };
})();
</script>

<script>
    (() => {
  const K          = "{{ $K }}";
  const COURSE_ID  = "{{ $course->id ?? ($courseId ?? '') }}";

  const routeBase  = (kind) => `{{ url($prefix . '/courses') }}/${COURSE_ID}/quizzes/${kind}/import`;

  const modal      = document.getElementById(`importModal-${K}`);
  const form       = document.getElementById(`formImport-${K}`);
  const fileInput  = document.getElementById(`importFile-${K}`);
  const kindInput  = document.getElementById(`importKind-${K}`);
  const kindBadge  = document.getElementById(`importKindBadge-${K}`);
  const btnPre     = document.getElementById(`btnImportPretest-${K}`);
  const btnPost    = document.getElementById(`btnImportPosttest-${K}`);

  const openModal = (kind) => {
    if (!COURSE_ID) { alert('COURSE_ID kosong di view.'); return; }
    kindInput.value = kind;
    kindBadge.textContent = `(${kind})`;
    fileInput.value = '';

    // set action form ke route yang benar
    form.setAttribute('action', routeBase(kind));

    // tampilkan modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
  };

  const closeModal = () => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
  };

  // tombol buka modal
  btnPre?.addEventListener('click',  () => openModal('pretest'));
  btnPost?.addEventListener('click', () => openModal('posttest'));

  // tombol close
  modal.querySelectorAll('[data-close-modal]').forEach(el => el.addEventListener('click', closeModal));
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

  // submit form = default browser submit (non-AJAX). Tidak ada JS tambahan di sini.
})();
</script>
@endpush
