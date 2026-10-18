document.querySelectorAll('[data-current-year]').forEach((node) => {
  node.textContent = new Date().getFullYear();
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
  button.addEventListener('click', () => {
    const input = document.getElementById(button.getAttribute('data-password-toggle'));
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    button.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
    button.setAttribute('aria-pressed', String(isHidden));
  });
});

const requestedState = new URL(window.location.href).searchParams.get('state') || 'normal';

document.querySelectorAll('[data-state-visible]').forEach((element) => {
  element.hidden = element.dataset.stateVisible !== requestedState;
});

document.querySelectorAll('[data-state-hidden]').forEach((element) => {
  element.hidden = element.dataset.stateHidden === requestedState;
});

document.querySelectorAll('[data-invalid-state]').forEach((field) => {
  const isInvalid = field.dataset.invalidState === requestedState;
  field.classList.toggle('is-invalid', isInvalid);
  if (field.dataset.errorDescribedby) {
    field.setAttribute('aria-describedby', isInvalid ? field.dataset.errorDescribedby : field.dataset.errorDescribedby.split(' ')[0]);
  }
});

document.querySelectorAll('[data-demo-state-select]').forEach((select) => {
  const applyState = () => {
    const state = select.value;
    document.querySelectorAll('[data-state-panel]').forEach((panel) => {
      panel.hidden = panel.dataset.statePanel !== state;
    });
    const url = new URL(window.location.href);
    if (state === 'normal') url.searchParams.delete('state');
    else url.searchParams.set('state', state);
    history.replaceState({}, '', url);
  };
  const requested = requestedState;
  if (requested && [...select.options].some((option) => option.value === requested)) select.value = requested;
  select.addEventListener('change', applyState);
  applyState();
});
