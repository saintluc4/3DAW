/* ============================================================
   modal.js — modal de confirmação estilizado
   Substitui: onclick="return confirm('...')" nativo do browser
============================================================ */
const Modal = {

  confirm(title, body, onConfirm) {
    const mc = document.getElementById('modal-container');

    mc.innerHTML = `
    <div class="modal-backdrop" id="modal-backdrop">
      <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-title" id="modal-title">${Utils.esc(title)}</div>
        <p>${Utils.esc(body)}</p>
        <div class="action-row" style="justify-content:flex-end">
          <button class="btn btn-secondary" id="modal-cancel">Cancelar</button>
          <button class="btn btn-danger"    id="modal-ok">Confirmar Exclusão</button>
        </div>
      </div>
    </div>`;

    const close = () => { mc.innerHTML = ''; };

    mc.querySelector('#modal-cancel').onclick    = close;
    mc.querySelector('#modal-ok').onclick        = () => { close(); onConfirm(); };
    mc.querySelector('#modal-backdrop').onclick  = e => { if (e.target.id === 'modal-backdrop') close(); };

    /* Fecha com Escape */
    const onKey = e => { if (e.key === 'Escape') { close(); document.removeEventListener('keydown', onKey); } };
    document.addEventListener('keydown', onKey);
  }
};

/* Atalho global chamado pelos botões Excluir em qualquer view */
function pedirExclusao(id) {
  const p = DB.buscarPergunta(id);
  if (!p) return;

  Modal.confirm(
    'Excluir Pergunta',
    `Tem certeza que deseja excluir permanentemente esta pergunta? Esta ação não pode ser desfeita.\n\n"${Utils.trunc(p.enunciado, 120)}"`,
    () => {
      DB.excluirPergunta(id);
      navigate('listar', { msgs: [{ txt: 'Pergunta excluída com sucesso.', tipo: 'success' }] });
    }
  );
}
