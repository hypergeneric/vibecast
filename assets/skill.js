(() => {
    const button = document.getElementById('vibecast-copy-skill');
    const text = document.getElementById('vibecast-skill');
    const status = document.getElementById('vibecast-copy-status');
    if (!button || !text || !status) return;
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(text.value);
            status.textContent = wp.i18n.__('Instructions copied.', 'vibecast');
        } catch (_) {
            text.focus();
            text.select();
            status.textContent = wp.i18n.__('Instructions selected. Copy them with your keyboard.', 'vibecast');
        }
    });
})();
