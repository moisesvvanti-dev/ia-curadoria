/**
 * ============================================================
 * LUMINOUS SECURITY & PERFORMANCE SHIELD v1.0
 * Advanced Anti-Tamper + Anti-Debug + Performance Guard
 * ============================================================
 */

const LuminousShield = (() => {
    const config = {
        maxClicksPerMinute: 10,
        clickWindow: 60000,
        lastClicks: []
    };

    // 1. ANTI-DEVTOOLS & SHORTCUTS
    const initAntiDebug = () => {
        // Disable Right Click
        document.addEventListener('contextmenu', e => e.preventDefault());

        // Disable Shortcuts
        document.addEventListener('keydown', e => {
            // F12
            if (e.keyCode === 123) e.preventDefault();
            // Ctrl+Shift+I/J/C
            if (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) e.preventDefault();
            // Ctrl+U (View Source)
            if (e.ctrlKey && e.keyCode === 85) e.preventDefault();
            // Ctrl+S
            if (e.ctrlKey && e.keyCode === 83) e.preventDefault();
        });

        // Anti-Debug (Infinite debugger loop if DevTools detected)
        // This is aggressive but effective for "locked" systems
        /*
        setInterval(() => {
            const before = Date.now();
            debugger;
            const after = Date.now();
            if (after - before > 100) {
                document.body.innerHTML = '<div style="background:#000;color:#f00;height:100vh;display:flex;align-items:center;justify-content:center;font-family:sans-serif;text-align:center;"><h2>🔒 SISTEMA BLOQUEADO</h2><p>Detecção de inspeção ativa. Feche as ferramentas de desenvolvedor e recarregue.</p></div>';
            }
        }, 2000);
        */
    };

    // 2. RATE LIMITING (Firebase Protection)
    const checkRateLimit = () => {
        const now = Date.now();
        config.lastClicks = config.lastClicks.filter(t => now - t < config.clickWindow);

        if (config.lastClicks.length >= config.maxClicksPerMinute) {
            console.warn("[Security] Rate limit exceeded");
            return false;
        }

        config.lastClicks.push(now);
        return true;
    };

    // 3. INTEGRITY CHECK
    const checkIntegrity = () => {
        // Basic check if core functions were replaced
        if (typeof ClickTracker === 'undefined' || !ClickTracker.track) {
            window.location.reload();
        }
    };

    const init = () => {
        initAntiDebug();
        setInterval(checkIntegrity, 5000);
        console.log("🛡️ Luminous Shield Active");
    };

    return { init, checkRateLimit };
})();

LuminousShield.init();
window.LuminousShield = LuminousShield;
