import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Installation PWA (bouton « Installer l'application » du menu)
let deferredInstallPrompt = null;

Alpine.store('pwa', {
    canInstall: false,
    async install() {
        if (!deferredInstallPrompt) return;
        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        this.canInstall = false;
    },
});

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    Alpine.store('pwa').canInstall = true;
});

window.addEventListener('appinstalled', () => {
    Alpine.store('pwa').canInstall = false;
});

if ('serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}

Alpine.start();
