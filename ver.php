<?php
// ── Contraseña ────────────────────────────────────────────
define('ACCESS_PASS', 'SatoshiNakamoto09');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pass'])) {
    if ($_POST['pass'] === ACCESS_PASS) {
        $_SESSION['auth'] = true;
    } else {
        $error = 'Contraseña incorrecta.';
    }
}

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ver.php');
    exit;
}

if (empty($_SESSION['auth'])) {
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso — Visor</title>
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:#0d1117;display:flex;align-items:center;justify-content:center;min-height:100vh;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
    .card{background:#161b22;border:1px solid #30363d;border-radius:12px;padding:40px 36px;width:100%;max-width:360px;display:flex;flex-direction:column;gap:20px}
    h2{color:#c9d1d9;font-size:1.15rem;text-align:center}
    label{color:#8b949e;font-size:.85rem;display:block;margin-bottom:6px}
    input[type=password]{width:100%;background:#0d1117;border:1px solid #30363d;border-radius:6px;color:#c9d1d9;padding:10px 14px;font-size:.95rem;outline:none;transition:border-color .2s}
    input[type=password]:focus{border-color:#58a6ff}
    button{width:100%;background:#58a6ff;color:#0d1117;font-weight:700;border:none;border-radius:6px;padding:11px;font-size:.95rem;cursor:pointer;transition:opacity .15s}
    button:hover{opacity:.85}
    .err{color:#f85149;font-size:.82rem;text-align:center;background:#2d0f0f;border:1px solid #6e1a1a;border-radius:6px;padding:8px}
    .lock{font-size:2rem;text-align:center}
  </style>
</head>
<body>
  <form class="card" method="POST">
    <div class="lock">🔒</div>
    <h2>Visor de datos — Acceso</h2>
    <?php if (!empty($error)): ?>
      <div class="err"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>
    <div>
      <label for="pass">Contraseña</label>
      <input type="password" id="pass" name="pass" autofocus placeholder="••••••••" required>
    </div>
    <button type="submit">Entrar</button>
  </form>
</body>
</html>
<?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visor de datos</title>
  <style>
    :root {
      --bg: #0d1117;
      --surface: #161b22;
      --border: #30363d;
      --text: #c9d1d9;
      --accent: #58a6ff;
      --green: #3fb950;
      --red: #f85149;
      --muted: #8b949e;
      --mono: 'Courier New', monospace;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: var(--bg); color: var(--text);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      min-height: 100vh; display: flex; flex-direction: column;
    }
    header {
      background: var(--surface); border-bottom: 1px solid var(--border);
      padding: 14px 20px; display: flex; align-items: center;
      justify-content: space-between; gap: 10px; flex-wrap: wrap;
    }
    .title-wrap { display: flex; align-items: center; gap: 10px; }
    .dot {
      width: 10px; height: 10px; border-radius: 50%;
      background: var(--green); box-shadow: 0 0 8px var(--green);
      animation: pulse 1.5s ease-in-out infinite;
    }
    .dot.off { background: var(--muted); box-shadow: none; animation: none; }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
    h1 { font-size: 1.05rem; font-weight: 600; }
    .controls { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .badge {
      font-size: .75rem; color: var(--muted);
      background: var(--bg); border: 1px solid var(--border);
      border-radius: 20px; padding: 3px 10px;
    }
    button {
      cursor: pointer; border: none; border-radius: 6px;
      padding: 7px 14px; font-size: .82rem; font-weight: 600;
      transition: opacity .15s, transform .1s;
    }
    button:active { transform: scale(.97); }
    button:disabled { opacity: .45; cursor: not-allowed; }
    .btn-save  { background: var(--accent); color: #0d1117; }
    .btn-clear { background: var(--red);    color: #fff; }
    .btn-pause { background: var(--border); color: var(--text); }
    .btn-sound { background: var(--border); color: var(--text); }
    .btn-logout{ background: transparent; color: var(--muted); border: 1px solid var(--border); }

    main { flex: 1; padding: 16px 20px; display: flex; flex-direction: column; gap: 10px; }
    .stats { display: flex; gap: 12px; flex-wrap: wrap; font-size: .78rem; color: var(--muted); }
    .stats span {
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 6px; padding: 4px 10px;
    }
    .stats strong { color: var(--text); }

    #output {
      flex: 1; min-height: 62vh;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 8px; padding: 14px;
      font-family: var(--mono); font-size: .82rem; line-height: 1.65;
      white-space: pre-wrap; word-break: break-word;
      overflow-y: auto; color: var(--text);
    }
    .empty-state {
      display: flex; flex-direction: column; align-items: center;
      justify-content: center; height: 100%; gap: 8px;
      color: var(--muted); font-family: inherit; font-size: .9rem;
    }
    .empty-state span { font-size: 2rem; }

    /* Cada registro */
    .entry {
      border-bottom: 1px solid var(--border);
      padding: 8px 4px;
      line-height: 1.55;
      white-space: pre-wrap;
      word-break: break-word;
    }
    .entry:last-child { border-bottom: none; }

    /* Nuevo registro resaltado */
    .new-entry {
      animation: highlight 2.5s ease-out forwards;
    }
    @keyframes highlight {
      0%   { background: rgba(88,166,255,.15); }
      100% { background: transparent; }
    }

    #toast {
      position: fixed; bottom: 24px; right: 24px;
      background: var(--surface); border: 1px solid var(--border);
      border-radius: 8px; padding: 10px 18px; font-size: .85rem;
      opacity: 0; transition: opacity .3s; pointer-events: none;
      color: var(--text); z-index: 999;
    }
    #toast.show { opacity: 1; }
    footer {
      text-align: center; padding: 10px;
      font-size: .73rem; color: var(--muted);
      border-top: 1px solid var(--border);
    }
  </style>
</head>
<body>

<header>
  <div class="title-wrap">
    <div class="dot" id="liveDot"></div>
    <h1>📄 datos.txt — Visor en vivo</h1>
  </div>
  <div class="controls">
    <span class="badge" id="lastUpdate">Esperando...</span>
    <button class="btn-sound" id="btnSound" title="Activar / silenciar sonido">🔔 Sonido</button>
    <button class="btn-pause" id="btnPause">⏸ Pausar</button>
    <button class="btn-save"  id="btnSave" >💾 Guardar</button>
    <button class="btn-clear" id="btnClear">🗑 Limpiar</button>
    <form method="POST" style="margin:0">
      <button type="submit" name="logout" class="btn-logout">Salir</button>
    </form>
  </div>
</header>

<main>
  <div class="stats">
    <span>📦 Tamaño: <strong id="statSize">—</strong></span>
    <span>📝 Registros: <strong id="statCount">—</strong></span>
    <span>🔄 Intervalo: <strong>1 s</strong></span>
  </div>
  <div id="output">
    <div class="empty-state"><span>📭</span>Sin datos todavía.</div>
  </div>
</main>

<div id="toast"></div>
<footer>Auto-actualización cada segundo · datos.txt</footer>

<script>
  const output     = document.getElementById('output');
  const lastUpdate = document.getElementById('lastUpdate');
  const liveDot    = document.getElementById('liveDot');
  const statSize   = document.getElementById('statSize');
  const statCount  = document.getElementById('statCount');
  const btnPause   = document.getElementById('btnPause');
  const btnSave    = document.getElementById('btnSave');
  const btnClear   = document.getElementById('btnClear');
  const btnSound   = document.getElementById('btnSound');
  const toast      = document.getElementById('toast');

  let paused      = false;
  let soundOn     = true;
  let prevCount   = null; // número de registros anterior
  let prevContent = null;

  // ── Sonido (Web Audio API) ───────────────────────────────
  let audioCtx = null;
  function getAudioCtx() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    return audioCtx;
  }
  async function playNotif() {
    if (!soundOn) return;
    try {
      const ctx = getAudioCtx();
      // Forzar reanudación (los navegadores suspenden el contexto sin interacción)
      if (ctx.state === 'suspended') await ctx.resume();
      const osc  = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.connect(gain);
      gain.connect(ctx.destination);
      osc.type = 'sine';
      osc.frequency.setValueAtTime(1046, ctx.currentTime);           // Do5
      osc.frequency.setValueAtTime(1318, ctx.currentTime + 0.08);   // Mi5
      osc.frequency.setValueAtTime(1568, ctx.currentTime + 0.16);   // Sol5
      gain.gain.setValueAtTime(0.0001, ctx.currentTime);
      gain.gain.linearRampToValueAtTime(0.45, ctx.currentTime + 0.04);
      gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.5);
      osc.start(ctx.currentTime);
      osc.stop(ctx.currentTime + 0.5);
    } catch(e) { console.warn('Audio error:', e); }
  }

  // ── Toast ────────────────────────────────────────────────
  function showToast(msg) {
    toast.textContent = msg;
    toast.classList.add('show');
    clearTimeout(toast._t);
    toast._t = setTimeout(() => toast.classList.remove('show'), 2500);
  }

  // ── Parsear bloques del archivo ──────────────────────────
  function parseBlocks(raw) {
    // Dividir en bloques usando las líneas de "===="
    return raw
      .split(/={10,}/)
      .map(s => s.trim())
      .filter(Boolean);
  }

  // Quitar líneas de guiones "----" internas y limpiar el bloque
  function cleanBlock(block) {
    return block
      .split('\n')
      .filter(line => !/^-{10,}$/.test(line.trim()))  // elimina líneas "-----"
      .map(line => line.trimEnd())
      .join('\n')
      .trim();
  }

  // ── Render ───────────────────────────────────────────────
  function renderBlocks(blocks, newCount) {
    if (!blocks.length) {
      output.innerHTML = '<div class="empty-state"><span>📭</span>Sin datos todavía.</div>';
      return;
    }
    // Los más nuevos van arriba → invertir
    const reversed = [...blocks].reverse();
    const html = reversed.map((block, idx) => {
      const clean = cleanBlock(block);
      if (!clean) return '';
      const cls = (idx < newCount) ? 'new-entry' : '';
      return `<div class="entry ${cls}">${escHtml(clean)}</div>`;
    }).filter(Boolean).join('');
    output.innerHTML = html || '<div class="empty-state"><span>📭</span>Sin datos todavía.</div>';
  }

  function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
  }

  // ── Fetch datos ──────────────────────────────────────────
  async function fetchData() {
    if (paused) return;
    try {
      const res  = await fetch('api_datos.php?action=read&_=' + Date.now());
      const json = await res.json();
      if (!json.ok) return;

      const now = new Date().toLocaleTimeString('es-ES');
      lastUpdate.textContent = '✅ ' + now;

      // Estadísticas
      const bytes = json.size;
      statSize.textContent = bytes >= 1024 ? (bytes/1024).toFixed(1) + ' KB' : bytes + ' B';

      const content = json.content.trim();
      const blocks  = content ? parseBlocks(content) : [];
      const count   = blocks.length;
      statCount.textContent = count;

      if (content !== prevContent) {
        // Cuántos registros nuevos llegaron
        const diff = (prevCount !== null && count > prevCount) ? count - prevCount : 0;

        if (diff > 0) {
          playNotif();
          showToast('🔔 ' + diff + ' nuevo' + (diff > 1 ? 's registros' : ' registro'));
        }

        prevContent = content;
        prevCount   = count;
        renderBlocks(blocks, diff);
      } else if (prevCount === null) {
        prevCount = count;
        renderBlocks(blocks, 0);
      }

      liveDot.classList.remove('off');
    } catch (e) {
      lastUpdate.textContent = '❌ Error de conexión';
      liveDot.classList.add('off');
    }
  }

  // ── Botón Sonido ─────────────────────────────────────────
  btnSound.addEventListener('click', () => {
    // Primer clic desbloquea AudioContext en móviles
    getAudioCtx();
    soundOn = !soundOn;
    btnSound.textContent = soundOn ? '🔔 Sonido' : '🔕 Silencio';
    showToast(soundOn ? '🔔 Sonido activado' : '🔕 Sonido silenciado');
  });

  // ── Guardar ──────────────────────────────────────────────
  btnSave.addEventListener('click', async () => {
    const res  = await fetch('api_datos.php?action=read&_=' + Date.now());
    const json = await res.json();
    if (!json.ok || !json.content.trim()) { showToast('⚠️ No hay datos para guardar'); return; }
    const blob = new Blob([json.content], { type: 'text/plain;charset=utf-8' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    const ts   = new Date().toISOString().replace(/[:.]/g,'-').slice(0,19);
    a.href = url; a.download = `datos_${ts}.txt`; a.click();
    URL.revokeObjectURL(url);
    showToast('💾 Archivo descargado');
  });

  // ── Limpiar ──────────────────────────────────────────────
  btnClear.addEventListener('click', async () => {
    if (!confirm('¿Seguro que deseas borrar todos los datos?')) return;
    const res  = await fetch('api_datos.php?action=clear', { method: 'POST' });
    const json = await res.json();
    if (json.ok) {
      prevContent = ''; prevCount = 0;
      output.innerHTML = '<div class="empty-state"><span>📭</span>Sin datos todavía.</div>';
      statSize.textContent = '0 B'; statCount.textContent = '0';
      showToast('🗑 Datos eliminados');
    } else {
      showToast('❌ Error al limpiar');
    }
  });

  // ── Pausar ───────────────────────────────────────────────
  btnPause.addEventListener('click', () => {
    paused = !paused;
    btnPause.textContent = paused ? '▶ Reanudar' : '⏸ Pausar';
    liveDot.classList.toggle('off', paused);
  });

  // ── Loop ─────────────────────────────────────────────────
  fetchData();
  setInterval(fetchData, 1000);
</script>
</body>
</html>
