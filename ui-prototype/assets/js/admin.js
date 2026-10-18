const adminPages = [
  ['utama', 'Utama', [
    ['dashboard', 'DB', 'Dashboard', 'index.html'],
  ]],
  ['konten', 'Website', [
    ['school', 'PS', 'Profil Sekolah', 'profil-sekolah.html'],
    ['organization', 'SO', 'Struktur Organisasi', 'organisasi.html'],
    ['posts', 'BK', 'Berita & Pengumuman', 'konten.html'],
  ]],
  ['people', 'Data Orang', [
    ['teachers', 'GR', 'Guru', 'guru.html'],
    ['students', 'SW', 'Siswa & Akun', 'siswa.html'],
  ]],
  ['academic', 'Akademik', [
    ['periods', 'PR', 'Periode Akademik', 'periode.html'],
    ['classes', 'KL', 'Kelas & Penempatan', 'kelas.html'],
    ['subjects', 'MP', 'Mata Pelajaran', 'mapel.html'],
    ['assignments', 'PG', 'Penugasan Mengajar', 'penugasan.html'],
    ['schedules', 'JD', 'Jadwal', 'jadwal.html'],
  ]],
  ['reference', 'Referensi', [
    ['states', 'ST', 'State UI', 'states.html'],
  ]],
];

function adminNav(activePage) {
  return adminPages.map(([, label, items]) => `
    <div class="admin-nav-label">${label}</div>
    ${items.map(([key, mark, title, href]) => `<a class="admin-nav-link ${key === activePage ? 'active' : ''}" ${key === activePage ? 'aria-current="page"' : ''} href="${href}"><span class="admin-nav-mark" aria-hidden="true">${mark}</span><span>${title}</span></a>`).join('')}
  `).join('');
}

function adminIdentity() {
  return `<div class="d-flex align-items-center gap-2"><span class="admin-avatar" aria-hidden="true">NA</span><span class="flex-grow-1"><strong class="d-block text-white small">Nadia Admin</strong><span class="d-block small opacity-50">Administrator</span></span></div>`;
}

function mountAdminShell() {
  const activePage = document.body.dataset.adminPage || 'dashboard';
  const shell = document.getElementById('adminShell');
  if (!shell) return;
  shell.innerHTML = `
    <aside class="admin-sidebar" aria-label="Navigasi admin desktop">
      <div class="admin-sidebar__brand"><a class="d-flex align-items-center gap-2 text-decoration-none" href="index.html"><span class="school-mark" aria-hidden="true">SC</span><span><span class="brand-wordmark d-block text-white">SIM Akademik</span><span class="brand-subtitle d-block text-white-50">Panel Admin</span></span></a></div>
      <nav class="admin-sidebar__scroll">${adminNav(activePage)}</nav>
      <div class="admin-sidebar__footer">${adminIdentity()}<form class="mt-3" action="../auth/login.html" method="get"><button class="btn btn-sm btn-outline-light w-100" type="submit">Keluar</button></form></div>
    </aside>
    <header class="admin-mobile-header"><div class="container-fluid d-flex align-items-center justify-content-between gap-3 py-2"><a class="d-flex align-items-center gap-2 text-decoration-none" href="index.html"><span class="school-mark" aria-hidden="true">SC</span><span class="text-white font-serif">Panel Admin</span></a><button class="btn btn-sm btn-outline-light" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMobileNav" aria-controls="adminMobileNav">Menu</button></div></header>
    <div class="offcanvas offcanvas-start admin-offcanvas bg-forest text-white" tabindex="-1" id="adminMobileNav" aria-labelledby="adminMobileNavLabel"><div class="offcanvas-header border-bottom footer-rule"><h2 class="offcanvas-title h4 text-white" id="adminMobileNavLabel">SIM Akademik</h2><button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button></div><div class="offcanvas-body d-flex flex-column"><nav class="flex-grow-1">${adminNav(activePage)}</nav><div class="pt-3 border-top footer-rule">${adminIdentity()}<form class="mt-3" action="../auth/login.html" method="get"><button class="btn btn-sm btn-outline-light w-100" type="submit">Keluar</button></form></div></div></div>
    <div class="toast-container position-fixed top-0 end-0 p-3"><div id="prototypeToast" class="toast admin-toast" role="status" aria-live="polite" aria-atomic="true"><div class="toast-header"><strong class="me-auto">Prototype SIMAK</strong><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button></div><div class="toast-body" id="prototypeToastBody">Aksi prototype berhasil disimulasikan.</div></div></div>
  `;
}

function showPrototypeToast(message, type = 'success') {
  const toastElement = document.getElementById('prototypeToast');
  const toastBody = document.getElementById('prototypeToastBody');
  if (!toastElement || !toastBody || !window.bootstrap) return;
  toastBody.textContent = message;
  toastElement.classList.toggle('border-danger', type === 'error');
  bootstrap.Toast.getOrCreateInstance(toastElement, { delay: 3600 }).show();
}

function prepareResponsiveTables() {
  document.querySelectorAll('.table-admin').forEach((table) => {
    const labels = [...table.querySelectorAll('thead th')].map((th) => th.textContent.trim());
    table.querySelectorAll('tbody tr').forEach((row) => {
      [...row.children].forEach((cell, index) => { cell.dataset.label = labels[index] || ''; });
    });
  });
}

function bindPrototypeForms() {
  document.querySelectorAll('form[data-prototype-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      const password = form.querySelector('[name="password"]');
      const confirmation = form.querySelector('[name="password_confirmation"]');
      if (password && confirmation) {
        confirmation.setCustomValidity(password.value === confirmation.value ? '' : 'Konfirmasi kata sandi tidak sama.');
      }
      form.classList.add('was-validated');
      if (!form.checkValidity()) {
        showPrototypeToast('Periksa kembali field yang wajib dan pesan validasi.', 'error');
        return;
      }
      showPrototypeToast(form.dataset.successMessage || 'Data tersimpan pada simulasi UI. Belum ada backend yang dijalankan.');
      const modalNode = form.closest('.modal');
      if (modalNode) bootstrap.Modal.getOrCreateInstance(modalNode).hide();
    });
  });

  document.querySelectorAll('form[data-filter-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      showPrototypeToast('Filter visual diterapkan. Data tetap berupa contoh statis.');
    });
  });
}

function bindFilePreviews() {
  document.querySelectorAll('[data-preview-input]').forEach((input) => {
    input.addEventListener('change', () => {
      const target = document.getElementById(input.dataset.previewInput);
      const file = input.files?.[0];
      if (!target || !file) return;
      if (!file.type.startsWith('image/')) {
        input.setCustomValidity('File harus berupa gambar JPEG, PNG, atau WebP.');
        showPrototypeToast('Upload gagal: file bukan gambar yang didukung.', 'error');
        return;
      }
      input.setCustomValidity('');
      const reader = new FileReader();
      reader.addEventListener('load', () => {
        target.style.backgroundImage = `url(${reader.result})`;
        target.classList.add('has-preview');
        const label = target.querySelector('[data-preview-label]');
        if (label) label.textContent = file.name;
      });
      reader.readAsDataURL(file);
    });
  });
}

function bindPasswordToggles() {
  document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = document.getElementById(button.dataset.passwordToggle);
      if (!input) return;
      const hidden = input.type === 'password';
      input.type = hidden ? 'text' : 'password';
      button.textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
    });
  });
}

function bindEditor() {
  const editor = document.querySelector('[data-editor]');
  const hidden = document.querySelector('[data-editor-output]');
  if (!editor) return;
  document.querySelectorAll('[data-editor-command]').forEach((button) => {
    button.addEventListener('click', () => {
      document.execCommand(button.dataset.editorCommand, false, button.dataset.editorValue || null);
      editor.focus();
    });
  });
  editor.addEventListener('input', () => { if (hidden) hidden.value = editor.innerHTML; });

  const counter = document.querySelector('[data-image-counter]');
  const list = document.querySelector('[data-inline-image-list]');
  const addButton = document.querySelector('[data-add-inline-image]');
  if (!counter || !list || !addButton) return;
  let count = Number(counter.dataset.imageCounter || 2);
  const paint = () => { counter.textContent = `${count}/10 gambar inline`; };
  paint();
  addButton.addEventListener('click', () => {
    if (count >= 10) {
      showPrototypeToast('Batas 10 gambar inline sudah tercapai.', 'error');
      return;
    }
    count += 1;
    const item = document.createElement('div');
    item.className = 'inline-image-item';
    item.innerHTML = `<div class="d-flex justify-content-between gap-2"><strong>Gambar inline ${count}</strong><span class="small text-muted-warm">baru</span></div><div class="row g-2 mt-1"><div class="col-md-5"><label class="form-label" for="inline_file_${count}">File gambar ${count} <span class="required-mark">*</span></label><input class="form-control" id="inline_file_${count}" name="inline_images[${count}][file]" type="file" accept="image/jpeg,image/png,image/webp" required></div><div class="col-md-4"><label class="form-label" for="inline_alt_${count}">Alt text <span class="required-mark">*</span></label><input class="form-control" id="inline_alt_${count}" name="inline_images[${count}][alt_text]" required></div><div class="col-md-3"><label class="form-label" for="inline_caption_${count}">Caption</label><input class="form-control" id="inline_caption_${count}" name="inline_images[${count}][caption]"></div></div>`;
    list.appendChild(item);
    paint();
    showPrototypeToast(`Slot gambar inline ${count} ditambahkan.`);
  });
}

function bindDemoStates() {
  document.querySelectorAll('[data-demo-state-select]').forEach((select) => {
    const apply = () => {
      const state = select.value;
      document.querySelectorAll('[data-state-panel]').forEach((panel) => { panel.hidden = panel.dataset.statePanel !== state; });
      const url = new URL(window.location.href);
      if (state === 'normal') url.searchParams.delete('state'); else url.searchParams.set('state', state);
      history.replaceState({}, '', url);
    };
    const requested = new URL(window.location.href).searchParams.get('state');
    if (requested && [...select.options].some((option) => option.value === requested)) select.value = requested;
    select.addEventListener('change', apply);
    apply();
  });
}

function bindSimulatedActions() {
  document.querySelectorAll('[data-simulate-success], [data-simulate]').forEach((button) => {
    button.addEventListener('click', () => showPrototypeToast(button.dataset.simulateSuccess || button.dataset.simulate || 'Aksi berhasil disimulasikan.'));
  });
}

mountAdminShell();
prepareResponsiveTables();
bindPrototypeForms();
bindFilePreviews();
bindPasswordToggles();
bindEditor();
bindDemoStates();
bindSimulatedActions();
